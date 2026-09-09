@extends('layout.dashboard.dashboardMain')
@section('title', 'Bulk Mail Sender')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Email /</span> Bulk Mail Sender</h4>

        @if (session('success') && !session('warning'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bx bxs-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <i class="bx bxs-error-circle me-1"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bxs-error me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Campaign setup --}}
        <div class="card mb-4">
            <div class="card-header flex-column flex-md-row">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm me-1">
                        <span class="avatar-initial bg-label-primary rounded-circle">
                            <i class="bx bx-send"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="mb-0">Campaign Setup</h5>
                        <small class="text-muted">Pick a mail configuration and one of your templates</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if ($mailConfigurations->isEmpty())
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="bx bxs-info-circle me-2 mt-1"></i>
                        <div>
                            No mail configuration available for your tenant yet.
                            @can('manage mail configurations')
                                <a href="{{ route('mail-configurations.create') }}" class="alert-link">Create one here</a>.
                            @endcan
                        </div>
                    </div>
                @endif
                @if ($templates->isEmpty())
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="bx bxs-info-circle me-2 mt-1"></i>
                        <div>
                            You have no email templates yet.
                            <a href="{{ route('email-templates.create') }}" class="alert-link">Create your first template</a>.
                        </div>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Mail Configuration</label>
                        <div class="dropdown border rounded-3 {{ $mailConfigurations->isEmpty() ? 'opacity-50' : '' }}" id="configDropdown">
                            <button
                                type="button"
                                class="bulk-select-toggle d-flex align-items-center justify-content-between w-100 px-3 py-3 bg-transparent border-0 text-start"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                {{ $mailConfigurations->isEmpty() ? 'disabled' : '' }}
                            >
                                <span class="bulk-select-label text-truncate pe-3">-- Select mail configuration --</span>
                                <i class="bx bx-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu w-100 p-0 shadow-lg" id="configMenu"></ul>
                        </div>
                        <div id="testMailWrapper" class="d-none mt-2">
                            <button type="button" id="testMailBtn" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-mail-send me-1"></i> Send test email
                            </button>
                            <div id="testMailResult" class="mt-1 small"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Template</label>
                        <div class="dropdown border rounded-3 {{ $templates->isEmpty() ? 'opacity-50' : '' }}" id="templateDropdown">
                            <button
                                type="button"
                                class="bulk-select-toggle d-flex align-items-center justify-content-between w-100 px-3 py-3 bg-transparent border-0 text-start"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                {{ $templates->isEmpty() ? 'disabled' : '' }}
                            >
                                <span class="bulk-select-label text-truncate pe-3">-- Select template --</span>
                                <i class="bx bx-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu w-100 p-0 shadow-lg" id="templateMenu"></ul>
                        </div>
                    </div>
                </div>

                <div id="composeSummary" class="d-none border rounded-3 p-3 mt-3 bg-lighter">
                    <div class="d-flex flex-wrap gap-4">
                        <div>
                            <small class="text-muted d-block">From</small>
                            <span id="summaryFrom" class="fw-semibold">—</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Reply-to</small>
                            <span id="summaryReplyTo" class="fw-semibold">—</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Subject</small>
                            <span id="summarySubject" class="fw-semibold">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recipients + Preview --}}
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header flex-column flex-md-row">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm me-1">
                                    <span class="avatar-initial bg-label-success rounded-circle">
                                        <i class="bx bx-user-plus"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="mb-0">Recipients</h5>
                                    <small class="text-muted">
                                        <span id="recipientCount">0</span> email(s) in the list
                                    </small>
                                </div>
                            </div>
                            <button type="button" id="clearRecipients" class="btn btn-outline-danger btn-sm d-none">
                                <i class="bx bx-trash me-1"></i> Clear all
                            </button>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPaste" role="tab">
                                    <i class="bx bx-paste me-1"></i> Paste
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tabUpload" role="tab">
                                    <i class="bx bx-upload me-1"></i> Upload CSV / JSON
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="tabPaste" role="tabpanel">
                                <div class="alert alert-primary bg-label-primary d-flex align-items-start" role="alert">
                                    <i class="bx bx-info-circle me-2 mt-1"></i>
                                    <div class="small">
                                        Paste a <strong>comma separated</strong> list (<code>a@site.com, b@site.com</code>),
                                        one per line, or a <strong>JSON array</strong>:
                                        <code>[{"email": "a@site.com"}, "b@site.com"]</code>.
                                    </div>
                                </div>
                                <textarea id="contactsInput" class="form-control font-monospace" rows="5"
                                    placeholder="john@example.com, jane@example.com&#10;[&quot;peter@example.com&quot;, &quot;amy@example.com&quot;]"></textarea>
                                <button type="button" id="parsePasteBtn" class="btn btn-primary mt-3">
                                    <i class="bx bx-plus-circle me-1"></i> Add to list
                                </button>
                            </div>

                            <div class="tab-pane fade" id="tabUpload" role="tabpanel">
                                <div class="alert alert-primary bg-label-primary d-flex align-items-start" role="alert">
                                    <i class="bx bx-info-circle me-2 mt-1"></i>
                                    <div class="small">
                                        Upload a <strong>CSV</strong> file with an <code>email</code> column, a plain
                                        <code>.txt</code> list, or a <strong>JSON</strong> array file.
                                    </div>
                                </div>
                                <input type="file" id="contactsFile" class="form-control" accept=".csv,.txt,.json,text/csv,text/plain,application/json" />
                                <button type="button" id="parseFileBtn" class="btn btn-primary mt-3">
                                    <i class="bx bx-upload me-1"></i> Upload &amp; add
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body border-top">
                        <div class="table-responsive recipients-table">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="w-px-50">#</th>
                                        <th>Email address</th>
                                        <th class="w-px-80 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="recipientsBody">
                                    <tr id="recipientsEmpty">
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bx bx-envelope-open display-4 d-block text-secondary"></i>
                                            Your recipient list is empty.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm me-1">
                            <span class="avatar-initial bg-label-info rounded-circle">
                                <i class="bx bx-news"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="mb-0">Template Preview</h5>
                            <small class="text-muted">Live preview of the selected template</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="previewEmpty" class="text-center text-muted py-5">
                            <i class="bx bx-show-alt display-4 d-block text-secondary mb-2"></i>
                            <p class="mb-0">Select a template above to preview it here.</p>
                        </div>
                        <div id="previewContent" class="d-none">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                <span id="previewSubject" class="badge bg-label-info"></span>
                                <small id="previewTemplateName" class="text-muted"></small>
                            </div>
                            <iframe id="templatePreview" class="bulk-preview w-100 border rounded-3" title="Template preview"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Send form --}}
        <form method="POST" action="{{ route('bulk-mail.send') }}" id="sendForm" class="card mt-4">
            @csrf
            <input type="hidden" name="mail_configuration_id" id="mailConfigurationId" value="" />
            <input type="hidden" name="email_template_id" id="emailTemplateId" value="" />
            <input type="hidden" name="recipients" id="recipientsJson" value="" />

            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bx bx-rocket text-primary bx-lg"></i>
                        <div>
                            <div class="fw-semibold" id="sendSummary">Select a configuration, template and add recipients to start.</div>
                            <small class="text-muted" id="sendSubSummary">Recipients: 0</small>
                        </div>
                    </div>
                    <button type="submit" id="sendButton" class="btn btn-primary btn-lg px-4" disabled>
                        <i class="bx bx-send me-1"></i> <span id="sendButtonLabel">Send Emails</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            .bulk-preview {
                height: 420px;
                background: #f2f2f7;
                border: 0;
                display: block;
            }
            .recipients-table {
                height: 420px;
                overflow-y: auto;
                overflow-x: hidden;
            }
            .recipients-table thead th {
                position: sticky;
                top: 0;
                z-index: 1;
                background: var(--bs-table-bg, #eceef1);
            }
            .bulk-select-toggle {
                cursor: pointer;
                transition: background-color 0.15s ease;
            }
            .bulk-select-toggle:disabled {
                cursor: not-allowed;
            }
            .bulk-select-toggle:focus-visible {
                outline: 2px solid var(--bs-primary);
                outline-offset: -2px;
                border-radius: inherit;
            }
            .bulk-select-menu {
                max-height: 280px;
                overflow-y: auto;
                z-index: 1080;
            }
            .bulk-select-menu .dropdown-item.active {
                background: var(--bs-primary);
                color: #ffffff;
            }
            .bulk-select-menu .dropdown-item.active small {
                color: rgba(255, 255, 255, 0.75) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            window.BulkMail = {
                templates: {!! Js::from($templateOptions) !!},
                configurations: {!! Js::from($configurationOptions) !!},
                parseUrl: @json(route('bulk-mail.parse')),
            };
        </script>
        <script>
            (function () {
                const scope = window.BulkMail;
                const recipients = [];
                const set = new Set();

                const $ = (sel) => document.querySelector(sel);
                const $all = (sel) => Array.from(document.querySelectorAll(sel));

                const templatePreview = $('#templatePreview');
                const recipientsBody = $('#recipientsBody');
                const recipientsJson = $('#recipientsJson');
                const sendButton = $('#sendButton');
                const sendButtonLabel = $('#sendButtonLabel');
                const sendSummary = $('#sendSummary');
                const sendSubSummary = $('#sendSubSummary');
                const recipientCount = $('#recipientCount');
                const clearRecipients = $('#clearRecipients');
                const composeSummary = $('#composeSummary');
                const mailConfigurationId = $('#mailConfigurationId');
                const emailTemplateId = $('#emailTemplateId');

                const esc = (s) => s ? String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])) : '';

                const selectedTemplate = () => scope.templates.find(t => String(t.id) === templatePicker.value);
                const selectedConfig = () => scope.configurations.find(c => String(c.id) === configPicker.value);

                function initBulkSelect({ rootId, menuId, options, placeholder, onSelect }) {
                    const root = document.getElementById(rootId);
                    const toggle = root.querySelector('[data-bs-toggle="dropdown"]');
                    const label = root.querySelector('.bulk-select-label');
                    const menu = document.getElementById(menuId);
                    const items = [];
                    let current = '';

                    options.forEach((opt) => {
                        const li = document.createElement('li');
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'dropdown-item px-3 py-2 border-0 d-flex flex-column align-items-start';
                        btn.dataset.value = String(opt.value);
                        btn.innerHTML =
                            `<span class="fw-semibold d-block text-truncate">${esc(opt.title)}</span>` +
                            (opt.sub ? `<small class="text-muted d-block text-truncate">${esc(opt.sub)}</small>` : '');
                        btn.addEventListener('click', () => select(String(opt.value)));
                        li.appendChild(btn);
                        menu.appendChild(li);
                        items.push({ value: String(opt.value), el: btn });
                    });

                    function select(value) {
                        current = value;
                        const opt = options.find((o) => String(o.value) === value);
                        if (opt) {
                            label.innerHTML =
                                `<span class="fw-semibold d-block text-truncate">${esc(opt.title)}</span>` +
                                (opt.sub ? `<small class="text-muted d-block text-truncate">${esc(opt.sub)}</small>` : '');
                            label.parentElement.classList.add('text-primary');
                        } else {
                            label.textContent = placeholder;
                            label.parentElement.classList.remove('text-primary');
                        }
                        items.forEach((i) => i.el.classList.toggle('active', i.value === value));
                        const dd = window.bootstrap?.Dropdown.getInstance(toggle);
                        dd?.hide();
                        onSelect?.(value);
                    }

                    return { get value() { return current; }, select };
                }

                const configOptions = scope.configurations.map((c) => ({
                    value: c.id,
                    title: c.from_name ? `${c.from_name} <${c.from_email}>` : c.from_email,
                    sub: `${c.host || ''} ${c.is_default ? '· Default' : ''}`.trim(),
                }));
                const templateOptions = scope.templates.map((t) => ({
                    value: t.id,
                    title: t.name,
                    sub: t.subject || '',
                }));

                const configPicker = initBulkSelect({
                    rootId: 'configDropdown',
                    menuId: 'configMenu',
                    options: configOptions,
                    placeholder: '-- Select mail configuration --',
                    onSelect: (value) => {
                        mailConfigurationId.value = value;
                        $('#testMailWrapper').classList.toggle('d-none', !value);
                        $('#testMailResult').innerHTML = '';
                        renderPreview();
                    },
                });
                const templatePicker = initBulkSelect({
                    rootId: 'templateDropdown',
                    menuId: 'templateMenu',
                    options: templateOptions,
                    placeholder: '-- Select template --',
                    onSelect: (value) => {
                        emailTemplateId.value = value;
                        renderPreview();
                    },
                });

                const defaultConfig = scope.configurations.find((c) => c.is_default);
                if (defaultConfig) configPicker.select(String(defaultConfig.id));

                function renderRecipients() {
                    recipientsBody.querySelectorAll('tr[data-recipient]').forEach(tr => tr.remove());

                    recipients.forEach((email, i) => {
                        const tr = document.createElement('tr');
                        tr.dataset.recipient = '';
                        tr.innerHTML = `
                            <td>${i + 1}</td>
                            <td class="fw-semibold">${email}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-remove="${email}" title="Remove">
                                    <i class="bx bx-x"></i>
                                </button>
                            </td>`;
                        recipientsBody.appendChild(tr);
                    });

                    $('#recipientsEmpty').classList.toggle('d-none', recipients.length > 0);
                    clearRecipients.classList.toggle('d-none', recipients.length === 0);
                    recipientCount.textContent = recipients.length;
                    recipientsJson.value = JSON.stringify(recipients);
                    updateSendState();
                }

                function addEmails(emails) {
                    let added = 0;
                    emails.forEach(email => {
                        const e = String(email).trim().toLowerCase();
                        if (!e || set.has(e)) return;
                        set.add(e);
                        recipients.push(e);
                        added++;
                    });
                    renderRecipients();
                    return added;
                }

                function updateSendState() {
                    const ready = configPicker.value !== '' && templatePicker.value !== '' && recipients.length > 0;
                    sendButton.disabled = !ready;

                    if (configPicker.value !== '' && templatePicker.value !== '' && recipients.length > 0) {
                        sendButtonLabel.textContent = `Send ${recipients.length} Email${recipients.length === 1 ? '' : 's'}`;
                        sendSummary.innerHTML = `Ready to send <strong>${recipients.length}</strong> email${recipients.length === 1 ? '' : 's'} using <strong>${selectedTemplate().name}</strong>.`;
                        const c = selectedConfig();
                        sendSubSummary.textContent = `From: ${c.from_name ? c.from_name + ' <' + c.from_email + '>' : c.from_email}`;
                    } else {
                        sendButtonLabel.textContent = 'Send Emails';
                        sendSummary.textContent = 'Select a configuration, template and add recipients to start.';
                        sendSubSummary.textContent = `Recipients: ${recipients.length}`;
                    }
                }

                function renderPreview() {
                    const t = selectedTemplate();
                    const config = selectedConfig();

                    if (t) {
                        $('#previewEmpty').classList.add('d-none');
                        $('#previewContent').classList.remove('d-none');
                        $('#previewSubject').textContent = t.subject || 'No subject';
                        $('#previewTemplateName').textContent = t.name;
                        templatePreview.srcdoc = t.content;
                    } else {
                        $('#previewEmpty').classList.remove('d-none');
                        $('#previewContent').classList.add('d-none');
                        templatePreview.removeAttribute('srcdoc');
                    }

                    if (t || config) {
                        composeSummary.classList.remove('d-none');
                        $('#summaryFrom').textContent = config ? (config.from_name ? config.from_name + ' <' + config.from_email + '>' : config.from_email) : '—';
                        $('#summaryReplyTo').textContent = config && config.reply_to ? config.reply_to : '—';
                        $('#summarySubject').textContent = t && t.subject ? t.subject : '—';
                    } else {
                        composeSummary.classList.add('d-none');
                    }

                    updateSendState();
                }

                async function parse(source, formData) {
                    $(`#parse${source === 'file' ? 'File' : 'Paste'}Btn`).disabled = true;
                    const original = $(`#parse${source === 'file' ? 'File' : 'Paste'}Btn`).innerHTML;
                    $(`#parse${source === 'file' ? 'File' : 'Paste'}Btn`).innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Parsing…';

                    try {
                        const response = await fetch(scope.parseUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}' },
                            body: formData,
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Failed to parse contacts');

                        const added = addEmails(data.emails);
                        const total = data.emails.length;
                        const action = added === total ? (total === 0 ? 'No email addresses found.' : `Added ${added} email${added === 1 ? '' : 's'}.`) : `Added ${added} new email${added === 1 ? '' : 's'} (${total - added} duplicate${total - added === 1 ? '' : 's'} skipped).`;
                        flash(action, total === 0 ? 'danger' : 'success');
                    } catch (err) {
                        flash(err.message, 'danger');
                    } finally {
                        $(`#parse${source === 'file' ? 'File' : 'Paste'}Btn`).disabled = false;
                        $(`#parse${source === 'file' ? 'File' : 'Paste'}Btn`).innerHTML = original;
                    }
                }

                function flash(message, type) {
                    const box = document.createElement('div');
                    box.className = `alert alert-${type} alert-dismissible`;
                    box.setAttribute('role', 'alert');
                    box.innerHTML = `<i class="bx ${type === 'danger' ? 'bxs-error' : 'bxs-check-circle'} me-1"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                    const anchor = document.querySelector('.container-xxl .fw-bold');
                    anchor.insertAdjacentElement('afterend', box);
                    setTimeout(() => box.remove(), 5000);
                }

                $('#testMailBtn').addEventListener('click', async () => {
                    const btn = $('#testMailBtn');
                    const configId = configPicker.value;
                    if (!configId) return;

                    btn.disabled = true;
                    const original = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing…';
                    const resultBox = $('#testMailResult');

                    try {
                        const formData = new FormData();
                        formData.append('mail_configuration_id', configId);
                        const response = await fetch('{{ route('bulk-mail.test') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}' },
                            body: formData,
                        });
                        const data = await response.json();
                        resultBox.className = `mt-1 small ${response.ok ? 'text-success' : 'text-danger'}`;
                        resultBox.textContent = data.message || (response.ok ? 'Test email sent.' : 'Test failed.');
                    } catch (err) {
                        resultBox.className = 'mt-1 small text-danger';
                        resultBox.textContent = 'Could not reach the server.';
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                });

                $('#parsePasteBtn').addEventListener('click', () => {
                    const input = $('#contactsInput').value;
                    if (!input.trim()) return;
                    const formData = new FormData();
                    formData.append('source', 'paste');
                    formData.append('input', input);
                    parse('paste', formData);
                });

                $('#parseFileBtn').addEventListener('click', () => {
                    const file = $('#contactsFile').files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('source', 'file');
                    formData.append('file', file);
                    parse('file', formData);
                });

                recipientsBody.addEventListener('click', (e) => {
                    const btn = e.target.closest('button[data-remove]');
                    if (!btn) return;
                    const email = btn.dataset.remove;
                    set.delete(email);
                    const idx = recipients.indexOf(email);
                    if (idx > -1) recipients.splice(idx, 1);
                    renderRecipients();
                });

                clearRecipients.addEventListener('click', () => {
                    recipients.length = 0;
                    set.clear();
                    renderRecipients();
                });

                if (window.bootstrap) { /* bootstrap loaded on every dashboard page */ }

                $('#sendForm').on('submit', function (e) {
                    e.preventDefault();

                    if (!configPicker.value || !templatePicker.value || recipients.length === 0) {
                        return;
                    }

                    const original = sendButton.innerHTML;
                    sendButton.disabled = true;
                    sendButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending…';

                    $.ajax({
                        url: '{{ route('bulk-mail.send') }}',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            _token: document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}',
                            mail_configuration_id: configPicker.value,
                            email_template_id: templatePicker.value,
                            recipients: recipients,
                        },
                    })
                    .done((data) => {
                        if (data.failed > 0) {
                            flash(data.message || 'Campaign sent with some failures.', 'warning');
                        } else {
                            flash(data.message || 'Campaign sent successfully.', 'success');
                        }
                        recipients.length = 0;
                        set.clear();
                        renderRecipients();
                    })
                    .fail((xhr) => {
                        const body = xhr.responseJSON || {};
                        const message = body.message
                            || (body.errors ? Object.values(body.errors).flat().join(' ') : '')
                            || 'Failed to send the campaign.';
                        flash(message, 'danger');
                    })
                    .always(() => {
                        sendButton.innerHTML = original;
                        updateSendState();
                    });
                });

                renderPreview();
            })();
        </script>
    @endpush
@endsection