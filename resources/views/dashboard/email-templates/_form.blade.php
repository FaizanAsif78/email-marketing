@php
    $template = $template ?? null;
@endphp

@csrf

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial bg-label-primary rounded-circle">
                            <i class="bx bx-detail"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="mb-0">Template Details</h5>
                        <small class="text-muted">Give your guest posting template a clear name and subject.</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name', $template?->name) }}"
                            placeholder="e.g. Guest Post Outreach"
                            autofocus
                        />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="subject" class="form-label">Email Subject</label>
                        <input
                            type="text"
                            class="form-control @error('subject') is-invalid @enderror"
                            id="subject"
                            name="subject"
                            value="{{ old('subject', $template?->subject) }}"
                            placeholder="e.g. Guest Post Pitche for [Blog]" />
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial bg-label-info rounded-circle">
                            <i class="bx bx-mail-send"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="mb-0">Email Body</h5>
                        <small class="text-muted">Write your outreach message with the rich text editor.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-1 flex-wrap placeholder-chips">
                    <small class="text-muted me-1">Insert:</small>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{contact_name}">&#123;contact_name&#125;</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{company_name}">&#123;company_name&#125;</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{blog_name}">&#123;blog_name&#125;</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{proposed_title}">&#123;proposed_title&#125;</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{website_url}">&#123;website_url&#125;</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-insert-text="{email_address}">&#123;email_address&#125;</button>
                </div>
            </div>

            <div class="card-body pb-2">
                @error('content')
                    <div class="alert alert-danger alert-dismissible py-2" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror

                <textarea id="email-content" name="content" rows="18">{{ old('content', $template?->content) }}</textarea>
            </div>

            <div class="card-footer d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <small class="text-muted">
                    <i class="bx bx-info-circle me-1"></i>
                    Tip: placeholders are replaced automatically when sending the campaign.
                </small>
                <div class="d-flex gap-2">
                    <a href="{{ route('email-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> {{ $template ? 'Update Template' : 'Save Template' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .tox-tinymce {
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
        }

        .tox .tox-edit-area__iframe {
            background-color: #ffffff;
        }

        .placeholder-chips .btn-sm {
            font-size: 0.75rem;
            padding: 0.3125rem 0.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        .placeholder-chips .btn-sm:hover {
            background-color: #696cff;
            border-color: #696cff;
            color: #ffffff;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE is not loaded');
                return;
            }

            tinymce.init({
                selector: '#email-content',
                height: 540,
                menubar: 'edit view insert format table tools',
                branding: false,
                promotion: false,

                plugins: 'autolink charmap code fullscreen help image link lists preview searchreplace table wordcount',

                toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | bullist numlist | link image table hr | ' +
                    'removeformat | searchreplace code preview fullscreen',

                content_style: 'body { font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; color: #384151; margin: 16px 12px; } ' +
                    'a { color: #696cff; } ' +
                    'h1, h2, h3 { line-height: 1.3; color: #16213a; }',

                entity_encoding: 'raw'
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const insertButtons = document.querySelectorAll('[data-insert-text]');

            insertButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const editor = tinymce.get('email-content');

                    if (editor) {
                        editor.insertContent(button.getAttribute('data-insert-text'));
                        editor.focus();
                        return;
                    }
                });
            });
        });
    </script>
@endpush