(function () {
    'use strict';

    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeUsPhoneDigits(raw) {
        var digits = String(raw || '').replace(/\D/g, '');
        if (digits.length === 11 && digits.charAt(0) === '1') {
            digits = digits.slice(1);
        }
        return digits.length === 10 ? digits : null;
    }

    function formatPhoneDisplay(raw) {
        var digits = normalizeUsPhoneDigits(raw);
        if (!digits) {
            return raw ? String(raw) : '';
        }
        return '(' + digits.slice(0, 3) + ') ' + digits.slice(3, 6) + '-' + digits.slice(6, 10);
    }

    function bindPhoneFormatting(inputEl) {
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('blur', function () {
            var formatted = formatPhoneDisplay(inputEl.value);
            if (formatted !== '') {
                inputEl.value = formatted;
            }
        });
    }

    function clearAlert(containerEl) {
        if (containerEl) {
            containerEl.innerHTML = '';
        }
    }

    function showAlert(containerEl, type, message, extraNode) {
        if (!containerEl) {
            return;
        }
        containerEl.innerHTML = '';

        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show shadow-sm';
        alertDiv.setAttribute('role', 'alert');

        var textSpan = document.createElement('span');
        textSpan.textContent = message;
        alertDiv.appendChild(textSpan);

        if (extraNode) {
            alertDiv.appendChild(document.createTextNode(' '));
            alertDiv.appendChild(extraNode);
        }

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'alert');
        closeBtn.setAttribute('aria-label', 'Close');
        alertDiv.appendChild(closeBtn);

        containerEl.appendChild(alertDiv);
        if (type === 'danger' && typeof containerEl.scrollIntoView === 'function') {
            containerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            var temp = document.createElement('textarea');
            temp.value = text;
            temp.setAttribute('readonly', '');
            temp.style.position = 'fixed';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.select();
            try {
                var ok = document.execCommand('copy');
                document.body.removeChild(temp);
                if (ok) {
                    resolve();
                } else {
                    reject(new Error('Clipboard copy failed'));
                }
            } catch (err) {
                document.body.removeChild(temp);
                reject(err);
            }
        });
    }

    function createCopyLinkButton(url, labelText) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-dark ms-2';
        btn.textContent = labelText || 'Copy Onboarding Link';
        btn.addEventListener('click', function () {
            copyTextToClipboard(url).then(function () {
                btn.textContent = 'Copied!';
                window.setTimeout(function () {
                    btn.textContent = labelText || 'Copy Onboarding Link';
                }, 2500);
            }).catch(function () {
                window.prompt('Copy onboarding link:', url);
            });
        });
        return btn;
    }

    function postApiForm(url, params) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: params.toString()
        }).then(function (response) {
            return response.text().then(function (rawText) {
                var data = null;
                try {
                    data = JSON.parse(rawText);
                } catch (e) {
                    throw new Error('Unexpected server response.');
                }
                if (!response.ok || (data && (data.status === 'error' || data.status === 'failed'))) {
                    var msg = (data && data.message) ? data.message : ('Request failed (' + response.status + ').');
                    throw new Error(msg);
                }
                return data;
            });
        });
    }

    function getJson(url) {
        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        }).then(function (response) {
            return response.text().then(function (rawText) {
                var data = null;
                try {
                    data = JSON.parse(rawText);
                } catch (e) {
                    throw new Error('Unexpected server response.');
                }
                if (!response.ok || (data && data.status === 'error')) {
                    var msg = (data && data.message) ? data.message : ('Request failed (' + response.status + ').');
                    throw new Error(msg);
                }
                return data;
            });
        });
    }

    function bindRosterPreview(firstInput, lastInput, fullCheckbox, previewEl) {
        if (!firstInput || !lastInput || !fullCheckbox || !previewEl) {
            return function () {};
        }

        function updatePreview() {
            var first = firstInput.value.trim() || 'Firstname';
            var last = lastInput.value.trim();
            if (fullCheckbox.checked) {
                previewEl.textContent = last ? (first + ' ' + last) : first;
            } else {
                var initial = last ? (last.charAt(0) + '.') : 'L.';
                previewEl.textContent = first + ' ' + initial;
            }
        }

        firstInput.addEventListener('input', updatePreview);
        lastInput.addEventListener('input', updatePreview);
        fullCheckbox.addEventListener('change', updatePreview);
        updatePreview();

        return updatePreview;
    }

    function updateEmailDeleteButtons(containerEl) {
        if (!containerEl) {
            return;
        }
        var rows = containerEl.querySelectorAll('.email-row');
        var hideDelete = rows.length <= 1;
        rows.forEach(function (row) {
            var btn = row.querySelector('.remove-email-btn');
            if (btn) {
                btn.classList.toggle('d-none', hideDelete);
            }
        });
    }

    function createEmailRowElement(value) {
        var wrapper = document.createElement('div');
        wrapper.className = 'input-group email-row';

        var input = document.createElement('input');
        input.type = 'email';
        input.className = 'form-control';
        input.name = 'email[]';
        input.maxLength = 100;
        input.placeholder = 'name@example.com';
        input.required = true;
        input.value = value || '';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-danger remove-email-btn';
        btn.title = 'Remove email';
        btn.setAttribute('aria-label', 'Remove email');
        btn.innerHTML = '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>';

        wrapper.appendChild(input);
        wrapper.appendChild(btn);
        return wrapper;
    }

    function setupEmailManager(containerEl, addBtnEl) {
        if (!containerEl || !addBtnEl) {
            return { setEmails: function () {} };
        }

        containerEl.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.remove-email-btn');
            if (!removeBtn) {
                return;
            }
            var rows = containerEl.querySelectorAll('.email-row');
            if (rows.length <= 1) {
                return;
            }
            var row = removeBtn.closest('.email-row');
            if (row) {
                row.remove();
                updateEmailDeleteButtons(containerEl);
            }
        });

        addBtnEl.addEventListener('click', function () {
            var row = createEmailRowElement('');
            containerEl.appendChild(row);
            updateEmailDeleteButtons(containerEl);
            var input = row.querySelector('input');
            if (input) {
                input.focus();
            }
        });

        updateEmailDeleteButtons(containerEl);

        return {
            setEmails: function (emails) {
                containerEl.innerHTML = '';
                var list = Array.isArray(emails) && emails.length > 0 ? emails : [''];
                list.forEach(function (addr) {
                    containerEl.appendChild(createEmailRowElement(addr));
                });
                updateEmailDeleteButtons(containerEl);
            }
        };
    }

    function validateProfileForm(formEl, alertEl, emailContainerEl, instrumentSelector) {
        clearAlert(alertEl);
        formEl.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        var firstInput = formEl.querySelector('[name="firstName"]');
        var lastInput = formEl.querySelector('[name="lastName"]');
        var addr1Input = formEl.querySelector('[name="address1"]');
        var cityInput = formEl.querySelector('[name="city"]');
        var zipInput = formEl.querySelector('[name="zip"]');
        var phoneInput = formEl.querySelector('[name="text"]');
        var emergNameInput = formEl.querySelector('[name="emergency_contact_name"]');
        var emergPhoneInput = formEl.querySelector('[name="emergency_contact_phone"]');

        function fail(el, msg) {
            if (el) {
                el.classList.add('is-invalid');
                el.focus();
            }
            showAlert(alertEl, 'danger', msg);
            return false;
        }

        if (!firstInput || firstInput.value.trim() === '') {
            return fail(firstInput, 'First name is required.');
        }
        if (!lastInput || lastInput.value.trim() === '') {
            return fail(lastInput, 'Last name is required.');
        }
        if (!addr1Input || addr1Input.value.trim() === '') {
            return fail(addr1Input, 'Street address is required.');
        }
        if (!cityInput || cityInput.value.trim() === '') {
            return fail(cityInput, 'City is required.');
        }
        if (!zipInput || !/^\d{5}$/.test(zipInput.value.trim())) {
            return fail(zipInput, 'Please enter a valid 5-digit Zip Code.');
        }

        if (phoneInput && phoneInput.value.trim() !== '') {
            if (!normalizeUsPhoneDigits(phoneInput.value)) {
                return fail(phoneInput, 'Cell phone number must be a valid 10-digit phone number.');
            }
        }

        if (!emergNameInput || emergNameInput.value.trim() === '') {
            return fail(emergNameInput, 'Emergency contact name is required.');
        }
        if (!emergPhoneInput || !normalizeUsPhoneDigits(emergPhoneInput.value)) {
            return fail(emergPhoneInput, 'Emergency contact phone must be a valid 10-digit phone number.');
        }

        var emailInputs = emailContainerEl ? emailContainerEl.querySelectorAll('input[name="email[]"]') : [];
        var validEmailCount = 0;
        var invalidEmailInput = null;
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        emailInputs.forEach(function (inp) {
            var val = inp.value.trim();
            if (val === '') {
                return;
            }
            if (!emailRegex.test(val) || val.length > 100) {
                invalidEmailInput = invalidEmailInput || inp;
                return;
            }
            validEmailCount++;
        });

        if (invalidEmailInput) {
            return fail(invalidEmailInput, 'Please enter valid email address(es).');
        }
        if (validEmailCount === 0) {
            return fail(emailInputs[0] || null, 'At least one email address is required.');
        }

        var checkedInstruments = formEl.querySelectorAll(instrumentSelector + ':checked');
        if (checkedInstruments.length === 0) {
            var firstBox = formEl.querySelector(instrumentSelector);
            return fail(firstBox, 'Please select at least one instrument.');
        }

        return true;
    }

    function setButtonBusy(btnEl, busy, busyLabel) {
        if (!btnEl) {
            return;
        }
        if (busy) {
            if (!btnEl.dataset.origHtml) {
                btnEl.dataset.origHtml = btnEl.innerHTML;
            }
            btnEl.disabled = true;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + escapeHtml(busyLabel || 'Working...');
        } else {
            btnEl.disabled = false;
            if (btnEl.dataset.origHtml) {
                btnEl.innerHTML = btnEl.dataset.origHtml;
            }
        }
    }

    function initMemberFormMode(appEl) {
        var formEl = document.getElementById('pendingInfoForm');
        if (!formEl) {
            return;
        }

        var alertEl = document.getElementById('memberFormAlert');
        var cardEl = document.getElementById('pendingInfoCard');
        var successPanelEl = document.getElementById('completionSuccessPanel');
        var successMsgEl = document.getElementById('completionSuccessMessage');
        var submitBtnEl = document.getElementById('submitPendingInfoBtn');
        var emailContainerEl = document.getElementById('memberEmailContainer');
        var addEmailBtnEl = document.getElementById('memberAddEmailBtn');

        bindRosterPreview(
            document.getElementById('firstName'),
            document.getElementById('lastName'),
            document.getElementById('displayFullName'),
            document.getElementById('rosterNamePreview')
        );

        bindPhoneFormatting(document.getElementById('text'));
        bindPhoneFormatting(document.getElementById('emergency_contact_phone'));
        setupEmailManager(emailContainerEl, addEmailBtnEl);

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validateProfileForm(formEl, alertEl, emailContainerEl, '.member-instrument-checkbox')) {
                return;
            }

            var params = new URLSearchParams(new FormData(formEl));
            setButtonBusy(submitBtnEl, true, 'Submitting...');

            postApiForm('/members/api/pendingMembers.php', params)
                .then(function (data) {
                    clearAlert(alertEl);
                    if (cardEl) {
                        cardEl.classList.add('d-none');
                    }
                    if (successPanelEl && successMsgEl) {
                        successMsgEl.textContent = data.message || 'Thank you! Your member information has been submitted.';
                        successPanelEl.classList.remove('d-none');
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })
                .catch(function (err) {
                    showAlert(alertEl, 'danger', err.message || 'Unable to submit your member information.');
                })
                .finally(function () {
                    setButtonBusy(submitBtnEl, false);
                });
        });
    }

    function initAdminMode(appEl) {
        var csrfToken = appEl.dataset.csrfToken || '';
        var adminAlertEl = document.getElementById('adminAlert');
        var sendDueBtn = document.getElementById('sendDueRemindersBtn');
        var tableEl = document.getElementById('kcbMemberTable');

        var modalEl = document.getElementById('memberModal');
        var modalFormEl = document.getElementById('memberForm');
        var modalAlertEl = document.getElementById('modalAlert');
        var modalSaveBtn = document.getElementById('modalSaveBtn');
        var modalEmailContainer = document.getElementById('modalEmailContainer');
        var modalAddEmailBtn = document.getElementById('modalAddEmailBtn');

        var updateModalPreview = bindRosterPreview(
            document.getElementById('modalFirstName'),
            document.getElementById('modalLastName'),
            document.getElementById('modalDisplayFullName'),
            document.getElementById('modalRosterNamePreview')
        );

        bindPhoneFormatting(document.getElementById('modalText'));
        bindPhoneFormatting(document.getElementById('modalEmergencyPhone'));
        var emailManager = setupEmailManager(modalEmailContainer, modalAddEmailBtn);

        var dataTable = null;
        if (tableEl && typeof window.DataTable === 'function') {
            dataTable = new DataTable(tableEl, {
                ajax: {
                    url: '/members/api/pendingMembers.php',
                    dataSrc: 'data',
                    error: function (xhr) {
                        var errMsg = 'Unable to load pending members list.';
                        if (xhr && xhr.responseText) {
                            try {
                                var parsed = JSON.parse(xhr.responseText);
                                if (parsed && parsed.message) {
                                    errMsg = parsed.message;
                                }
                            } catch (e) {
                                // ignore parse failures
                            }
                        }
                        showAlert(adminAlertEl, 'danger', errMsg);
                    }
                },
                order: [[6, 'desc']],
                responsive: true,
                language: {
                    emptyTable: 'No pending members awaiting approval or profile completion.'
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1,
                        render: function (data, type, row) {
                            if (type !== 'display') {
                                return '';
                            }
                            var uid = escapeHtml(row.uid);
                            var name = escapeHtml(row.fullName || 'this member');
                            var buttons = [];

                            if (Number(row.pending_info) === 1) {
                                buttons.push(
                                    '<button type="button" class="btn btn-outline-warning text-dark btn-sm" ' +
                                    'data-action="remind" data-uid="' + uid + '" data-name="' + name + '" title="Send reminder email">' +
                                    '<i class="fa-solid fa-bell me-1" aria-hidden="true"></i>Remind</button>'
                                );
                                if (row.onboarding_url) {
                                    buttons.push(
                                        '<button type="button" class="btn btn-outline-secondary btn-sm" ' +
                                        'data-action="copy-link" data-url="' + escapeHtml(row.onboarding_url) + '" data-name="' + name + '" title="Copy member onboarding link">' +
                                        '<i class="fa-solid fa-link" aria-hidden="true"></i></button>'
                                    );
                                }
                            } else {
                                buttons.push(
                                    '<button type="button" class="btn btn-success btn-sm" ' +
                                    'data-action="activate" data-uid="' + uid + '" data-name="' + name + '" title="Activate member and email onboarding link">' +
                                    '<i class="fa-solid fa-user-check me-1" aria-hidden="true"></i>Activate</button>'
                                );
                            }

                            buttons.push(
                                '<button type="button" class="btn btn-outline-primary btn-sm" ' +
                                'data-action="edit" data-uid="' + uid + '" title="Edit or complete profile manually">' +
                                '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>'
                            );
                            buttons.push(
                                '<button type="button" class="btn btn-outline-danger btn-sm" ' +
                                'data-action="delete" data-uid="' + uid + '" data-name="' + name + '" title="Delete pending member">' +
                                '<i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>'
                            );

                            return '<div class="d-flex flex-wrap flex-xl-nowrap gap-1">' + buttons.join('') + '</div>';
                        }
                    },
                    {
                        data: 'fullName',
                        defaultContent: '',
                        responsivePriority: 1,
                        render: function (data, type) {
                            return type === 'display' ? escapeHtml(data) : (data || '');
                        }
                    },
                    {
                        data: null,
                        defaultContent: '',
                        responsivePriority: 2,
                        render: function (data, type, row) {
                            var isAwaitingInfo = Number(row.pending_info) === 1;
                            if (type !== 'display') {
                                return isAwaitingInfo ? 'Awaiting Member Info' : 'Needs Admin Activation';
                            }
                            if (!isAwaitingInfo) {
                                return '<span class="badge bg-secondary">Needs Admin Activation</span>';
                            }
                            var count = Number(row.reminder_count) || 0;
                            var details = ['Activated' + (row.activated_dt_tm ? ': ' + escapeHtml(row.activated_dt_tm) : '')];
                            details.push('Reminders: ' + count + '/3');
                            if (count > 0 && row.last_reminder_dt_tm) {
                                details.push('Last: ' + escapeHtml(row.last_reminder_dt_tm));
                            }
                            return '<div><span class="badge bg-warning text-dark">Awaiting Member Info</span></div>' +
                                '<div class="small text-muted mt-1">' + details.join(' &middot; ') + '</div>';
                        }
                    },
                    {
                        data: 'text',
                        defaultContent: '',
                        render: function (data, type) {
                            var formatted = formatPhoneDisplay(data);
                            return type === 'display' ? escapeHtml(formatted) : formatted;
                        }
                    },
                    {
                        data: 'email',
                        defaultContent: '',
                        responsivePriority: 3,
                        render: function (data, type) {
                            if (type !== 'display') {
                                return data || '';
                            }
                            return String(data || '').split(',').map(function (e) {
                                return escapeHtml(e.trim());
                            }).filter(Boolean).join('<br>');
                        }
                    },
                    {
                        data: 'instrument',
                        defaultContent: '',
                        render: function (data, type) {
                            return type === 'display' ? escapeHtml(data) : (data || '');
                        }
                    },
                    {
                        data: 'estbd_dt_tm',
                        defaultContent: '',
                        responsivePriority: 2,
                        render: function (data, type) {
                            return type === 'display' ? escapeHtml(data) : (data || '');
                        }
                    }
                ]
            });
        }

        function reloadTable() {
            if (dataTable) {
                dataTable.ajax.reload(null, false);
            }
        }

        if (sendDueBtn) {
            sendDueBtn.addEventListener('click', function () {
                clearAlert(adminAlertEl);
                setButtonBusy(sendDueBtn, true, 'Checking Due Reminders...');

                var params = new URLSearchParams();
                params.set('type', 'sendDueReminders');
                params.set('csrf_token', csrfToken);

                postApiForm('/members/api/pendingMembers.php', params)
                    .then(function (data) {
                        var alertType = data.status === 'disabled' ? 'warning' : 'info';
                        showAlert(adminAlertEl, alertType, data.message || 'Checked pending member reminders.');
                        reloadTable();
                    })
                    .catch(function (err) {
                        showAlert(adminAlertEl, 'danger', err.message || 'Unable to process pending reminders.');
                    })
                    .finally(function () {
                        setButtonBusy(sendDueBtn, false);
                    });
            });
        }

        if (tableEl) {
            tableEl.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-action]');
                if (!btn) {
                    return;
                }

                var action = btn.dataset.action;
                var uid = btn.dataset.uid || '';
                var name = btn.dataset.name || 'this member';

                if (action === 'copy-link') {
                    var url = btn.dataset.url || '';
                    if (url) {
                        copyTextToClipboard(url)
                            .then(function () {
                                showAlert(adminAlertEl, 'info', 'Copied onboarding link for ' + name + '.');
                            })
                            .catch(function () {
                                window.prompt('Copy onboarding link for ' + name + ':', url);
                            });
                    }
                    return;
                }

                if (action === 'activate') {
                    clearAlert(adminAlertEl);
                    setButtonBusy(btn, true, 'Activating...');
                    var activateParams = new URLSearchParams();
                    activateParams.set('type', 'activate');
                    activateParams.set('uid', uid);
                    activateParams.set('csrf_token', csrfToken);

                    postApiForm('/members/api/pendingMembers.php', activateParams)
                        .then(function (data) {
                            var alertType = data.status === 'warning' ? 'warning' : 'success';
                            var extraBtn = data.activation_url ? createCopyLinkButton(data.activation_url, 'Copy Onboarding Link') : null;
                            showAlert(adminAlertEl, alertType, data.message || 'Member activated.', extraBtn);
                            reloadTable();
                        })
                        .catch(function (err) {
                            showAlert(adminAlertEl, 'danger', err.message || 'Failed to activate pending member.');
                            setButtonBusy(btn, false);
                        });
                    return;
                }

                if (action === 'remind') {
                    clearAlert(adminAlertEl);
                    setButtonBusy(btn, true, 'Sending...');
                    var remindParams = new URLSearchParams();
                    remindParams.set('type', 'remind');
                    remindParams.set('uid', uid);
                    remindParams.set('csrf_token', csrfToken);

                    postApiForm('/members/api/pendingMembers.php', remindParams)
                        .then(function (data) {
                            showAlert(adminAlertEl, 'success', data.message || ('Reminder sent to ' + name + '.'));
                            reloadTable();
                        })
                        .catch(function (err) {
                            showAlert(adminAlertEl, 'danger', err.message || 'Failed to send reminder email.');
                            setButtonBusy(btn, false);
                        });
                    return;
                }

                if (action === 'delete') {
                    if (!window.confirm('Are you sure you want to delete ' + name + '?')) {
                        return;
                    }
                    clearAlert(adminAlertEl);
                    setButtonBusy(btn, true, 'Deleting...');
                    var delParams = new URLSearchParams();
                    delParams.set('type', 'delete');
                    delParams.set('uid', uid);
                    delParams.set('csrf_token', csrfToken);

                    postApiForm('/members/api/pendingMembers.php', delParams)
                        .then(function (data) {
                            showAlert(adminAlertEl, 'success', data.message || 'Pending member deleted.');
                            reloadTable();
                        })
                        .catch(function (err) {
                            showAlert(adminAlertEl, 'danger', err.message || 'Failed to delete pending member.');
                            setButtonBusy(btn, false);
                        });
                    return;
                }

                if (action === 'edit' && modalEl && modalFormEl) {
                    clearAlert(modalAlertEl);
                    setButtonBusy(btn, true, '...');
                    getJson('/members/api/pendingMembers.php?type=getMemberRecord&uid=' + encodeURIComponent(uid))
                        .then(function (res) {
                            var member = res.member || {};
                            document.getElementById('modalUid').value = member.uid || uid;
                            document.getElementById('modalFirstName').value = member.firstName || '';
                            document.getElementById('modalLastName').value = member.lastName || '';
                            document.getElementById('modalDisplayFullName').checked = Boolean(member.displayFullName && String(member.displayFullName) !== '0');
                            document.getElementById('modalAddress1').value = member.address1 || '';
                            document.getElementById('modalAddress2').value = member.address2 || '';
                            document.getElementById('modalCity').value = member.city || '';
                            document.getElementById('modalZip').value = member.zip || '';
                            document.getElementById('modalText').value = formatPhoneDisplay(member.text || '');
                            document.getElementById('modalEmergencyName').value = member.emergency_contact_name || '';
                            document.getElementById('modalEmergencyPhone').value = formatPhoneDisplay(member.emergency_contact_phone || '');

                            var emails = String(member.email || '').split(',').map(function (s) {
                                return s.trim();
                            }).filter(Boolean);
                            emailManager.setEmails(emails);

                            var instrSet = String(member.instrument || '').split(',').map(function (s) {
                                return s.trim();
                            }).filter(Boolean);
                            modalFormEl.querySelectorAll('.modal-instrument-checkbox').forEach(function (box) {
                                box.checked = instrSet.indexOf(box.value) !== -1;
                            });

                            updateModalPreview();
                            var modalBody = modalEl.querySelector('.modal-body');
                            if (modalBody) {
                                modalBody.scrollTop = 0;
                            }
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        })
                        .catch(function (err) {
                            showAlert(adminAlertEl, 'danger', err.message || 'Unable to load member details.');
                        })
                        .finally(function () {
                            setButtonBusy(btn, false);
                        });
                }
            });
        }

        if (modalFormEl) {
            modalFormEl.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!validateProfileForm(modalFormEl, modalAlertEl, modalEmailContainer, '.modal-instrument-checkbox')) {
                    return;
                }

                var params = new URLSearchParams(new FormData(modalFormEl));
                setButtonBusy(modalSaveBtn, true, 'Saving...');

                postApiForm('/members/api/pendingMembers.php', params)
                    .then(function (data) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                        showAlert(adminAlertEl, 'success', data.message || 'Member profile finalized.');
                        reloadTable();
                    })
                    .catch(function (err) {
                        showAlert(modalAlertEl, 'danger', err.message || 'Unable to save member profile.');
                    })
                    .finally(function () {
                        setButtonBusy(modalSaveBtn, false);
                    });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var appEl = document.getElementById('pendingMembersApp');
        if (!appEl) {
            return;
        }
        var viewMode = appEl.dataset.viewMode || '';
        if (viewMode === 'admin') {
            initAdminMode(appEl);
        } else if (viewMode === 'member_form') {
            initMemberFormMode(appEl);
        }
    });
})();
