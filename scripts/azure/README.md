# Automated Microsoft 365 Contacts Sync (Azure Setup)

This directory contains the Azure Automation PowerShell runbook (`sync-exchange-contact.ps1`) used by `ExchangeContactService` (`includes/class/exchangeContactService.class.php`) to automatically add or remove external contacts on `https://admin.cloud.microsoft/?#/Contact` whenever active members or their email addresses are added, updated, reinstated, or removed on the website.

---

## 1. Create the Azure Automation Account & Enable Managed Identity

1. In the **Azure Portal** (`portal.azure.com`), create an **Automation Account** (same region/subscription as your App Service).
2. Under **Account Settings > Identity**, turn **System assigned** status **On** and copy the **Object (principal) ID**.

---

## 2. Install `ExchangeOnlineManagement` Module

> [!IMPORTANT]
> **Recommended Runtime**: Use **PowerShell 5.1** (or PowerShell 7.4 via Runtime Environments if available).
> Do **NOT** use PowerShell 7.2 with modern `ExchangeOnlineManagement` (v3.5+): versions 3.5+ were recompiled against .NET 8 (`System.Runtime, Version=8.0.0.0`), whereas Azure Automation's PowerShell 7.2 sandbox only runs .NET 6, causing a `FileNotFoundException` for `System.Runtime 8.0.0.0`. In PowerShell 5.1, the module uses .NET Framework `net472` assemblies and runs cleanly.

1. In the Automation Account, go to **Shared Resources > Modules**.
2. Azure Automation requires dependent modules to be imported first before `ExchangeOnlineManagement` can be installed. Import them in this order:
   * **`PackageManagement`**: Click **Add a module** > **Browse from gallery** > search **`PackageManagement`** > select **Runtime version: 5.1** > click **Import**. Wait until status is **Available**.
   * **`PowerShellGet`**: Click **Add a module** > **Browse from gallery** > search **`PowerShellGet`** > select **Runtime version: 5.1** > click **Import**. Wait until status is **Available**.
   * **`ExchangeOnlineManagement`**: Click **Add a module** > **Browse from gallery** > search **`ExchangeOnlineManagement`** > select **Runtime version: 5.1** > click **Import**.
3. Wait 1–2 minutes until `ExchangeOnlineManagement` displays a status of **Available**.

---

## 3. Grant Exchange Online Permissions to the Managed Identity

Run the following once from PowerShell as a Microsoft 365 **Global Administrator**:

```powershell
Install-Module Microsoft.Graph -Scope CurrentUser
Connect-MgGraph -Scopes "Application.Read.All","AppRoleAssignment.ReadWrite.All","RoleManagement.ReadWrite.Directory"

$managedIdentityObjectId = "ec0140fd-5c3f-46a5-aaf5-8596272db308"

# 1. Grant Exchange.ManageAsApp permission
$exoSp = Get-MgServicePrincipal -Filter "AppId eq '00000002-0000-0ff1-ce00-000000000000'"
$appRole = $exoSp.AppRoles | Where-Object { $_.Value -eq "Exchange.ManageAsApp" }

New-MgServicePrincipalAppRoleAssignedTo `
    -ServicePrincipalId $exoSp.Id `
    -PrincipalId $managedIdentityObjectId `
    -ResourceId $exoSp.Id `
    -AppRoleId $appRole.Id

# 2. Assign the Exchange Administrator Entra role
$exoAdminRole = Get-MgRoleManagementDirectoryRoleDefinition -Filter "DisplayName eq 'Exchange Administrator'"
New-MgRoleManagementDirectoryRoleAssignment `
    -PrincipalId $managedIdentityObjectId `
    -RoleDefinitionId $exoAdminRole.Id `
    -DirectoryScopeId "/"
```

---

## 4. Create the Runbook, Variables, and Webhook

1. In the Automation Account, go to **Shared Resources > Variables**:
   * Add **`EXO_ORGANIZATION`** = `yourtenant.onmicrosoft.com` (your tenant's `.onmicrosoft.com` domain).
   * *(Recommended)* Add an encrypted string variable **`EXO_WEBHOOK_SECRET`** with a random secret token.
1. Go to **Process Automation > Runbooks** > **+ Create a runbook**:
   * Name: `sync-exchange-contact`
   * Runbook type: **PowerShell**
   * Runtime version: **5.1** *(matches the 5.1 module imported in step 2)*
   * Paste the contents of `scripts/azure/sync-exchange-contact.ps1` and click **Save** > **Publish**.
   * If publishing fails use 
      ```
      Publish-AzAutomationRunbook `
         -AutomationAccountName "AutomationAccount" `
         -ResourceGroupName "KCBResourceGroupProd" `
         -Name "sync-exchange-contacts"
      ```
1. Test it manually from the **Test pane** first with `-Action add`, `-Email`, `-FirstName`, `-LastName` (and again with `-Action remove`).
1. On the published runbook, go to **Resources > Webhooks** > **+ Add webhook**:
   * Give it a name (e.g., `kcb-website-prod`) and set an expiration date (remember to rotate before expiry).
   * Copy the **Webhook URL** immediately (Azure only shows it once).

---

## 5. Configure Azure App Service Environment Variables

In your **Azure Web App** (`webserver-prod` / `webserver-dev`) > **Settings > Environment variables > App settings**, add:

| App Setting Name | Value | Notes |
| :--- | :--- | :--- |
| `EXO_CONTACT_WEBHOOK_URL` | `https://...azure-automation.net/webhooks?token=...` | Exposed to PHP as `APPSETTING_EXO_CONTACT_WEBHOOK_URL` |
| `EXO_CONTACT_WEBHOOK_SECRET` | Same value as `EXO_WEBHOOK_SECRET` | Optional extra header verification (`X-KCB-Webhook-Secret`) |
| `EXO_CONTACT_SYNC_ENABLED` | `true` (or `false` on `webserver-dev`) | Set `false` in dev if you don't want dev edits touching prod M365 contacts |
| `EXO_CONTACT_WEBHOOK_ALLOWED_DOMAINS` | `azure-automation.net,azurewebsites.net` | Optional comma-separated domain allowlist for SSRF hardening |

---

## 6. Initial One-Time Backfill of Existing Active Members

After deploying and setting `EXO_CONTACT_WEBHOOK_URL`:

```bash
# Preview active member emails that would be synced
php scripts/sync_exchange_contacts.php --dry-run

# Sync all existing active members to Microsoft 365 Contacts in batches of 25
php scripts/sync_exchange_contacts.php
```
