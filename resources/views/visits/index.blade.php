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
>>>>>>> 4864f174f20811493741e0acdecfb5be9a962a08
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
<div class="saas-table-card">
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
                    <th style="width: 140px;">VISIT DATE</th>
                    <th>VISITOR INFORMATION</th>
                    <th>ASSIGNED STAFF</th>
                    <th>PURPOSE</th>
                    <th class="text-end">FEEDBACK STATUS & ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            @forelse($visits as $v)
                <tr>
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
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6">{{ $v->visitor_name }}</span>
                            @if($v->visitor_code)
                                <span class="saas-code-badge">
                                    <i class="bi bi-qr-code me-1"></i>{{ $v->visitor_code }}
                                </span>
                            @endif
                        </div>
                        @if($v->visitor_designation)
                            <div class="small text-secondary fw-semibold">{{ $v->visitor_designation }}</div>
                        @endif
                        <div class="small text-muted d-flex align-items-center gap-2 mt-1">
                            @if($v->visitor_company)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1"></i> {{ $v->visitor_company }}
                                </span>
                            @endif
                            @if($v->visitor_mobile)
                                <span><i class="bi bi-telephone text-muted"></i> {{ $v->visitor_mobile }}</span>
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
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-slate-300"></i>
                        No visit records match the selected filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing <b>{{ $visits->firstItem() ?? 0 }}</b> to <b>{{ $visits->lastItem() ?? 0 }}</b> of <b>{{ $visits->total() }}</b> visitors ({{ $visits->perPage() }} per page)@if($visits->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $visits->links() }}
        </div>
    </div>
</div>
@endsection
