let dataTable;

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function resolveStatusInfo(row) {
    if (!row) {
        return { label: 'Unknown', badge: 'secondary', icon: 'fa-question-circle', tooltip: 'No status information' };
    }

    const raw = String(row.valid || '').trim();

    if (raw === 'valid' || raw === '1' || raw === 'Success') {
        return {
            label: 'Success',
            badge: 'success',
            icon: 'fa-check-circle',
            tooltip: 'Successful login'
        };
    }
    if (raw === 'auth_required_no_cookie') {
        return {
            label: 'Code Sent',
            badge: 'info text-dark',
            icon: 'fa-paper-plane',
            tooltip: 'Verification code emailed'
        };
    }
    if (raw === 'auth_cd_not_expired') {
        return {
            label: 'Active Code Pending',
            badge: 'info text-dark',
            icon: 'fa-clock',
            tooltip: 'Existing code is still valid'
        };
    }
    if (raw === 'auth_failed_invalid_cookie') {
        return {
            label: 'Saved Cookie Expired',
            badge: 'warning text-dark',
            icon: 'fa-cookie-bite',
            tooltip: 'Remember-me session expired; new code emailed'
        };
    }
    if (raw.startsWith('resend_throttle__')) {
        const sec = raw.substring('resend_throttle__'.length);
        return {
            label: `Resend Cooldown (${sec}s)`,
            badge: 'secondary',
            icon: 'fa-hourglass-half',
            tooltip: `Code requested too quickly (${sec}s remaining)`
        };
    }
    if (raw.startsWith('over_max_requests__')) {
        const timeout = raw.substring('over_max_requests__'.length);
        return {
            label: 'Account Locked',
            badge: 'danger',
            icon: 'fa-lock',
            tooltip: `Account locked after 3+ failed attempts (until ${timeout})`
        };
    }
    if (raw === 'auth_invalid') {
        return {
            label: 'Incorrect Code',
            badge: 'danger',
            icon: 'fa-times-circle',
            tooltip: 'Incorrect 6-digit code entered'
        };
    }
    if (raw === 'auth_old') {
        return {
            label: 'Code Expired',
            badge: 'warning text-dark',
            icon: 'fa-hourglass-end',
            tooltip: 'Code expired; new code was emailed'
        };
    }
    if (raw === 'invalid') {
        return {
            label: 'Unregistered / Inactive',
            badge: 'danger',
            icon: 'fa-user-xmark',
            tooltip: 'Email not registered or account disabled'
        };
    }
    if (raw === 'invalid_pending') {
        return {
            label: 'Pending Approval',
            badge: 'warning text-dark',
            icon: 'fa-user-clock',
            tooltip: 'Member application pending officer approval'
        };
    }
    if (raw === 'invalid_request') {
        return {
            label: 'Invalid Request',
            badge: 'secondary',
            icon: 'fa-exclamation-triangle',
            tooltip: 'Missing or malformed email address'
        };
    }
    if (raw === 'db_error') {
        return {
            label: 'Database Error',
            badge: 'danger',
            icon: 'fa-database',
            tooltip: 'Database connection or query failure'
        };
    }

    return {
        label: row.status_label || raw || 'Unknown',
        badge: row.status_badge || 'secondary',
        icon: 'fa-info-circle',
        tooltip: row.status_desc || raw || 'Status recorded'
    };
}

document.addEventListener('DOMContentLoaded', function () {
    dataTable = new DataTable('#kcbLogonTable', {
        order: [[2, 'desc']],
        responsive: true,
        pageLength: 25,
        ajax: {
            url: 'api/loginStats.php',
            dataSrc: ''
        },
        columns: [
            {
                data: 'logonValue',
                title: 'Email Address',
                render: function (data, type) {
                    if (type === 'display') {
                        return '<span class="text-break">' + escapeHtml(data || '') + '</span>';
                    }
                    return data || '';
                }
            },
            {
                data: 'valid',
                title: 'Status',
                render: function (data, type, row) {
                    const info = resolveStatusInfo(row);
                    if (type === 'sort' || type === 'filter') {
                        return info.label;
                    }
                    return `<span class="badge bg-${info.badge} px-2 py-1 text-wrap" title="${escapeHtml(info.tooltip)}" data-bs-toggle="tooltip">` +
                           `<i class="fa ${info.icon} me-1" aria-hidden="true"></i>${escapeHtml(info.label)}</span>`;
                }
            },
            {
                data: 'estbd_dt_tm',
                title: 'Date / Time',
                render: function (data, type) {
                    if (type === 'sort' || type === 'type') {
                        return data || '';
                    }
                    if (!data) return '';
                    const dateObj = new Date(data.replace(/-/g, '/'));
                    if (isNaN(dateObj)) return escapeHtml(data);
                    return dateObj.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                }
            }
        ]
    });
});
