@extends('layouts.app')
@section('title', 'Plant Visitors Log')
@section('page_title', 'Plant Visitor Directory')
@section('page_subtitle', 'Browse all plant visitors, assigned organizers, and feedback statuses')

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>All Visitors</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'completed'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'completed' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Feedback Completed</span>
                <span class="filter-tab-badge">{{ $tabCounts['completed'] ?? 0 }}</span>
            </a>
            <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'pending'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'pending' ? 'active' : '' }}">
                <i class="bi bi-clock-history text-amber-500"></i>
                <span>Awaiting Feedback</span>
                <span class="filter-tab-badge">{{ $tabCounts['pending'] ?? 0 }}</span>
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
        <form method="GET" action="{{ route('visits.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <div class="filter-input-search">
                <i class="bi bi-search"></i>
                <input name="q" value="{{ request('q') }}" class="form-control form-control-modern form-control-sm" placeholder="Search visitor name, company, mobile...">
            </div>

            <div style="min-width: 170px;">
                <select name="organizer_id" class="form-select form-select-modern form-select-sm">
                    <option value="">All Organizers</option>
                    @foreach($organizers as $o)
                        <option value="{{ $o->id }}" @selected(request('organizer_id') == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">From:</span>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-modern form-control-sm" title="From Date" style="width: 135px;">
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">To:</span>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-modern form-control-sm" title="To Date" style="width: 135px;">
            </div>

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['q', 'organizer_id', 'from', 'to', 'tab']))
                <a href="{{ route('visits.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Visitors Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-person-badge-fill text-primary"></i>
            <span>Recorded Plant Visits</span>
        </div>
        <span class="badge-modern badge-slate">{{ $visits->total() }} Visits Total</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Visit Date</th>
                    <th>Visitor Information</th>
                    <th>Assigned Organizer</th>
                    <th>Purpose of Visit</th>
                    <th class="text-end">Feedback Status</th>
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
                        <div class="fw-bold text-dark fs-6">{{ $v->visitor_name }}</div>
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
                        <div class="d-flex align-items-center">
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
                                <a href="{{ route('feedbacks.show', $v->feedback) }}" class="btn-action-icon view" title="View feedback submission">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </div>
                        @else
                            <span class="badge-modern badge-slate text-muted">
                                <i class="bi bi-hourglass-split"></i> Awaiting Feedback
                            </span>
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
</div>

<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $visits->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $visits->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $visits->total() }}</span> visits (10 per page)
    </div>
    <div>
        {{ $visits->links() }}
    </div>
</div>
@endsection
