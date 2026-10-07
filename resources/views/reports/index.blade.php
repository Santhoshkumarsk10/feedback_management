@extends('layouts.app')
@section('title', 'Analytics & Reports')
@section('page_title', 'Performance Reports & Exports')
@section('page_subtitle', 'Comprehensive aggregated feedback reports by tour organizer, shift, and survey criteria')

@section('topbar_actions')
    <a href="{{ route('reports.export', request()->query()) }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-file-earmark-arrow-down-fill"></i>
        <span>Export CSV Report</span>
    </a>
@endsection

@section('content')
@php
    $totalVisits = $rows->sum('visits_count');
    $totalFeedbacks = $rows->sum('feedbacks_count');
    $overallRate = $totalVisits > 0 ? min(100, round(($totalFeedbacks / $totalVisits) * 100)) : 0;
    $validRatings = $rows->filter(fn($r) => !is_null($r->avg_rating) && $r->avg_rating > 0);
    $meanRating = $validRatings->count() > 0 ? round($validRatings->avg('avg_rating'), 2) : 0;
@endphp

@if($selectedShift)
<div class="alert alert-info border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%); border-left: 4px solid var(--shibaura-blue) !important;">
    <div class="d-flex align-items-center gap-3">
        <span class="stat-icon-bubble shibaura" style="width: 40px; height: 40px; font-size: 1.1rem;">
            <i class="bi bi-clock-history"></i>
        </span>
        <div>
            <div class="fw-bold text-dark fs-6">Shift Filter Applied: <span class="text-primary">{{ $selectedShift->name }} ({{ $selectedShift->formatted_24h_range }})</span></div>
            <div class="small text-muted">{{ $selectedShift->description ?? 'Reviewing tour throughput, response metrics, and staff performance for this operational shift.' }}</div>
        </div>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('reports.index', request()->except('shift_id')) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-x-circle"></i>
            <span>Clear Shift Filter (Show All)</span>
        </a>
    </div>
</div>
@endif

<!-- Aggregate Metric Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card-widget cyan">
            <div class="stat-widget-header">
                <span class="stat-label-text">{{ $selectedShift ? $selectedShift->name . ' Tours' : 'Tours In Period' }}</span>
                <div class="stat-icon-bubble cyan">
                    <i class="bi bi-compass-fill"></i>
                </div>
            </div>
            <div class="stat-metric-number">{{ number_format($totalVisits) }}</div>
            <span class="stat-pill-trend neutral"><i class="bi bi-calendar3"></i> {{ $selectedShift ? $selectedShift->name : 'Total Tours' }}</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card-widget emerald">
            <div class="stat-widget-header">
                <span class="stat-label-text">Feedback Rate</span>
                <div class="stat-icon-bubble emerald">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <div class="stat-metric-number">{{ $overallRate }}%</div>
            <span class="stat-pill-trend up"><i class="bi bi-check-circle"></i> {{ $totalFeedbacks }} Responses</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card-widget amber">
            <div class="stat-widget-header">
                <span class="stat-label-text">Period Mean Score</span>
                <div class="stat-icon-bubble amber">
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
            <div class="stat-metric-number">{{ number_format($meanRating, 1) }}<span class="fs-5 text-muted fw-normal">/5</span></div>
            <span class="stat-pill-trend {{ $meanRating >= 4 ? 'up' : 'neutral' }}">
                <i class="bi bi-trophy"></i> Overall Quality
            </span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card-widget indigo">
            <div class="stat-widget-header">
                <span class="stat-label-text">Active Staff</span>
                <div class="stat-icon-bubble indigo">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="stat-metric-number">{{ $rows->filter(fn($r) => $r->visits_count > 0)->count() }}</div>
            <span class="stat-pill-trend neutral"><i class="bi bi-person-check"></i> On Duty</span>
        </div>
    </div>
</div>

