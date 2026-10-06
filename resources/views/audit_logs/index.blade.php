@extends('layouts.app')
@section('title', 'System Audit Logs')
@section('page_title', 'System Audit Trail & Security Logs')
@section('page_subtitle', 'Chronological audit trail of administrative activities, master entity modifications, and security events')

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('audit-logs.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>All Activities</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'masters'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'masters' ? 'active' : '' }}">
                <i class="bi bi-building-gear text-primary"></i>
                <span>Master Changes</span>
                <span class="filter-tab-badge">{{ $tabCounts['masters'] ?? 0 }}</span>
            </a>
            <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'security'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'security' ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill text-warning"></i>
                <span>Auth & Security</span>
                <span class="filter-tab-badge">{{ $tabCounts['security'] ?? 0 }}</span>
            </a>
            <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'operations'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'operations' ? 'active' : '' }}">
                <i class="bi bi-activity text-success"></i>
                <span>Operations & Logs</span>
                <span class="filter-tab-badge">{{ $tabCounts['operations'] ?? 0 }}</span>
            </a>
        </div>

        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">
                <i class="bi bi-list-check"></i> 10 per page
            </span>
        </div>
    </div>

    <!-- Filter Controls Bar -->
    <div class="filter-controls-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search user, action, description, IP..." 
                :suggestions="$auditSuggestions ?? []"
                header-title="Audit Log Suggestions"
            />

            <x-custom-select 
                name="action" 
                :value="request('action')" 
                placeholder="All Actions" 
                search-placeholder="Search actions..." 
                icon="bi-activity" 
                min-width="145px" 
                :options="[
                    ['value' => 'login', 'label' => 'Login'],
                    ['value' => 'logout', 'label' => 'Logout'],
                    ['value' => 'create', 'label' => 'Create'],
                    ['value' => 'update', 'label' => 'Update'],
                    ['value' => 'toggle', 'label' => 'Toggle Status'],
                    ['value' => 'delete', 'label' => 'Delete'],
                    ['value' => 'export', 'label' => 'Export'],
                ]" 
                auto-submit
            />

            <x-custom-select 
                name="module" 
                :value="request('module')" 
                placeholder="All Modules" 
                search-placeholder="Search modules..." 
                icon="bi-boxes" 
                min-width="145px" 
                :options="[
                    ['value' => 'plants', 'label' => 'Plants'],
                    ['value' => 'roles', 'label' => 'Roles'],
                    ['value' => 'users', 'label' => 'Users'],
                    ['value' => 'questions', 'label' => 'Questions'],
                    ['value' => 'visits', 'label' => 'Visits'],
                    ['value' => 'feedbacks', 'label' => 'Feedbacks'],
                    ['value' => 'auth', 'label' => 'Auth & Security'],
                    ['value' => 'reports', 'label' => 'Reports'],
                ]" 
                auto-submit
            />

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">From:</span>
                <x-date-picker name="from" :value="request('from')" placeholder="dd/mm/yyyy" title="From Date" />
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">To:</span>
                <x-date-picker name="to" :value="request('to')" placeholder="dd/mm/yyyy" title="To Date" />
            </div>

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['tab', 'q', 'action', 'module', 'from', 'to']))
                <a href="{{ route('audit-logs.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Logs Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-journal-text text-primary"></i>
            <span>Recorded Audit Events</span>
        </div>
        <span class="badge-modern badge-slate">{{ $logs->total() }} Events Found</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="width: 165px;">Timestamp</th>
                    <th>User / Actor</th>
                    <th>Action & Module</th>
                    <th>Event Description</th>
                    <th>Network & Device</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
            @php
                $actionBadges = [
                    'create' => 'badge-emerald',
                    'update' => 'badge-indigo',
                    'delete' => 'badge-rose',
                    'toggle' => 'badge-amber',
                    'login'  => 'badge-cyan',
                    'logout' => 'badge-slate',
                    'export' => 'badge-shibaura',
                ];
            @endphp
            @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="small fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                        <div class="small text-muted">{{ $log->created_at->format('h:i:s A') }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-chip" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ strtoupper(substr($log->user_name ?? 'S', 0, 2)) }}
                            </span>
                            <div>
                                <div class="fw-bold text-dark small">{{ $log->user_name }}</div>
                                <div class="badge-modern badge-slate py-0 px-1" style="font-size: 0.68rem;">
                                    {{ ucfirst($log->user_role ?? 'System') }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-inline-flex flex-column gap-1">
                            <span class="badge-modern {{ $actionBadges[$log->action] ?? 'badge-slate' }} text-capitalize py-0 px-2" style="font-size: 0.72rem;">
                                {{ $log->action }}
                            </span>
                            <span class="badge-modern badge-slate py-0 px-2" style="font-size: 0.68rem; text-transform: uppercase;">
                                <i class="bi bi-box-seam me-1"></i> {{ $log->module }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="text-dark small fw-semibold">{{ $log->description }}</div>
                    </td>
                    <td>
                        <div class="small text-dark">
                            <i class="bi bi-hdd-network text-muted me-1"></i> <code>{{ $log->ip_address ?: '127.0.0.1' }}</code>
                        </div>
                        @if($log->user_agent)
                            <div class="small text-muted text-truncate" style="max-width: 160px; font-size: 0.72rem;" title="{{ $log->user_agent }}">
                                <i class="bi bi-browser-chrome text-muted me-1"></i> {{ Str::limit($log->user_agent, 24) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($log->details)
                            <button type="button" class="btn-action-icon view" title="View event details" onclick="showLogDetails({{ json_encode($log) }})">
                                <i class="bi bi-info-circle-fill"></i>
                            </button>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-journal-x fs-2 d-block mb-2 text-slate-300"></i>
                        No audit events recorded for the selected filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Footer Container -->
<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $logs->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $logs->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $logs->total() }}</span> audit events (10 per page)
    </div>
    <div>
        {{ $logs->links() }}
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--slate-200); overflow: hidden;">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon-bubble shibaura" style="width: 32px; height: 32px; font-size: 0.95rem;">
                        <i class="bi bi-journal-code"></i>
                    </span>
                    <h6 class="modal-title fw-bold text-dark mb-0" id="logDetailsModalLabel">Audit Event Inspection</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">Recorded Timestamp</div>
                        <div class="fw-bold text-dark" id="modalTimestamp">—</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Actor</div>
                        <div class="fw-bold text-dark" id="modalActor">—</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Action / Module</div>
                        <div class="fw-bold text-dark" id="modalActionModule">—</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">IP Address</div>
                        <div class="fw-bold text-dark" id="modalIp">—</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Event Summary</div>
                        <div class="fw-bold text-dark" id="modalDescription">—</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="small text-muted fw-bold mb-1">Payload / State Changes (JSON):</div>
                    <pre class="bg-dark text-success p-3 rounded-3 small mb-0" style="max-height: 260px; overflow-y: auto;" id="modalPayload"></pre>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn-modern-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showLogDetails(log) {
    document.getElementById('modalTimestamp').innerText = log.created_at ? new Date(log.created_at).toLocaleString() : '—';
    document.getElementById('modalActor').innerText = (log.user_name || 'System') + ' (' + (log.user_role || 'system') + ')';
    document.getElementById('modalActionModule').innerText = (log.action || '').toUpperCase() + ' in ' + (log.module || '').toUpperCase();
    document.getElementById('modalIp').innerText = log.ip_address || '127.0.0.1';
    document.getElementById('modalDescription').innerText = log.description || '—';
    document.getElementById('modalPayload').innerText = JSON.stringify(log.details || {}, null, 2);

    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    modal.show();
}
</script>
@endpush
@endsection
