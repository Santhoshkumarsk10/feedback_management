@extends('layouts.app')
@section('title', 'Analytics & Reports')
@section('page_title', 'Performance Reports & Exports')
@section('page_subtitle', 'Comprehensive aggregated feedback reports by tour organizer, department, and survey criteria')

@section('topbar_actions')
    <div class="dropdown">
        <button class="btn-modern-primary btn-sm dropdown-toggle d-flex align-items-center gap-1 shadow-xs" 
                type="button" id="topbarExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-download"></i>
            <span>Export Report</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" aria-labelledby="topbarExportDropdown" style="min-width: 215px;">
            <li class="dropdown-header small text-uppercase fw-bold text-muted px-3">Select Export Format</li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'excel'])) }}">
                    <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">Excel (.xlsx)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Formatted spreadsheet</div>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'pdf'])) }}" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">PDF Document (.pdf)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Printable executive report</div>
                    </div>
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'csv'])) }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-primary fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">CSV File (.csv)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Raw table records</div>
                    </div>
                </a>
            </li>
        </ul>
    </div>
@endsection

@section('content')
@php
    $totalVisits = $rows->sum('visits_count');
    $totalFeedbacks = $rows->sum('feedbacks_count');
    $overallRate = $totalVisits > 0 ? min(100, round(($totalFeedbacks / $totalVisits) * 100)) : 0;
    $validRatings = $rows->filter(fn($r) => !is_null($r->avg_rating) && $r->avg_rating > 0);
    $meanRating = $validRatings->count() > 0 ? round($validRatings->avg('avg_rating'), 2) : 0;
@endphp



<!-- Aggregate Metric Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card-widget cyan">
            <div class="stat-widget-header">
                <span class="stat-label-text">Tours In Period</span>
                <div class="stat-icon-bubble cyan">
                    <i class="bi bi-compass-fill"></i>
                </div>
            </div>
            <div class="stat-metric-number">{{ number_format($totalVisits) }}</div>
            <span class="stat-pill-trend neutral"><i class="bi bi-calendar3"></i> Total Tours</span>
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

<!-- Date Filter Card & Tabs -->
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
            <a href="{{ route('reports.index', ['report_tab' => $reportTab]) }}" class="filter-tab-btn {{ $isAllTime ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>All Time</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $startThisMonth, 'to' => $endThisMonth]) }}" class="filter-tab-btn {{ $isThisMonth ? 'active' : '' }}">
                <i class="bi bi-calendar-month"></i>
                <span>This Month</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $start30Days, 'to' => $endToday]) }}" class="filter-tab-btn {{ $is30Days ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i>
                <span>Last 30 Days</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $startYear, 'to' => $endYear]) }}" class="filter-tab-btn {{ $isThisYear ? 'active' : '' }}">
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
            <input type="hidden" name="report_tab" value="{{ $reportTab }}">


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

            @if(request()->hasAny(['from', 'to']))
                <a href="{{ route('reports.index', ['report_tab' => $reportTab]) }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Report Navigation Tabs -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'staff'])) }}" 
           class="btn btn-sm d-flex align-items-center gap-2 py-2 px-3 fw-bold rounded-pill transition-all {{ $reportTab === 'staff' ? 'btn-primary shadow-sm' : 'btn-light border text-secondary' }}">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Staff Performance</span>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'low_rating'])) }}" 
           class="btn btn-sm d-flex align-items-center gap-2 py-2 px-3 fw-bold rounded-pill transition-all {{ $reportTab === 'low_rating' ? 'btn-danger shadow-sm' : 'btn-light border text-secondary' }}">
            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            <span>Low-Rating Reviews (≤ 2★)</span>
            <span class="badge {{ $reportTab === 'low_rating' ? 'bg-white text-danger' : 'bg-danger text-white' }} rounded-pill ms-1">{{ $lowRatings->count() }}</span>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'pending'])) }}" 
           class="btn btn-sm d-flex align-items-center gap-2 py-2 px-3 fw-bold rounded-pill transition-all {{ $reportTab === 'pending' ? 'btn-warning text-dark shadow-sm' : 'btn-light border text-secondary' }}">
            <i class="bi bi-clock-history text-warning"></i>
            <span>Pending Feedback Queue</span>
            <span class="badge {{ $reportTab === 'pending' ? 'bg-dark text-warning' : 'bg-secondary text-white' }} rounded-pill ms-1">{{ $pendingVisits->count() }}</span>
        </a>
    </div>

    <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center gap-1 rounded-pill px-3 shadow-xs" 
                type="button" id="tabExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-download"></i>
            <span>Export Report</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" aria-labelledby="tabExportDropdown" style="min-width: 220px;">
            <li class="dropdown-header small text-uppercase fw-bold text-muted px-3">Available Formats</li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'excel'])) }}" id="btnExportExcel">
                    <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">Excel (.xlsx)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Formatted spreadsheet with styles</div>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'pdf'])) }}" target="_blank" id="btnExportPdf">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">PDF Document (.pdf)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Printable executive format</div>
                    </div>
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" 
                   href="{{ route('reports.export', array_merge(request()->query(), ['type' => $reportTab, 'format' => 'csv'])) }}" id="btnExportCsv">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-primary fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">CSV (.csv)</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">Raw comma-separated data</div>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>

@if($reportTab === 'staff')

    <!-- Staff Performance Section -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-person-lines-fill text-primary"></i>
                <span>Staff & Organizer Performance Review</span>
            </div>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'staff'])) }}" class="btn-modern-secondary btn-sm py-1 px-2" title="Download spreadsheet">
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
            No rating questions available for the selected dates.
        </div>
    @endforelse