<!-- Shift Performance Comparison Widget (Shift A vs Shift B vs Shift C) -->
<div class="card-modern mb-4">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="card-title">
            <i class="bi bi-layers-fill text-primary"></i>
            <span>Shift-Wise Performance Overview (Shift A / B / C)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-modern badge-slate"><i class="bi bi-clock"></i> {{ $shifts->count() }} Operational Shifts</span>
            @if(request('shift_id'))
                <a href="{{ route('reports.index', request()->except('shift_id')) }}" class="badge-modern badge-indigo text-decoration-none">
                    <i class="bi bi-arrow-repeat"></i> Reset Shift Filter
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            @foreach($shiftStats as $st)
                @php
                    $isThisShiftSelected = (string)request('shift_id') === (string)$st['id'];
                    $colorTheme = match($st['code']) {
                        'SHIFT-A' => 'cyan',
                        'SHIFT-B' => 'amber',
                        'SHIFT-C' => 'purple',
                        default => 'indigo'
                    };
                @endphp
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 h-100 transition-all {{ $isThisShiftSelected ? 'border-primary shadow-sm bg-white' : 'bg-light border' }}" 
                         style="border-width: {{ $isThisShiftSelected ? '2px' : '1px' }}; transition: all 0.2s ease;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="stat-icon-bubble {{ $colorTheme }}" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="bi bi-clock"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $st['name'] }}</h6>
                                    <span class="small text-muted">{{ $st['time_range'] }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                @if($st['is_active_now'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small py-1 px-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-record-fill text-success"></i> Active
                                    </span>
                                @endif
                                @if($isThisShiftSelected)
                                    <span class="badge bg-primary text-white small py-1 px-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-check-circle-fill"></i> Filtered
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-2 text-center my-2">
                            <div class="col-4">
                                <div class="p-2 rounded bg-white border">
                                    <div class="text-muted small" style="font-size: 0.7rem;">Tours</div>
                                    <div class="fw-bold text-dark fs-6">{{ $st['visits_count'] }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-white border">
                                    <div class="text-muted small" style="font-size: 0.7rem;">Feedbacks</div>
                                    <div class="fw-bold text-dark fs-6">{{ $st['feedbacks_count'] }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-white border">
                                    <div class="text-muted small" style="font-size: 0.7rem;">Avg Score</div>
                                    <div class="fw-bold text-dark fs-6">
                                        {{ $st['avg_rating'] > 0 ? number_format($st['avg_rating'], 1) : '—' }}
                                        <i class="bi bi-star-fill text-warning" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 small">
                                <span class="text-muted" style="font-size: 0.72rem;">Feedback Rate</span>
                                <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $st['response_rate'] }}%</span>
                            </div>
                            <div class="progress-modern" style="height: 6px;">
                                <div class="progress-bar-emerald" style="width: {{ $st['response_rate'] }}%; height: 100%;"></div>
                            </div>
                        </div>

                        @if($isThisShiftSelected)
                            <a href="{{ route('reports.index', request()->except('shift_id')) }}" 
                               class="btn btn-sm btn-outline-secondary w-100 py-1" style="font-size: 0.8rem;">
                                <i class="bi bi-x-circle me-1"></i> Clear Shift Filter
                            </a>
                        @else
                            <a href="{{ route('reports.index', array_merge(request()->query(), ['shift_id' => $st['id']])) }}" 
                               class="btn btn-sm btn-outline-primary w-100 py-1" style="font-size: 0.8rem;">
                                <i class="bi bi-funnel me-1"></i> Filter {{ $st['name'] }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Date & Shift Filter Card & Tabs -->
<div class="filter-card-wrapper mb-4">
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            @php
                $isAllTime = !request('from') && !request('to');
                $startThisMonth = now()->startOfMonth()->toDateString();
                $endThisMonth = now()->endOfMonth()->toDateString();
                $isThisMonth = request('from') === $startThisMonth && request('to') === $endThisMonth;
                $start30Days = now()->subDays(30)->toDateString();
                $endToday = now()->toDateString();
                $is30Days = request('from') === $start30Days && request('to') === $endToday;
                $startYear = now()->startOfYear()->toDateString();
                $endYear = now()->endOfYear()->toDateString();
                $isThisYear = request('from') === $startYear && request('to') === $endYear;
            @endphp
            <a href="{{ route('reports.index', array_merge(request()->only(['shift_id']))) }}" class="filter-tab-btn {{ $isAllTime ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>All Time</span>
            </a>
            <a href="{{ route('reports.index', array_merge(request()->only(['shift_id']), ['from' => $startThisMonth, 'to' => $endThisMonth])) }}" class="filter-tab-btn {{ $isThisMonth ? 'active' : '' }}">
                <i class="bi bi-calendar-month"></i>
                <span>This Month</span>
            </a>
            <a href="{{ route('reports.index', array_merge(request()->only(['shift_id']), ['from' => $start30Days, 'to' => $endToday])) }}" class="filter-tab-btn {{ $is30Days ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i>
                <span>Last 30 Days</span>
            </a>
            <a href="{{ route('reports.index', array_merge(request()->only(['shift_id']), ['from' => $startYear, 'to' => $endYear])) }}" class="filter-tab-btn {{ $isThisYear ? 'active' : '' }}">
                <i class="bi bi-calendar4-range"></i>
                <span>This Year</span>
            </a>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">
                <i class="bi bi-funnel"></i> Filtered Analysis
            </span>
        </div>
    </div>

    <div class="filter-controls-body">
        <form method="GET" action="{{ route('reports.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="reportsFilterForm">
            <x-custom-select 
                name="shift_id" 
                :value="request('shift_id')" 
                placeholder="All Shifts" 
                search-placeholder="Search shifts..." 
                icon="bi-clock-history" 
                min-width="190px" 
                :options="collect([['value' => '', 'label' => 'All Shifts']])->concat($shifts->map(fn($s) => ['value' => $s->id, 'label' => $s->name . ' (' . $s->start_time_short . ' – ' . $s->end_time_short . ')']))" 
                auto-submit
            />

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted fw-bold">From:</span>
                <x-date-picker name="from" :value="request('from')" placeholder="dd/mm/yyyy" title="From Date" />
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted fw-bold">To:</span>
                <x-date-picker name="to" :value="request('to')" placeholder="dd/mm/yyyy" title="To Date" />
            </div>

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-check2"></i> Apply Filters
            </button>

            @if(request()->hasAny(['from', 'to', 'shift_id']))
                <a href="{{ route('reports.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Staff Performance Section -->
<div class="card-modern mb-4">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-person-lines-fill text-primary"></i>
            <span>Staff & Organizer Performance Review</span>
            @if($selectedShift)
                <span class="badge-modern badge-blue ms-2">{{ $selectedShift->name }} ({{ $selectedShift->formatted_24h_range }})</span>
            @endif
        </div>
        <a href="{{ route('reports.export', request()->query()) }}" class="btn-modern-secondary btn-sm py-1 px-2" title="Download spreadsheet">
            <i class="bi bi-download"></i> CSV
        </a>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Staff / Organizer</th>
                    <th>Department</th>
                    <th>Visits Handled</th>
                    <th>Feedbacks</th>
                    <th style="min-width: 160px;">Response Rate</th>
                    <th class="text-end">Average Score</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                @php
                    $respPct = $r->visits_count ? min(100, round($r->feedbacks_count / $r->visits_count * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="user-avatar-chip">
                                {{ strtoupper(substr($r->name, 0, 2)) }}
                            </span>
                            <span class="fw-bold text-dark">{{ $r->name }}</span>
                        </div>
                    </td>
                    <td>
                        @if($r->department)
                            <span class="badge-modern badge-indigo">{{ $r->department }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $r->visits_count }}</span>
                        <span class="text-muted small">tours</span>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $r->feedbacks_count }}</span>
                        <span class="text-muted small">submissions</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-modern flex-grow-1">
                                <div class="progress-bar-emerald" style="width: {{ $respPct }}%; height: 100%;"></div>
                            </div>
                            <span class="small fw-bold text-dark">{{ $respPct }}%</span>
                        </div>
                    </td>
                    <td class="text-end">
                        @include('partials.rating', ['value' => $r->avg_rating])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">No staff tour records found matching the selected filter criteria.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Question-Wise Breakdown Section (Grouped Section-Wise) -->
@php
    $groupedStats = collect($questionStats)->groupBy('section');
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
        <i class="bi bi-star-half text-warning me-1"></i> Section-Wise Rating Criteria Analysis
        @if($selectedShift)
            <span class="badge-modern badge-blue ms-2 fs-6 fw-normal"><i class="bi bi-clock"></i> {{ $selectedShift->name }}</span>
        @endif
    </h5>
    <span class="badge-modern badge-shibaura">{{ count($questionStats) }} Rating Parameters</span>
</div>

@forelse($groupedStats as $secName => $items)
    <div class="card-modern mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="d-flex align-items-center gap-2">
                <span class="stat-icon-bubble shibaura" style="width: 32px; height: 32px; font-size: 0.95rem;">
                    <i class="bi bi-bar-chart-steps"></i>
                </span>
                <span class="fw-bold text-dark fs-6">{{ $secName }}</span>
            </div>
            <span class="badge-modern badge-shibaura">{{ $items->count() }} Parameters</span>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 52%;">Rating Parameter</th>
                        <th>Responses</th>
                        <th style="min-width: 180px;">Satisfaction Index</th>
                        <th class="text-end">Average Score</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $s)
                    @php
                        $pct = $s['avg'] ? min(100, round(($s['avg'] / 5.0) * 100)) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $s['question'] }}</div>
                        </td>
                        <td>
                            <span class="badge-modern badge-slate">{{ $s['count'] }} responses</span>
                        </td>
                        <td>
                            @if($s['count'] > 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-modern flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar-shibaura" style="width: {{ $pct }}%; height: 100%; background: linear-gradient(90deg, #38bdf8, #06539d);"></div>
                                    </div>
                                    <span class="small fw-bold text-dark" style="font-size: 0.75rem;">{{ $pct }}%</span>
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @include('partials.rating', ['value' => $s['count'] ? $s['avg'] : null])
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card-modern p-4 text-center text-muted">
        No rating questions available for the selected dates or shift.
    </div>
@endforelse
@endsection
