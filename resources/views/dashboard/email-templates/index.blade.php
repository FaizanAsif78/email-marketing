@extends('layout.dashboard.dashboardMain')
@section('title', 'Email Templates')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Email /</span> Templates</h4>

        <div class="card mb-4">
            <div class="card-header flex-column flex-md-row">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm me-1">
                            <span class="avatar-initial bg-label-primary rounded-circle">
                                <i class="bx bx-news"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="mb-0">My Templates</h5>
                            <small class="text-muted">{{ $templates->total() }} {{ $templates->total() === 1 ? 'template' : 'templates' }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="GET" action="{{ route('email-templates.index') }}" class="d-flex">
                            <input
                                type="text"
                                class="form-control me-2"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by name or subject"
                            />
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </form>
                        <a href="{{ route('email-templates.create') }}" class="btn btn-primary text-nowrap">
                            <i class="bx bx-plus me-1"></i> New Template
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($templates->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-news display-3 text-secondary"></i>
                    <h5 class="mt-3">No templates yet</h5>
                    <p class="text-muted mb-3">Create your first guest posting email template with the rich text editor.</p>
                    <a href="{{ route('email-templates.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Template
                    </a>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                @foreach ($templates as $template)
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-header p-0 position-relative">
                                <iframe
                                    srcdoc="{{ $template->content }}"
                                    class="template-preview w-100 border-0"
                                    loading="lazy"
                                    sandbox=""
                                    title="{{ $template->name }}"
                                ></iframe>
                                <a href="{{ route('email-templates.edit', $template) }}"
                                   class="btn btn-primary btn-sm position-absolute top-50 start-50 translate-middle opacity-0 template-preview-cta">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-1 fw-semibold">{{ $template->name }}</h5>
                                <small class="text-muted d-block">{{ $template->subject ?? 'No subject set' }}</small>
                            </div>
                            <div class="card-footer bg-transparent border-top">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('email-templates.edit', $template) }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('email-templates.destroy', $template) }}"
                                        class="flex-grow-1"
                                        onsubmit="return confirm('Are you sure you want to delete this template?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($templates->hasPages())
                <div class="mt-4">
                    {{ $templates->links() }}
                </div>
            @endif
        @endif
    </div>

    @push('styles')
        <style>
            .template-preview {
                height: 200px;
                background: #f2f2f7;
                pointer-events: none;
            }
            .template-preview-cta {
                transition: opacity 0.15s ease;
            }
            .card:hover .template-preview-cta {
                opacity: 1;
            }
        </style>
    @endpush
@endsection