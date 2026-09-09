@extends('layout.dashboard.dashboardMain')
@section('title', 'Bulk Mail History')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Email /</span> Bulk Mail History</h4>

        <div class="card mb-4">
            <div class="card-header flex-column flex-md-row">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm me-1">
                            <span class="avatar-initial bg-label-primary rounded-circle">
                                <i class="bx bx-send"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="mb-0">Sent Campaigns</h5>
                            <small class="text-muted">{{ $mailings->total() }} {{ $mailings->total() === 1 ? 'campaign' : 'campaigns' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('bulk-mail.create') }}" class="btn btn-primary text-nowrap">
                        <i class="bx bx-send me-1"></i> New Campaign
                    </a>
                </div>
            </div>
        </div>

        @if ($mailings->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-envelope-open display-3 text-secondary"></i>
                    <h5 class="mt-3">No campaigns yet</h5>
                    <p class="text-muted mb-3">Send your first bulk campaign from the sender screen.</p>
                    <a href="{{ route('bulk-mail.create') }}" class="btn btn-primary">
                        <i class="bx bx-send me-1"></i> Open Bulk Mail Sender
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Template</th>
                                <th>Subject</th>
                                <th class="text-center">Recipients</th>
                                <th class="text-center">Sent</th>
                                <th class="text-center">Failed</th>
                                <th>Status</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mailings as $mailing)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($mailing->created_at)->format('M d, Y') }}</span>
                                        <small class="text-muted d-block">{{ $mailing->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-truncate" style="max-width: 180px;">
                                        <span class="fw-semibold">{{ $mailing->emailTemplate?->name ?? 'Template deleted' }}</span>
                                        <small class="text-muted d-block">{{ $mailing->mailConfiguration?->from_email ?? 'Config deleted' }}</small>
                                    </td>
                                    <td class="text-truncate" style="max-width: 220px;">{{ $mailing->subject ?? '—' }}</td>
                                    <td class="text-center">{{ $mailing->recipients_count }}</td>
                                    <td class="text-center text-success fw-semibold">{{ $mailing->sent_count }}</td>
                                    <td class="text-center {{ $mailing->failed_count > 0 ? 'text-danger fw-semibold' : '' }}">{{ $mailing->failed_count }}</td>
                                    <td>
                                        @if ($mailing->status === \App\Models\BulkMailing::STATUS_PROCESSING)
                                            <span class="badge bg-label-warning">Processing</span>
                                        @elseif ($mailing->status === \App\Models\BulkMailing::STATUS_FAILED)
                                            <span class="badge bg-label-danger">Failed</span>
                                        @else
                                            <span class="badge bg-label-success">Completed</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#mailing-{{ $mailing->id }}"
                                            aria-expanded="false"
                                        >
                                            <i class="bx bx-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="mailing-{{ $mailing->id }}">
                                    <td colspan="8" class="bg-lighter">
                                        <div class="p-3">
                                            <h6 class="fw-semibold mb-2">Recipients &amp; Delivery</h6>
                                            @php
                                                $deliveries = $mailing->deliveries->keyBy('email');
                                                $legacyResults = is_array($mailing->results) ? $mailing->results : [];
                                            @endphp
                                            @if ($deliveries->isNotEmpty() || $legacyResults)
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($mailing->recipients as $email)
                                                        @php
                                                            $delivery = $deliveries->get($email);
                                                            $status = $delivery ? $delivery->status : ($legacyResults[$email] ?? null);
                                                        @endphp
                                                        <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                                            <span class="text-break me-2">{{ $email }}</span>
                                                            @if ($status === 'sent')
                                                                <span class="badge bg-label-success text-nowrap"><i class="bx bx-check me-1"></i>Sent</span>
                                                            @elseif ($status === 'failed')
                                                                <span class="badge bg-label-danger text-nowrap">
                                                                    <i class="bx bx-x me-1"></i>Failed
                                                                    @if ($delivery?->error)
                                                                        <span class="text-truncate d-inline-block align-middle" style="max-width: 260px;" title="{{ $delivery->error }}">{{ $delivery->error }}</span>
                                                                    @endif
                                                                </span>
                                                            @else
                                                                <span class="badge bg-label-secondary text-nowrap">—</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted mb-0">No delivery details recorded.</p>
                                            @endif
                                            @if ($mailing->error)
                                                <div class="bg-lighter border rounded-3 p-3 mt-3">
                                                    <small><pre class="mb-0"><code>{{ $mailing->error }}</code></pre></small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($mailings->hasPages())
                <div class="mt-4">
                    {{ $mailings->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection