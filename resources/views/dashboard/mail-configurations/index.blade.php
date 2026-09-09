@extends('layout.dashboard.dashboardMain')
@section('title', 'Mail Configurations')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Management /</span> Mail Configurations</h4>

        <div class="card mb-4">
            <div class="card-header flex-column flex-md-row">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm me-1">
                            <span class="avatar-initial bg-label-primary rounded-circle">
                                <i class="bx bx-mail-send"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="mb-0">SMTP Credentials</h5>
                            <small class="text-muted">{{ $configurations->total() }} {{ $configurations->total() === 1 ? 'configuration' : 'configurations' }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('mail-configurations.index') }}" class="d-flex">
                            <input
                                type="text"
                                class="form-control me-2"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by host, username or from email"
                            />
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </form>
                        <a href="{{ route('mail-configurations.create') }}" class="btn btn-primary text-nowrap">
                            <i class="bx bx-plus me-1"></i> Add Configuration
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @forelse ($configurations as $configuration)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-start gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar avatar-md me-1">
                                    <span class="avatar-initial bg-label-primary rounded-circle">
                                        <i class="bx bx-envelope fs-4"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="mb-1 fw-semibold">{{ $configuration->smtp_host }}</h5>
                                    <small class="text-muted">Port {{ $configuration->smtp_port }}</small>
                                </div>
                            </div>
                            @if ($configuration->is_default)
                                <span class="badge bg-label-success">
                                    <i class="bx bx-check me-1"></i>Default
                                </span>
                            @else
                                <span class="badge bg-label-secondary">Available</span>
                            @endif
                        </div>

                        <div class="card-body">
                            <ul class="list-unstyled mb-0 d-grid gap-3">
                                <li class="d-flex align-items-center gap-3">
                                    <i class="bx bx-at text-primary fs-5"></i>
                                    <div>
                                        <small class="text-muted d-block">From</small>
                                        <span class="fw-semibold text-wrap">{{ $configuration->from_name }}</span>
                                        <small class="text-muted d-block text-break">{{ $configuration->from_email }}</small>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center gap-3">
                                    <i class="bx bx-user text-primary fs-5"></i>
                                    <div>
                                        <small class="text-muted d-block">Username</small>
                                        <span class="text-break">{{ $configuration->username }}</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center gap-3">
                                    <i class="bx bx-lock-open-alt text-primary fs-5"></i>
                                    <div>
                                        <small class="text-muted d-block">Encryption</small>
                                        <span class="badge bg-label-secondary text-capitalize">{{ $configuration->encryption }}</span>
                                    </div>
                                </li>
                                @if ($configuration->reply_to_email)
                                    <li class="d-flex align-items-center gap-3">
                                        <i class="bx bx-reply text-primary fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block">Reply-To</small>
                                            <span class="text-break">{{ $configuration->reply_to_email }}</span>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="card-footer bg-transparent border-top">
                            <div class="d-flex gap-2">
                                <a href="{{ route('mail-configurations.edit', $configuration) }}" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('mail-configurations.destroy', $configuration) }}"
                                    class="flex-grow-1"
                                    onsubmit="return confirm('Are you sure you want to delete this mail configuration?');"
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
            @empty
                <div class="col-12">
                    <div class="card border-0">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-envelope-open display-3 text-secondary"></i>
                            <h5 class="mt-3">No mail configurations found</h5>
                            <p class="text-muted mb-3">Add your first SMTP configuration to start sending emails.</p>
                            <a href="{{ route('mail-configurations.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add Configuration
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($configurations->hasPages())
            <div class="mt-4">
                {{ $configurations->links() }}
            </div>
        @endif
    </div>
@endsection