@elseif($reportTab === 'low_rating')
    <!-- Low-Rating Incident Reviews Section (Ratings <= 2 stars per Section 6.8) -->
    <div class="card-modern mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="card-title text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Low-Rating Incident Reviews (Overall Rating ≤ 2 Stars)</span>
                </div>
                <div class="small text-muted mt-1">
                    Critical feedback requiring operational investigation, root-cause analysis, and management follow-up (BRS Section 6.8).
                </div>
            </div>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'low_rating'])) }}" class="btn-modern-danger btn-sm py-1 px-3">
                <i class="bi bi-download"></i> Export Incidents CSV
            </a>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Rating</th>
                        <th>Visitor Details</th>
                        <th>Host Staff</th>
                        <th>Submission Mode</th>
                        <th style="min-width: 250px;">Comments / Issues</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lowRatings as $f)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $f->submitted_at ? $f->submitted_at->format('d M Y') : '—' }}</div>
                            <div class="small text-muted">{{ $f->submitted_at ? $f->submitted_at->format('h:i A') : '' }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2 py-1 fs-6">
                                    {{ $f->overall_rating }} ★
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $f->visit?->visitor_name ?? '—' }}</div>
                            <div class="small text-muted">{{ $f->visit?->visitor_company ?? 'Individual' }}</div>
                            @if($f->visit?->visitor_mobile)
                                <div class="small text-muted"><i class="bi bi-telephone"></i> {{ $f->visit->visitor_mobile }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $f->organizer?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td>
                            @if($f->is_staff_assisted)
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-1 px-2">
                                    <i class="bi bi-person-check-fill text-warning"></i> Staff Assisted
                                </span>
                                @if($f->submittedBy)
                                    <div class="small text-muted mt-1">by {{ $f->submittedBy->name }}</div>
                                @endif
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                                    <i class="bi bi-person-fill text-success"></i> Direct Visitor
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="p-2 rounded bg-light border text-dark small" style="white-space: pre-wrap; max-height: 100px; overflow-y: auto;">
                                {{ $f->comments ?: 'No written feedback comments provided.' }}
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('feedbacks.show', $f) }}" class="btn btn-sm btn-outline-primary" title="View Full Survey Responses">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <div class="stat-icon-bubble emerald mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Low-Rating Incidents</h6>
                                <p class="text-muted small mb-0">No ratings of 1 or 2 stars were recorded in the selected date range.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($reportTab === 'pending')
    <!-- Pending Feedback Audit Queue Section (Section 6.5 & 6.8) -->
    <div class="card-modern mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="card-title text-warning text-dark">
                    <i class="bi bi-clock-history text-warning"></i>
                    <span>Pending Feedback Audit Queue (Visits Without Feedback)</span>
                </div>
                <div class="small text-muted mt-1">
                    Complete listing of all registered plant visitors who have not submitted feedback yet (BRS Section 6.5 & 6.8).
                </div>
            </div>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'pending'])) }}" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-download"></i> Export Pending CSV
            </a>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Visit Date</th>
                        <th>Visitor ID / Pass</th>
                        <th>Visitor Details</th>
                        <th>Host Staff</th>
                        <th>Department</th>
                        <th>Plant Gate Status</th>
                        <th>Exit Elapsed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pendingVisits as $v)
                    <tr class="{{ $v->is_exceeded_exit_threshold ? 'table-danger-subtle' : '' }}" style="{{ $v->is_exceeded_exit_threshold ? 'background-color: #fff5f5;' : '' }}">
                        <td>
                            <div class="fw-semibold text-dark">{{ $v->visit_date ? $v->visit_date->format('d M Y') : '—' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                {{ $v->visitor_code ?: 'VIS-' . $v->id }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $v->visitor_name }}</div>
                            <div class="small text-muted">{{ $v->visitor_company ?: 'Individual' }}</div>
                            @if($v->visitor_mobile)
                                <div class="small text-muted"><i class="bi bi-telephone"></i> {{ $v->visitor_mobile }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $v->organizer?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td>
                            @if($v->department)
                                <span class="badge-modern badge-indigo">{{ $v->department }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($v->out_time)
                                <span class="badge bg-secondary text-white py-1 px-2">
                                    <i class="bi bi-door-closed"></i> Checked Out
                                </span>
                                <div class="small text-muted mt-1">{{ $v->formatted_out_time }}</div>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                                    <i class="bi bi-building"></i> Inside Plant
                                </span>
                                @if($v->in_time)
                                    <div class="small text-muted mt-1">In: {{ $v->formatted_in_time }}</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($v->out_time)
                                <div class="fw-bold {{ $v->is_exceeded_exit_threshold ? 'text-danger' : 'text-secondary' }}">
                                    {{ $v->time_since_exit ?? 'Just now' }}
                                </div>
                                @if($v->is_exceeded_exit_threshold)
                                    <span class="badge bg-danger text-white small py-1 px-2 mt-1">
                                        <i class="bi bi-exclamation-octagon-fill"></i> > 2 hrs post-exit
                                    </span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('visits.show', $v) }}" class="btn btn-sm btn-outline-primary" title="View Visit">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <div class="stat-icon-bubble emerald mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    <i class="bi bi-check2-all"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Pending Feedback</h6>
                                <p class="text-muted small mb-0">All visitor tours have completed their feedback for this period.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

