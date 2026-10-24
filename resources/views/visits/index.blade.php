@extends('layouts.app')
@section('title', 'Visitors Log')
@section('page_title', 'Visitor Directory')
@section('page_subtitle', 'Browse all visitors, escort assignments, and feedback statuses')

@section('summary_cards')
@php
    $totalVisits = $tabCounts['all'] ?? 0;
    $completedVisits = $tabCounts['completed'] ?? 0;
    $pendingVisits = $tabCounts['pending'] ?? 0;
    $completionRate = $totalVisits > 0 ? round(($completedVisits / $totalVisits) * 100) : 0;
@endphp
<div class="row g-3">
    <!-- 1. Total Visitors -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/registered-visitors.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalVisits) }}</div>
                        <div class="saas-stat-label">Total registered visitors</div>
                    </div>
                </div>
                <span class="saas-stat-badge cyan">Delegations</span>
            </div>
        </div>
    </div>

    <!-- 2. Feedback Submitted -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/surveyed.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($completedVisits) }}</div>
                        <div class="saas-stat-label">Feedback submitted</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Surveyed</span>
            </div>
        </div>
    </div>

    <!-- 3. Pending Feedback -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/awaiting-feedback.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($pendingVisits) }}</div>
                        <div class="saas-stat-label">Pending feedback</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Awaiting</span>
            </div>
        </div>
    </div>

    <!-- 4. Response Rate -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/engagement.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ $completionRate }}%</div>
                        <div class="saas-stat-label">Feedback response rate</div>
                    </div>
                </div>
                <span class="saas-stat-badge indigo">Engagement</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <div class="saas-filter-pills">
        <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page']))) }}"
           class="saas-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
            <span>All visitors</span>
            <span class="pill-counter">{{ $tabCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'completed'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'completed' ? 'active' : '' }}">
            <span>Feedback completed</span>
            <span class="pill-counter">{{ $tabCounts['completed'] ?? 0 }}</span>
        </a>
        <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'pending'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'pending' ? 'active' : '' }}">
            <span>Awaiting feedback</span>
            <span class="pill-counter">{{ $tabCounts['pending'] ?? 0 }}</span>
        </a>
    </div>

    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('visits.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search visitor, company..." 
                :suggestions="$visitSuggestions ?? []"
                header-title="Visitor Suggestions"
            />

            <x-custom-select 
                name="organizer_id" 
                :value="request('organizer_id')" 
                placeholder="All Staff" 
                search-placeholder="Search staff..." 
                icon="bi-person-badge" 
                min-width="160px" 
                :options="$organizers->map(fn($o) => ['value' => $o->id, 'label' => $o->name])" 
                auto-submit
            />

            <x-date-range-picker 
                from-name="from" 
                to-name="to" 
                :from-value="request('from')" 
                :to-value="request('to')" 
                placeholder="Date range" 
                title="Filter by Visit Date" 
                auto-submit
            />

            <button type="submit" class="saas-btn-filter" title="Apply Filters">
                <i class="bi bi-funnel-fill"></i>
                <span class="d-none d-sm-inline">Filter</span>
            </button>

            @if(request()->hasAny(['q', 'organizer_id', 'from', 'to', 'tab']))
                <a href="{{ route('visits.index') }}" class="saas-btn-filter text-danger" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Visitors Table Card -->
<div class="saas-table-card" id="visits-table-card">
    <div class="saas-table-header">
        <div>
            <div class="saas-table-title">
                Visitor directory
                @if(!empty($isTodayFilter))
                    <span class="badge bg-primary text-white ms-2 fs-xs">Today's Visits</span>
                @endif
            </div>
            <div class="saas-table-subtitle">Browse all visitors, escort assignments, and feedback statuses</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('plantvisitors.create')
                <form action="{{ route('visits.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="saas-btn-filter text-success border-success-subtle bg-white" title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Gate API
                    </button>
                </form>
                <a href="{{ route('visits.create') }}" class="btn-action-edit-pill text-decoration-none">
                    <i class="bi bi-person-plus-fill"></i> Register Visitor
                </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 75px;" class="text-center">INDEX NO</th>
                    <th style="width: 145px;">
                        @php
                            $currentSortBy = request('sort_by', 'visit_date');
                            $currentSortDir = strtolower(request('sort_dir', 'desc'));
                            $isVisitDateSorted = ($currentSortBy === 'visit_date');
                            $nextVisitDateDir = ($isVisitDateSorted && $currentSortDir === 'desc') ? 'asc' : 'desc';
                            $visitDateSortUrl = request()->fullUrlWithQuery(['sort_by' => 'visit_date', 'sort_dir' => $nextVisitDateDir, 'page' => 1]);
                        @endphp
                        <a href="{{ $visitDateSortUrl }}" 
                           class="sortable-th d-inline-flex align-items-center gap-1 text-decoration-none text-uppercase fw-bold text-nowrap"
                           style="color: {{ $isVisitDateSorted ? 'var(--shibaura-blue, #0d6efd)' : 'inherit' }};"
                           title="Sort by Visit Date (Currently {{ $isVisitDateSorted && $currentSortDir === 'asc' ? 'Oldest first' : 'Recent first' }})">
                            <span>VISIT DATE</span>
                            @if($isVisitDateSorted)
                                @if($currentSortDir === 'asc')
                                    <i class="bi bi-arrow-up text-primary fw-bold" style="font-size: 0.9rem;" title="Oldest date entry first (Ascending)"></i>
                                @else
                                    <i class="bi bi-arrow-down text-primary fw-bold" style="font-size: 0.9rem;" title="Recent date entry first (Descending)"></i>
                                @endif
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50" style="font-size: 0.75rem;"></i>
                            @endif
                        </a>
                    </th>
                    <th style="min-width: 250px;">
                        @php
                            $isNameSorted = ($currentSortBy === 'name');
                            $nextNameDir = ($isNameSorted && $currentSortDir === 'asc') ? 'desc' : 'asc';
                            $nameSortUrl = request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextNameDir, 'page' => 1]);
                        @endphp
                        <a href="{{ $nameSortUrl }}" 
                           class="sortable-th d-inline-flex align-items-center gap-1 text-decoration-none text-uppercase fw-bold text-nowrap"
                           style="color: {{ $isNameSorted ? 'var(--shibaura-blue, #0d6efd)' : 'inherit' }};"
                           title="Sort by Visitor Name">
                            <span>VISITOR INFORMATION</span>
                            @if($isNameSorted)
                                @if($currentSortDir === 'asc')
                                    <i class="bi bi-arrow-up text-primary fw-bold" style="font-size: 0.9rem;"></i>
                                @else
                                    <i class="bi bi-arrow-down text-primary fw-bold" style="font-size: 0.9rem;"></i>
                                @endif
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50" style="font-size: 0.75rem;"></i>
                            @endif
                        </a>
                    </th>
                    <th>ASSIGNED STAFF</th>
                    <th>PURPOSE</th>
                    <th class="text-end">FEEDBACK STATUS & ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            @forelse($visits as $v)
                <tr>
                    <td class="text-center align-middle">
                        <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 0.78rem;">
                            #{{ ($visits->firstItem() ?? 1) + $loop->index }}
                        </span>
                    </td>

                    <td style="white-space: nowrap;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 rounded bg-light text-center" style="min-width: 48px; border: 1px solid #e2e8f0;">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">{{ $v->visit_date->format('M') }}</div>
                                <div class="fw-bold text-dark fs-6" style="line-height: 1;">{{ $v->visit_date->format('d') }}</div>
                            </div>
                            <span class="small text-muted">{{ $v->visit_date->format('Y') }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2 flex-nowrap mb-1">
                            <span class="fw-bold text-dark fs-6 text-nowrap">{{ $v->visitor_name }}</span>
                            @if($v->visitor_code)
                                <span class="saas-code-badge text-nowrap" style="white-space: nowrap !important; flex-shrink: 0;">
                                    <i class="bi bi-qr-code me-1"></i>{{ $v->visitor_code }}
                                </span>
                            @endif
                        </div>
                        <div class="small d-flex align-items-center flex-wrap gap-2 text-muted" style="font-size: 0.78rem;">
                            @if($v->visitor_company)
                                <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem; font-weight: 500;">
                                    <i class="bi bi-building text-secondary"></i> {{ $v->visitor_company }}
                                </span>
                            @endif
                            @if($v->visitor_designation)
                                <span class="text-secondary fw-semibold small" style="font-size: 0.75rem;">
                                    {{ $v->visitor_designation }}
                                </span>
                            @endif
                            @if($v->visitor_mobile)
                                <span class="text-muted small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-telephone text-muted"></i> {{ $v->visitor_mobile }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($v->organizer)
                            <div class="d-flex align-items-center gap-2">
                                <span class="user-avatar-chip" style="width: 28px; height: 28px; font-size: 0.72rem;">
                                    {{ strtoupper(substr($v->organizer->name, 0, 2)) }}
                                </span>
                                <div>
                                    <span class="fw-semibold text-dark">{{ $v->organizer->name }}</span>
                                    @if($v->organizer->department)
                                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $v->organizer->department }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="badge bg-light text-muted border">
                                <i class="bi bi-person-dash"></i> Unassigned
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($v->purpose)
                            <span class="badge-modern badge-slate">{{ $v->purpose }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($v->feedback)
                            <div class="d-inline-flex align-items-center gap-2">
                                @include('partials.rating', ['value' => $v->feedback->overall_rating])
                                <a href="{{ route('feedbacks.show', $v->feedback) }}" class="btn-action-edit-pill text-decoration-none" title="View feedback submission">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        @elseif($v->is_feedback_expired)
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.75rem;" title="Feedback collection expired because visit day has ended">
                                    <i class="bi bi-clock-history text-secondary me-1"></i> Expired (Day Ended)
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 disabled" style="font-size: 0.75rem;" disabled title="Feedback collection has expired for past visits">
                                    <i class="bi bi-lock-fill me-1"></i> Closed
                                </button>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-hourglass-split text-warning me-1"></i> Pending
                                </span>
                                <a href="{{ route('visits.feedback.create', $v) }}" class="btn-action-edit-pill text-decoration-none" style="background: #fffbeb; color: #b45309; border-color: #fde68a;" title="Submit feedback on behalf of visitor">
                                    <i class="bi bi-pencil-square"></i> Submit
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-slate-300"></i>
                        No visit records match the selected filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="saas-table-footer">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted fw-semibold me-1" style="font-size: 0.8rem;">Page size:</span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Page size selector">
                    @foreach([10, 25, 50, 100] as $size)
                        @php $isActive = (int)request('per_page', 10) === $size; @endphp
                        <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                           class="btn {{ $isActive ? 'btn-primary text-white fw-bold' : 'btn-outline-secondary bg-white text-secondary' }}"
                           style="font-size: 0.78rem; padding: 2px 10px; border-color: #cbd5e1;">
                            {{ $size }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="saas-footer-text">
                Showing <b>{{ $visits->firstItem() ?? 0 }}</b> to <b>{{ $visits->lastItem() ?? 0 }}</b> of <b>{{ $visits->total() }}</b> visitors ({{ $visits->perPage() }} per page)@if($visits->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
            </div>
        </div>
        <div>
            {{ $visits->links('partials.pagination') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableCard = document.getElementById('visits-table-card');
    if (!tableCard) return;

    let isFetching = false;

    function loadUrlViaAjax(url, pushState = true) {
        if (isFetching) return;
        isFetching = true;

        tableCard.style.transition = 'opacity 0.15s ease';
        tableCard.style.opacity = '0.5';
        tableCard.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableCard = doc.getElementById('visits-table-card');
            if (newTableCard) {
                tableCard.innerHTML = newTableCard.innerHTML;
            }

            const newPills = doc.querySelector('.saas-filter-pills');
            const currentPills = document.querySelector('.saas-filter-pills');
            if (newPills && currentPills) {
                currentPills.innerHTML = newPills.innerHTML;
            }

            if (pushState && window.history && window.history.pushState) {
                window.history.pushState({ url: url }, '', url);
            }
        })
        .catch(err => {
            console.warn('AJAX load error, falling back to full navigation:', err);
            window.location.href = url;
        })
        .finally(() => {
            tableCard.style.opacity = '1';
            tableCard.style.pointerEvents = '';
            isFetching = false;
        });
    }

    document.addEventListener('click', function (e) {
        // Page size selector links (10, 25, 50, 100)
        const sizeLink = e.target.closest('#visits-table-card .btn-group[aria-label="Page size selector"] a');
        if (sizeLink && sizeLink.href) {
            e.preventDefault();
            loadUrlViaAjax(sizeLink.href);
            return;
        }

        // Pagination links
        const pageLink = e.target.closest('#visits-table-card .pagination a.page-link');
        if (pageLink && pageLink.href) {
            e.preventDefault();
            loadUrlViaAjax(pageLink.href);
            return;
        }

        // Table header sort links
        const sortLink = e.target.closest('#visits-table-card th a.sortable-th');
        if (sortLink && sortLink.href) {
            e.preventDefault();
            loadUrlViaAjax(sortLink.href);
            return;
        }

        // Filter tabs (All visitors, Feedback completed, Awaiting feedback)
        const tabLink = e.target.closest('.saas-filter-pills a.saas-pill-btn');
        if (tabLink && tabLink.href) {
            e.preventDefault();
            loadUrlViaAjax(tabLink.href);
            return;
        }
    });

    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.url) {
            loadUrlViaAjax(e.state.url, false);
        } else {
            loadUrlViaAjax(window.location.href, false);
        }
    });
});
</script>
@endpush
