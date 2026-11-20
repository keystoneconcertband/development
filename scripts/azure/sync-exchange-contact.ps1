<#
.SYNOPSIS
    Azure Automation Runbook to add or remove Exchange Online Mail Contacts
    (visible at https://admin.cloud.microsoft/?#/Contact) from the KCB website.

.DESCRIPTION
    Supports invocation via an Azure Automation Webhook ($WebhookData JSON body)
    or direct manual parameters (-Action, -Email, etc.) for testing.
    Authenticates to Exchange Online using the Automation Account's Managed Identity.

    Required Azure Automation setup:
      1. Enable System-assigned Managed Identity on the Automation Account.
      2. Import ExchangeOnlineManagement module for PowerShell 5.1 runtime
         (or PS 7.4 via Runtime Environments; PS 7.2 lacks .NET 8 System.Runtime).
      3. Grant Exchange.ManageAsApp + Exchange Administrator role to the Managed Identity.
      4. Create Automation Variable: EXO_ORGANIZATION = "<tenant>.onmicrosoft.com"
      5. (Optional) Create encrypted Automation Variable: EXO_WEBHOOK_SECRET
      6. Runbook runtime: PowerShell 5.1
#>

param(
    [Parameter(Mandatory = $false)]
    [object]$WebhookData,

    [Parameter(Mandatory = $false)]
    [ValidateSet('', 'add', 'remove')]
    [string]$Action = '',

    [Parameter(Mandatory = $false)]
    [string]$Email = '',

    [Parameter(Mandatory = $false)]
    [string]$FirstName = '',

    [Parameter(Mandatory = $false)]
    [string]$LastName = '',

    [Parameter(Mandatory = $false)]
    [string]$DisplayName = '',

    [Parameter(Mandatory = $false)]
    [string]$Organization = ''
)

$ErrorActionPreference = 'Stop'

function Get-AutomationVarSafe {
    param([string]$Name)
    if (Get-Command -Name Get-AutomationVariable -ErrorAction SilentlyContinue) {
        try {
            return Get-AutomationVariable -Name $Name
        } catch {
            return $null
        }
    }
    return $null
}

function Get-ShortHash {
    param([string]$InputText)
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($InputText.ToLowerInvariant())
    $sha = [System.Security.Cryptography.SHA256]::Create()
    try {
        $hashBytes = $sha.ComputeHash($bytes)
        return ([BitConverter]::ToString($hashBytes) -replace '-', '').Substring(0, 6).ToLowerInvariant()
    } finally {
        $sha.Dispose()
    }
}

function Escape-OPathValue {
    param([string]$Value)
    return ($Value -replace "'", "''")
}

function Get-UniqueContactName {
    param(
        [string]$PreferredDisplayName,
        [string]$EmailAddress
    )
    $clean = ($PreferredDisplayName -replace '[\r\n\t/\\<>]+', ' ').Trim()
    if ([string]::IsNullOrWhiteSpace($clean)) {
        $clean = $EmailAddress
    }
    if ($clean.Length -gt 64) {
        $clean = $clean.Substring(0, 64).Trim()
    }

    $safeCandidate = Escape-OPathValue -Value $clean
    $conflict = Get-Recipient -Filter "Name -eq '$safeCandidate'" -ErrorAction SilentlyContinue
    if (-not $conflict) {
        return $clean
    }

    $hash = Get-ShortHash -InputText $EmailAddress
    $suffix = " ($hash)"
    $maxPrefixLen = 64 - $suffix.Length
    $prefix = if ($clean.Length -gt $maxPrefixLen) { $clean.Substring(0, $maxPrefixLen).Trim() } else { $clean }
    $uniqueName = "$prefix$suffix"

    $counter = 1
    while (Get-Recipient -Filter "Name -eq '$(Escape-OPathValue -Value $uniqueName)'" -ErrorAction SilentlyContinue) {
        $counterSuffix = " ($hash-$counter)"
        $maxLen = 64 - $counterSuffix.Length
        $p = if ($clean.Length -gt $maxLen) { $clean.Substring(0, $maxLen).Trim() } else { $clean }
        $uniqueName = "$p$counterSuffix"
        $counter++
    }

    return $uniqueName
}

function Get-UniqueContactAlias {
    param([string]$EmailAddress)
    $localPart = ($EmailAddress -split '@')[0]
    $sanitized = ($localPart -replace '[^a-zA-Z0-9._-]', '_').Trim('._-')
    if ([string]::IsNullOrWhiteSpace($sanitized)) {
        $sanitized = 'kcbcontact'
    }

    $hash = Get-ShortHash -InputText $EmailAddress
    $maxPrefixLen = 64 - ($hash.Length + 1)
    if ($sanitized.Length -gt $maxPrefixLen) {
        $sanitized = $sanitized.Substring(0, $maxPrefixLen)
    }

    $alias = "$sanitized`_$hash"
    $counter = 1
    while (Get-Recipient -Filter "Alias -eq '$(Escape-OPathValue -Value $alias)'" -ErrorAction SilentlyContinue) {
        $suffix = "_$hash$counter"
        $maxLen = 64 - $suffix.Length
        $p = if ($sanitized.Length -gt $maxLen) { $sanitized.Substring(0, $maxLen) } else { $sanitized }
        $alias = "$p$suffix"
        $counter++
    }

    return $alias
}

# 1. Parse operations from $WebhookData or direct parameters
$operations = @()
$payloadOrg = ''

if ($WebhookData) {
    $expectedSecret = [string](Get-AutomationVarSafe -Name 'EXO_WEBHOOK_SECRET')
    if (-not [string]::IsNullOrWhiteSpace($expectedSecret)) {
        $providedSecret = ''
        if ($WebhookData.RequestHeader) {
            foreach ($prop in $WebhookData.RequestHeader.PSObject.Properties) {
                if ($prop.Name -ieq 'X-KCB-Webhook-Secret') {
                    $providedSecret = [string]$prop.Value
                    break
                }
            }
        }
        if ($providedSecret -cne $expectedSecret) {
            throw 'Unauthorized webhook request: invalid X-KCB-Webhook-Secret.'
        }
    }

    if ([string]::IsNullOrWhiteSpace($WebhookData.RequestBody)) {
        Write-Output 'Webhook request body was empty; nothing to process.'
        return
    }

    $payload = $WebhookData.RequestBody | ConvertFrom-Json
    if ($payload.organization) {
        $payloadOrg = [string]$payload.organization
    }

    if ($payload.operations) {
        $operations = @($payload.operations)
    } elseif ($payload.action -and $payload.email) {
        $operations = @($payload)
    }
} elseif (-not [string]::IsNullOrWhiteSpace($Action) -and -not [string]::IsNullOrWhiteSpace($Email)) {
    $operations = @(
        [PSCustomObject]@{
            action      = $Action
            email       = $Email
            firstName   = $FirstName
            lastName    = $LastName
            displayName = $DisplayName
        }
    )
} else {
    throw 'Provide either $WebhookData or -Action and -Email.'
}

if ($operations.Count -eq 0) {
    Write-Output 'No contact operations provided.'
    return
}

# 2. Resolve Exchange organization domain
$org = $Organization
if ([string]::IsNullOrWhiteSpace($org)) { $org = $payloadOrg }
if ([string]::IsNullOrWhiteSpace($org)) { $org = [string](Get-AutomationVarSafe -Name 'EXO_ORGANIZATION') }
if ([string]::IsNullOrWhiteSpace($org)) { $org = [string]$env:EXO_ORGANIZATION }

if ([string]::IsNullOrWhiteSpace($org)) {
    throw 'Exchange organization domain is missing. Set the EXO_ORGANIZATION Automation Variable (e.g. yourtenant.onmicrosoft.com).'
}

# 3. Connect to Exchange Online via Managed Identity
Import-Module ExchangeOnlineManagement
Connect-ExchangeOnline -ManagedIdentity -Organization $org

try {
    foreach ($op in $operations) {
        $opAction = ([string]$op.action).Trim().ToLowerInvariant()
        $opEmail = ([string]$op.email).Trim()

        if ($opEmail -notmatch '^[^@\s]+@[^@\s]+\.[^@\s]+$') {
            Write-Warning "Skipping invalid email address: '$opEmail'"
            continue
        }

        $safeEmail = Escape-OPathValue -Value $opEmail

        if ($opAction -eq 'add') {
            $existingMailContact = Get-MailContact -Filter "ExternalEmailAddress -eq '$safeEmail'" -ErrorAction SilentlyContinue
            $opFirst = ([string]$op.firstName).Trim()
            $opLast = ([string]$op.lastName).Trim()
            $opDisplay = ([string]$op.displayName).Trim()
            if ([string]::IsNullOrWhiteSpace($opDisplay)) {
                $opDisplay = "$opFirst $opLast".Trim()
            }
            if ([string]::IsNullOrWhiteSpace($opDisplay)) {
                $opDisplay = $opEmail
            }
            if ($opDisplay.Length -gt 256) {
                $opDisplay = $opDisplay.Substring(0, 256).Trim()
            }

            if ($existingMailContact) {
                Write-Output "MailContact already exists for $opEmail ($($existingMailContact.DisplayName)); skipping creation."
                continue
            }

            $existingRecipient = Get-Recipient -Filter "EmailAddresses -eq 'smtp:$safeEmail'" -ErrorAction SilentlyContinue
            if ($existingRecipient) {
                Write-Output "Skipping add for ${opEmail}: address already exists in Exchange as $($existingRecipient.RecipientTypeDetails)."
                continue
            }

            $contactName = Get-UniqueContactName -PreferredDisplayName $opDisplay -EmailAddress $opEmail
            $contactAlias = Get-UniqueContactAlias -EmailAddress $opEmail

            $createParams = @{
                Name                 = $contactName
                ExternalEmailAddress = $opEmail
                DisplayName          = $opDisplay
                Alias                = $contactAlias
            }
            if (-not [string]::IsNullOrWhiteSpace($opFirst)) {
                $createParams['FirstName'] = if ($opFirst.Length -gt 64) { $opFirst.Substring(0, 64) } else { $opFirst }
            }
            if (-not [string]::IsNullOrWhiteSpace($opLast)) {
                $createParams['LastName'] = if ($opLast.Length -gt 64) { $opLast.Substring(0, 64) } else { $opLast }
            }

            New-MailContact @createParams | Out-Null
            Write-Output "Created MailContact: $opDisplay <$opEmail> (Alias: $contactAlias)"
        }
        elseif ($opAction -eq 'remove') {
            $existingContacts = @(Get-MailContact -Filter "ExternalEmailAddress -eq '$safeEmail'" -ErrorAction SilentlyContinue)
            if ($existingContacts.Count -eq 0) {
                Write-Output "No MailContact found for $opEmail; nothing to remove."
                continue
            }

            foreach ($contact in $existingContacts) {
                Remove-MailContact -Identity $contact.Identity -Confirm:$false
                Write-Output "Removed MailContact: $($contact.DisplayName) <$opEmail>"
            }
        }
        else {
            Write-Warning "Ignoring unsupported action '$opAction' for $opEmail"
        }
    }
}
finally {
    Disconnect-ExchangeOnline -Confirm:$false -ErrorAction SilentlyContinue
}
