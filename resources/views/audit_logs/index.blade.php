@extends('layouts.app')
@section('title', 'System Audit Logs')
@section('page_title', 'System Audit Trail & Security Logs')
@section('page_subtitle', 'Chronological audit trail of administrative activities, master entity modifications, and security events')

@section('summary_cards')
@php
    $totalLogs = $tabCounts['all'] ?? 0;
    $masterLogs = $tabCounts['masters'] ?? 0;
    $securityLogs = $tabCounts['security'] ?? 0;
@endphp
<div class="row g-3">
    <!-- 1. Total Audit Records -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-permissions.svg" width="48" height="48" alt="Audit Records">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalLogs) }}</div>
                        <div class="saas-stat-label">Total audit records</div>
                    </div>
                </div>
                <span class="saas-stat-badge cyan">Logs</span>
            </div>
>>>>>>> 4864f174f20811493741e0acdecfb5be9a962a08
        </div>
    </div>

    <!-- 2. Master Modifications -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/security-modules.svg" width="48" height="48" alt="Master Changes">
                    <div>
                        <div class="saas-stat-value">{{ number_format($masterLogs) }}</div>
                        <div class="saas-stat-label">Master changes</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Masters</span>
            </div>
        </div>
    </div>

    <!-- 3. Security Events -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/rbac.svg" width="48" height="48" alt="Security Events">
                    <div>
                        <div class="saas-stat-value">{{ number_format($securityLogs) }}</div>
                        <div class="saas-stat-label">Auth & security</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Security</span>
            </div>
        </div>
    </div>

    <!-- 4. Tamper Evident -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/system-roles.svg" width="48" height="48" alt="Tamper-evident trail">
                    <div>
                        <div class="saas-stat-value fs-5">Active</div>
                        <div class="saas-stat-label">Tamper-evident trail</div>
                    </div>
                </div>
                <span class="saas-stat-badge indigo">Verified</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <div class="saas-filter-pills">
        <a href="{{ route('audit-logs.index', array_merge(request()->except(['tab', 'page']))) }}"
           class="saas-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
            <span>All activities</span>
            <span class="pill-counter">{{ $tabCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'masters'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'masters' ? 'active' : '' }}">
            <span>Master changes</span>
            <span class="pill-counter">{{ $tabCounts['masters'] ?? 0 }}</span>
        </a>
        <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'security'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'security' ? 'active' : '' }}">
            <span>Auth & security</span>
            <span class="pill-counter">{{ $tabCounts['security'] ?? 0 }}</span>
        </a>
        <a href="{{ route('audit-logs.index', array_merge(request()->except(['page']), ['tab' => 'operations'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'operations' ? 'active' : '' }}">
            <span>Operations & logs</span>
            <span class="pill-counter">{{ $tabCounts['operations'] ?? 0 }}</span>
        </a>
    </div>

    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search user, action, IP..." 
                :suggestions="$auditSuggestions ?? []"
                header-title="Audit Suggestions"
            />

            <x-custom-select 
                name="action" 
                :value="request('action')" 
                placeholder="All Actions" 
                search-placeholder="Search actions..." 
                icon="bi-activity" 
                min-width="135px" 
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
                min-width="135px" 
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

            <x-date-range-picker 
                from-name="from" 
                to-name="to" 
                :from-value="request('from')" 
                :to-value="request('to')" 
                placeholder="Date range" 
                title="Filter by Log Date" 
                auto-submit
            />

            <button type="submit" class="saas-btn-filter" title="Apply Filters">
                <i class="bi bi-funnel-fill"></i>
                <span class="d-none d-sm-inline">Filter</span>
            </button>

            @if(request()->hasAny(['tab', 'q', 'action', 'module', 'from', 'to']))
                <a href="{{ route('audit-logs.index') }}" class="saas-btn-filter text-danger" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Logs Table Card -->
<div class="saas-table-card">

    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 155px;">TIMESTAMP</th>
                    <th>ACTOR / USER</th>
                    <th>ACTION & MODULE</th>
                    <th>EVENT DESCRIPTION</th>
                    <th>NETWORK & IP</th>
                    <th class="text-end">INSPECT</th>
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
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $log->created_at->format('h:i:s A') }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-chip" style="width: 30px; height: 30px; font-size: 0.75rem;">
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
                            <i class="bi bi-hdd-network text-muted me-1"></i> <code class="small">{{ $log->ip_address ?: '127.0.0.1' }}</code>
                        </div>
                        @if($log->user_agent)
                            <div class="small text-muted text-truncate mt-1" style="max-width: 160px; font-size: 0.70rem;" title="{{ $log->user_agent }}">
                                <i class="bi bi-browser-chrome text-muted me-1"></i> {{ Str::limit($log->user_agent, 20) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($log->details)
                            <button type="button" class="btn-action-edit-pill" title="View event details" onclick="showLogDetails({{ json_encode($log) }})">
                                <i class="bi bi-info-circle"></i> Inspect
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

    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing <b>{{ $logs->firstItem() ?? 0 }}</b> to <b>{{ $logs->lastItem() ?? 0 }}</b> of <b>{{ $logs->total() }}</b> audit events ({{ $logs->perPage() }} per page)@if($logs->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $logs->links() }}
        </div>
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
