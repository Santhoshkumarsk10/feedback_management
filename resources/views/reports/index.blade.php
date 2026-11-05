@extends('layouts.app')
@section('title', 'Analytics & Reports')
@section('page_title', 'Performance Reports & Exports')
@section('page_subtitle', 'Comprehensive aggregated feedback reports by date range, department, and more.')

@section('topbar_actions')
    @can('reportsexports.export')
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle d-flex align-items-center gap-1 rounded-pill px-3 shadow-xs" 
                    type="button" id="topbarExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download"></i>
                <span>Export Report</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 rounded-3 mt-1" aria-labelledby="topbarExportDropdown" style="min-width: 220px; font-size: 0.84rem;">
                <li class="dropdown-header small text-uppercase fw-bold text-muted px-3">Available Formats</li>
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
    @endcan
@endsection

@section('content')
@php
    $totalVisits = $rows->sum('visits_count');
    $totalFeedbacks = $rows->sum('feedbacks_count');
    $overallRate = $totalVisits > 0 ? min(100, round(($totalFeedbacks / $totalVisits) * 100)) : 0;
    $validRatings = $rows->filter(fn($r) => !is_null($r->avg_rating) && $r->avg_rating > 0);
    $meanRating = $validRatings->count() > 0 ? round($validRatings->avg('avg_rating'), 2) : 0;
    $activeStaffCount = $rows->filter(fn($r) => $r->visits_count > 0)->count();

    // Palette for avatar backgrounds
    $avatarPalette = ['#2563eb', '#7c3aed', '#059669', '#d97706', '#0891b2', '#e11d48'];
@endphp

<!-- SUMMARY METRIC CARDS ROW (4-Column Reference Design) -->
<div class="report-metrics-grid">
    <!-- 1. TOURS IN PERIOD -->
    <div class="report-metric-card">
        <div class="report-metric-content">
            <div class="report-metric-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="report-metric-lbl">TOURS IN PERIOD</div>
                <div class="report-metric-val">{{ number_format($totalVisits) }}</div>
                <div class="report-metric-pill blue">
                    <i class="bi bi-clipboard2-check"></i>
                    <span>Total Tours</span>
                </div>
            </div>
        </div>
        <div class="report-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
        <!-- Decorative subtle wave SVG -->
        <svg class="report-metric-wave" viewBox="0 0 110 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48C20 40 40 45 60 30C80 15 95 25 110 5V48H0Z" fill="#3b82f6" fill-opacity="0.12"/>
            <path d="M0 48C25 44 45 38 65 24C85 10 98 18 110 0V48H0Z" fill="#3b82f6" fill-opacity="0.08"/>
        </svg>
    </div>

    <!-- 2. FEEDBACK RATE -->
    <div class="report-metric-card">
        <div class="report-metric-content">
            <div class="report-metric-icon green">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div>
                <div class="report-metric-lbl">FEEDBACK RATE</div>
                <div class="report-metric-val">{{ $overallRate }}%</div>
                <div class="report-metric-pill green">
                    <i class="bi bi-chat-left-text"></i>
                    <span>{{ $totalFeedbacks }} Responses</span>
                </div>
            </div>
        </div>
        <div class="report-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
        <!-- Decorative subtle wave SVG -->
        <svg class="report-metric-wave" viewBox="0 0 110 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48C20 40 40 45 60 30C80 15 95 25 110 5V48H0Z" fill="#10b981" fill-opacity="0.12"/>
            <path d="M0 48C25 44 45 38 65 24C85 10 98 18 110 0V48H0Z" fill="#10b981" fill-opacity="0.08"/>
        </svg>
    </div>

    <!-- 3. PERIOD MEAN SCORE -->
    <div class="report-metric-card">
        <div class="report-metric-content">
            <div class="report-metric-icon amber">
                <i class="bi bi-star-fill"></i>
            </div>
            <div>
                <div class="report-metric-lbl">PERIOD MEAN SCORE</div>
                <div class="report-metric-val">{{ number_format($meanRating, 1) }}<span class="fs-6 text-muted fw-normal">/5</span></div>
                <div class="report-metric-pill amber">
                    <i class="bi bi-trophy"></i>
                    <span>Overall Quality</span>
                </div>
            </div>
        </div>
        <div class="report-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
        <!-- Decorative subtle wave SVG -->
        <svg class="report-metric-wave" viewBox="0 0 110 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48C20 40 40 45 60 30C80 15 95 25 110 5V48H0Z" fill="#f59e0b" fill-opacity="0.12"/>
            <path d="M0 48C25 44 45 38 65 24C85 10 98 18 110 0V48H0Z" fill="#f59e0b" fill-opacity="0.08"/>
        </svg>
    </div>

    <!-- 4. ACTIVE STAFF -->
    <div class="report-metric-card">
        <div class="report-metric-content">
            <div class="report-metric-icon purple">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <div class="report-metric-lbl">ACTIVE STAFF</div>
                <div class="report-metric-val">{{ number_format($activeStaffCount) }}</div>
                <div class="report-metric-pill purple">
                    <i class="bi bi-people"></i>
                    <span>On Duty</span>
                </div>
            </div>
        </div>
        <div class="report-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
        <!-- Decorative subtle wave SVG -->
        <svg class="report-metric-wave" viewBox="0 0 110 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48C20 40 40 45 60 30C80 15 95 25 110 5V48H0Z" fill="#8b5cf6" fill-opacity="0.12"/>
            <path d="M0 48C25 44 45 38 65 24C85 10 98 18 110 0V48H0Z" fill="#8b5cf6" fill-opacity="0.08"/>
        </svg>
    </div>
</div>

<!-- DATE PRESET & FILTER PANEL (Reference Design) -->
<div class="report-filter-panel">
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

    <div class="report-preset-row">
        <div class="report-preset-group">
            <a href="{{ route('reports.index', ['report_tab' => $reportTab]) }}" class="report-preset-btn {{ $isAllTime ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>All Time</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $startThisMonth, 'to' => $endThisMonth]) }}" class="report-preset-btn {{ $isThisMonth ? 'active' : '' }}">
                <i class="bi bi-calendar-month"></i>
                <span>This Month</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $start30Days, 'to' => $endToday]) }}" class="report-preset-btn {{ $is30Days ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i>
                <span>Last 30 Days</span>
            </a>
            <a href="{{ route('reports.index', ['report_tab' => $reportTab, 'from' => $startYear, 'to' => $endYear]) }}" class="report-preset-btn {{ $isThisYear ? 'active' : '' }}">
                <i class="bi bi-calendar4-range"></i>
                <span>This Year</span>
            </a>
        </div>

        <div class="report-filter-badge">
            <i class="bi bi-funnel text-secondary"></i>
            <span>Filtered Analysis</span>
        </div>
    </div>

    <div>
        <form method="GET" action="{{ route('reports.index') }}" class="d-flex flex-wrap align-items-center gap-2 m-0" id="reportsFilterForm">
            <input type="hidden" name="report_tab" value="{{ $reportTab }}">

            <x-date-range-picker 
                from-name="from" 
                to-name="to" 
                :from-value="request('from')" 
                :to-value="request('to')" 
                placeholder="Select Date Range" 
                title="Filter Report Period" 
                icon="bi-calendar3"
                auto-submit
            />

            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold rounded-3" style="height: 38px;">
                <i class="bi bi-funnel-fill me-1"></i> Apply Filters
            </button>

            @if(request()->hasAny(['from', 'to']))
                <a href="{{ route('reports.index', ['report_tab' => $reportTab]) }}" class="btn btn-outline-danger btn-sm px-3 rounded-3 d-inline-flex align-items-center gap-1" style="height: 38px;" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- REPORT NAVIGATION TABS ROW (Reference Design) -->
<div class="report-nav-row">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'staff'])) }}" 
           class="report-tab-btn {{ $reportTab === 'staff' ? 'active-blue' : '' }}">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Staff Performance</span>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'low_rating'])) }}" 
           class="report-tab-btn {{ $reportTab === 'low_rating' ? 'active-red' : '' }}">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Low-Rating Reviews (≤ 2★)</span>
            <span class="report-tab-badge {{ $reportTab === 'low_rating' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">{{ $lowRatings->count() }}</span>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_tab' => 'pending'])) }}" 
           class="report-tab-btn {{ $reportTab === 'pending' ? 'active-amber' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Pending Feedback Queue</span>
            <span class="report-tab-badge {{ $reportTab === 'pending' ? 'bg-white text-dark' : 'bg-warning text-dark' }} ms-1">{{ $pendingVisits->count() }}</span>
        </a>
    </div>

    @can('reportsexports.export')
        <div class="dropdown">
            <button class="btn-report-export-dropdown dropdown-toggle" type="button" id="tabExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download text-primary"></i>
                <span>Export Report</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 rounded-3 mt-1" aria-labelledby="tabExportDropdown" style="min-width: 220px; font-size: 0.84rem;">
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
    @endcan
</div>

@if($reportTab === 'staff')

    <!-- 1. STAFF & ORGANIZER PERFORMANCE REVIEW CARD (Reference Format Design) -->
    <div class="report-table-card">
        <div class="report-table-header">
            <div class="report-table-title">
                <i class="bi bi-people-fill text-primary"></i>
                <span>Staff &amp; Organizer Performance Review</span>
            </div>
            @can('reportsexports.export')
                <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'staff', 'format' => 'csv'])) }}" class="btn-report-csv" title="Download spreadsheet">
                    <i class="bi bi-download text-primary"></i>
                    <span>Download CSV</span>
                </a>
            @endcan
        </div>

        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">STAFF / ORGANIZER</th>
                        <th style="min-width: 190px;">DEPARTMENT</th>
                        <th style="min-width: 140px;">VISITS HANDLED</th>
                        <th style="min-width: 140px;">FEEDBACKS</th>
                        <th style="min-width: 150px;">RESPONSE RATE</th>
                        <th style="min-width: 140px;">AVERAGE SCORE</th>
                        <th style="min-width: 120px;" class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    @php
                        $respPct = $r->visits_count ? min(100, round($r->feedbacks_count / $r->visits_count * 100)) : 0;
                        $initials = strtoupper(substr($r->name, 0, 2));
                        $avatarBg = $avatarPalette[$loop->index % count($avatarPalette)];

                        // Department pill class
                        $deptLower = strtolower($r->department ?? '');
                        if (str_contains($deptLower, 'prod') || str_contains($deptLower, 'plant') || str_contains($deptLower, 'operat')) {
                            $deptPillClass = 'blue';
                            $deptIcon = 'bi-buildings';
                        } elseif (str_contains($deptLower, 'custom') || str_contains($deptLower, 'tour') || str_contains($deptLower, 'relat')) {
                            $deptPillClass = 'purple';
                            $deptIcon = 'bi-people';
                        } elseif (str_contains($deptLower, 'qual') || str_contains($deptLower, 'eng')) {
                            $deptPillClass = 'amber';
                            $deptIcon = 'bi-gear';
                        } else {
                            $deptPillClass = 'green';
                            $deptIcon = 'bi-briefcase';
                        }
                    @endphp
                    <tr>
                        <!-- 1. STAFF / ORGANIZER -->
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="report-avatar" style="background-color: {{ $avatarBg }};">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6" style="line-height: 1.25;">{{ $r->name }}</div>
                                    <div class="stat-sub-label">Staff / Organizer</div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. DEPARTMENT -->
                        <td>
                            @if($r->department)
                                <span class="dept-pill {{ $deptPillClass }}">
                                    <i class="bi {{ $deptIcon }}"></i>
                                    <span>{{ $r->department }}</span>
                                </span>
                            @else
                                <span class="dept-pill green">
                                    <i class="bi bi-briefcase"></i>
                                    <span>Management</span>
                                </span>
                            @endif
                        </td>

                        <!-- 3. VISITS HANDLED -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-people"></i>
                                <span>{{ $r->visits_count }}</span>
                            </div>
                            <div class="stat-sub-label">Total visits</div>
                        </td>

                        <!-- 4. FEEDBACKS -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-chat-left-dots"></i>
                                <span>{{ $r->feedbacks_count }}</span>
                            </div>
                            <div class="stat-sub-label">Received</div>
                        </td>

                        <!-- 5. RESPONSE RATE -->
                        <td>
                            <div class="response-rate-box">
                                <div class="rate-donut-ring {{ $respPct > 0 ? 'has-val' : '' }}">
                                    @if($respPct > 0)
                                        <i class="bi bi-check" style="font-size: 0.75rem; color: #10b981;"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6" style="line-height: 1.2;">{{ $respPct }}%</div>
                                    <div class="stat-sub-label">{{ $r->feedbacks_count }}/{{ $r->visits_count }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- 6. AVERAGE SCORE -->
                        <td>
                            <div class="score-row">
                                <i class="bi bi-star-fill text-warning"></i>
                                <span>{{ $r->avg_rating ? number_format($r->avg_rating, 1) : '0.0' }}/5</span>
                            </div>
                            <div class="score-sub">{{ $r->avg_rating ? 'Rated' : 'No ratings' }}</div>
                        </td>

                        <!-- 7. ACTIONS -->
                        <td class="text-end">
                            <button type="button" class="btn-report-view" data-bs-toggle="modal" data-bs-target="#staffDetailsModal_{{ $r->id }}">
                                <i class="bi bi-eye"></i>
                                <span>View Details</span>
                            </button>
                        </td>
                    </tr>

                    <!-- Modal for Staff Performance Details -->
                    <div class="modal fade" id="staffDetailsModal_{{ $r->id }}" tabindex="-1" aria-labelledby="staffDetailsModalLabel_{{ $r->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="modal-header border-bottom py-3 px-4 bg-light">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="report-avatar" style="background-color: {{ $avatarBg }}; width: 44px; height: 44px; font-size: 1rem;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-0">{{ $r->name }}</h5>
                                            <div class="fs-xs text-muted">{{ $r->department ?: 'Operations & Tours' }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="p-3 rounded-3 bg-light border text-center">
                                                <div class="text-muted small mb-1">Total Tours Handled</div>
                                                <div class="fs-4 fw-bold text-dark">{{ $r->visits_count }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded-3 bg-light border text-center">
                                                <div class="text-muted small mb-1">Feedbacks Received</div>
                                                <div class="fs-4 fw-bold text-success">{{ $r->feedbacks_count }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded-3 bg-light border text-center">
                                                <div class="text-muted small mb-1">Response Rate</div>
                                                <div class="fs-4 fw-bold text-primary">{{ $respPct }}%</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded-3 bg-light border text-center">
                                                <div class="text-muted small mb-1">Average Score</div>
                                                <div class="fs-4 fw-bold text-warning">
                                                    <i class="bi bi-star-fill text-warning me-1"></i>{{ $r->avg_rating ? number_format($r->avg_rating, 1) : '0.0' }} / 5
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top py-2 px-4 bg-light d-flex justify-content-between">
                                    <a href="{{ route('visits.index', ['organizer_id' => $r->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-list-ul me-1"></i> View Staff Visits
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No staff tour records found matching the selected filter criteria.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 fs-xs text-muted">
            <div>Showing 1 to {{ $rows->count() }} of {{ $rows->count() }} records</div>
        </div>
    </div>

    <!-- 2, 3, 4. QUESTION SECTIONS: SAME UNIFIED FORMAT DESIGN -->
    @php
        $groupedStats = collect($questionStats)->groupBy('section');
    @endphp

    @forelse($groupedStats as $secName => $items)
        @php
            $secLower = strtolower($secName);
            if (str_contains($secLower, 'section 2') || str_contains($secLower, 'overall experience')) {
                $secIcon = 'bi-star-fill text-warning';
                $secCategoryLabel = 'Overall Experience';
                $secBadgeClass = 'blue';
            } elseif (str_contains($secLower, 'section 3') || str_contains($secLower, 'facilities & activities')) {
                $secIcon = 'bi-building-check text-info';
                $secCategoryLabel = 'Facilities & Activities';
                $secBadgeClass = 'purple';
            } elseif (str_contains($secLower, 'section 4') || str_contains($secLower, 'technical centre')) {
                $secIcon = 'bi-cpu-fill text-success';
                $secCategoryLabel = 'Technical Centre Facilities';
                $secBadgeClass = 'green';
            } else {
                $secIcon = 'bi-bar-chart-steps text-primary';
                $secCategoryLabel = 'Questionnaire Criteria';
                $secBadgeClass = 'amber';
            }
        @endphp

        <div class="report-table-card">
            <div class="report-table-header">
                <div class="report-table-title">
                    <i class="bi {{ $secIcon }}"></i>
                    <span>{{ $secName }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.75rem;">
                        {{ $items->count() }} Parameters
                    </span>
                    @can('reportsexports.export')
                        <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'staff', 'format' => 'csv'])) }}" class="btn-report-csv" title="Download section CSV">
                            <i class="bi bi-download text-primary"></i>
                            <span>Download CSV</span>
                        </a>
                    @endcan
                </div>
            </div>

            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="min-width: 280px; width: 40%;">RATING PARAMETER</th>
                            <th style="min-width: 170px;">CATEGORY</th>
                            <th style="min-width: 140px;">RESPONSES</th>
                            <th style="min-width: 170px;">SATISFACTION INDEX</th>
                            <th style="min-width: 140px;">AVERAGE SCORE</th>
                            <th style="min-width: 120px;" class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $s)
                        @php
                            $pct = $s['avg'] ? min(100, round(($s['avg'] / 5.0) * 100)) : 0;
                            $qIndex = $loop->iteration;
                            $qInitials = 'Q' . $qIndex;
                            $qAvatarBg = $avatarPalette[($qIndex - 1) % count($avatarPalette)];
                        @endphp
                        <tr>
                            <!-- 1. RATING PARAMETER -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="report-avatar" style="background-color: {{ $qAvatarBg }};">
                                        {{ $qInitials }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6" style="line-height: 1.25;">{{ $s['question'] }}</div>
                                        <div class="stat-sub-label">Parameter #{{ $qIndex }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. CATEGORY -->
                            <td>
                                <span class="dept-pill {{ $secBadgeClass }}">
                                    <i class="bi {{ $secIcon }}"></i>
                                    <span>{{ $secCategoryLabel }}</span>
                                </span>
                            </td>

                            <!-- 3. RESPONSES -->
                            <td>
                                <div class="stat-primary-row">
                                    <i class="bi bi-chat-left-dots"></i>
                                    <span>{{ $s['count'] }}</span>
                                </div>
                                <div class="stat-sub-label">Submissions</div>
                            </td>

                            <!-- 4. SATISFACTION INDEX -->
                            <td>
                                <div class="response-rate-box">
                                    <div class="rate-donut-ring {{ $pct > 0 ? 'has-val' : '' }}">
                                        @if($pct > 0)
                                            <i class="bi bi-check" style="font-size: 0.75rem; color: #10b981;"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1" style="max-width: 140px;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark fs-6" style="line-height: 1.2;">{{ $pct }}%</span>
                                            <span class="fs-xs text-muted">{{ $s['count'] > 0 ? number_format($s['avg'], 1) . '/5.0' : '0/5.0' }}</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 999px; background: #e2e8f0;">
                                            <div class="progress-bar" style="width: {{ $pct }}%; border-radius: 999px; background: linear-gradient(90deg, #3b82f6, #1d4ed8);"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 5. AVERAGE SCORE -->
                            <td>
                                <div class="score-row">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span>{{ $s['count'] ? number_format($s['avg'], 1) : '0.0' }}/5</span>
                                </div>
                                <div class="score-sub">{{ $s['count'] ? 'Rated' : 'No ratings' }}</div>
                            </td>

                            <!-- 6. ACTIONS -->
                            <td class="text-end">
                                <button type="button" class="btn-report-view" data-bs-toggle="modal" data-bs-target="#questionModal_{{ $loop->parent->index }}_{{ $loop->index }}">
                                    <i class="bi bi-eye"></i>
                                    <span>View Details</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for Question Details -->
                        <div class="modal fade" id="questionModal_{{ $loop->parent->index }}_{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="report-avatar" style="background-color: {{ $qAvatarBg }}; width: 44px; height: 44px; font-size: 1rem;">
                                                {{ $qInitials }}
                                            </div>
                                            <div>
                                                <h5 class="modal-title fw-bold text-dark mb-0">{{ $secName }}</h5>
                                                <div class="fs-xs text-muted">Parameter #{{ $qIndex }} Evaluation</div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="p-3 rounded-3 bg-light border mb-3">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Question Statement</div>
                                            <div class="fw-bold text-dark fs-6">{{ $s['question'] }}</div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="p-3 rounded-3 bg-light border text-center">
                                                    <div class="text-muted small mb-1">Total Responses</div>
                                                    <div class="fs-4 fw-bold text-dark">{{ $s['count'] }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-3 rounded-3 bg-light border text-center">
                                                    <div class="text-muted small mb-1">Satisfaction Index</div>
                                                    <div class="fs-4 fw-bold text-primary">{{ $pct }}%</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-3 rounded-3 bg-light border text-center">
                                                    <div class="text-muted small mb-1">Average Rating</div>
                                                    <div class="fs-4 fw-bold text-warning">
                                                        <i class="bi bi-star-fill text-warning me-1"></i>{{ $s['count'] ? number_format($s['avg'], 2) : '0.00' }} / 5.00
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top py-2 px-4 bg-light">
                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 fs-xs text-muted">
                <div>Showing 1 to {{ $items->count() }} of {{ $items->count() }} parameters</div>
            </div>
        </div>
    @empty
        <div class="report-table-card p-4 text-center text-muted">
            <i class="bi bi-info-circle fs-3 d-block mb-2 text-slate-400"></i>
            No rating questions available for the selected dates.
        </div>
    @endforelse

@elseif($reportTab === 'low_rating')

    <!-- Low-Rating Incident Reviews Section (Same Unified Design) -->
    <div class="report-table-card">
        <div class="report-table-header">
            <div class="report-table-title text-danger">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <span>Low-Rating Incident Reviews (Overall Rating ≤ 2 Stars)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.75rem;">
                    {{ $lowRatings->count() }} Incidents @if($lowRatings->count() >= 1000)(Recent 1,000 max)@endif
                </span>
                @can('reportsexports.export')
                    <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'low_rating', 'format' => 'csv'])) }}" class="btn-report-csv text-danger border-danger-subtle" title="Export Incidents CSV">
                        <i class="bi bi-download text-danger"></i>
                        <span>Export CSV</span>
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">VISITOR DETAILS</th>
                        <th style="min-width: 150px;">SUBMISSION DATE</th>
                        <th style="min-width: 160px;">HOST STAFF</th>
                        <th style="min-width: 140px;">MODE</th>
                        <th style="min-width: 250px;">COMMENTS / REMARKS</th>
                        <th style="min-width: 120px;">RATING</th>
                        <th style="min-width: 110px;" class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lowRatings as $f)
                    @php
                        $nameParts = preg_split('/\s+/', trim($f->visit?->visitor_name ?? 'Guest'));
                        $fInitials = strtoupper(substr($nameParts[0] ?? 'G', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                        $fAvatarBg = '#ef4444';
                    @endphp
                    <tr>
                        <!-- 1. VISITOR DETAILS -->
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="report-avatar" style="background-color: {{ $fAvatarBg }};">
                                    {{ $fInitials }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6" style="line-height: 1.25;">{{ $f->visit?->visitor_name ?? '—' }}</div>
                                    <div class="stat-sub-label">{{ $f->visit?->visitor_company ?? 'Individual' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. SUBMISSION DATE -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $f->submitted_at ? $f->submitted_at->format('d M Y') : '—' }}</span>
                            </div>
                            <div class="stat-sub-label">{{ $f->submitted_at ? $f->submitted_at->format('h:i A') : '' }}</div>
                        </td>

                        <!-- 3. HOST STAFF -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-person"></i>
                                <span>{{ $f->organizer?->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="stat-sub-label">Host Officer</div>
                        </td>

                        <!-- 4. MODE -->
                        <td>
                            @if($f->is_staff_assisted)
                                <span class="dept-pill amber">
                                    <i class="bi bi-person-check-fill"></i>
                                    <span>Staff Assisted</span>
                                </span>
                            @else
                                <span class="dept-pill green">
                                    <i class="bi bi-person-fill"></i>
                                    <span>Direct Visitor</span>
                                </span>
                            @endif
                        </td>

                        <!-- 5. COMMENTS / REMARKS -->
                        <td>
                            <div class="p-2 rounded bg-light border text-dark small" style="white-space: pre-wrap; max-height: 80px; overflow-y: auto;">
                                {{ $f->comments ?: 'No written feedback comments provided.' }}
                            </div>
                        </td>

                        <!-- 6. RATING -->
                        <td>
                            <div class="score-row text-danger">
                                <i class="bi bi-star-fill text-danger"></i>
                                <span class="fw-bold">{{ $f->overall_rating }} / 5</span>
                            </div>
                            <div class="score-sub text-danger">Critical Issue</div>
                        </td>

                        <!-- 7. ACTIONS -->
                        <td class="text-end">
                            <a href="{{ route('feedbacks.show', $f) }}" class="btn-report-view text-danger border-danger-subtle">
                                <i class="bi bi-eye"></i>
                                <span>View Details</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check text-success fs-2 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Low-Rating Incidents</h6>
                            <p class="text-muted small mb-0">No ratings of 1 or 2 stars were recorded in the selected date range.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 fs-xs text-muted">
            <div>Showing 1 to {{ $lowRatings->count() }} of {{ $lowRatings->count() }} incidents</div>
        </div>
    </div>

@elseif($reportTab === 'pending')

    <!-- Pending Feedback Audit Queue Section (Same Unified Design) -->
    <div class="report-table-card">
        <div class="report-table-header">
            <div class="report-table-title text-warning">
                <i class="bi bi-clock-history text-warning"></i>
                <span class="text-dark">Pending Feedback Audit Queue (Visits Without Feedback)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 0.75rem;">
                    {{ $pendingVisits->count() }} Pending Visits @if($pendingVisits->count() >= 1000)(Recent 1,000 max)@endif
                </span>
                @can('reportsexports.export')
                    <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'pending', 'format' => 'csv'])) }}" class="btn-report-csv text-warning border-warning-subtle" title="Export Pending CSV">
                        <i class="bi bi-download text-warning"></i>
                        <span>Export CSV</span>
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">VISITOR DETAILS</th>
                        <th style="min-width: 140px;">VISIT DATE</th>
                        <th style="min-width: 160px;">HOST STAFF</th>
                        <th style="min-width: 160px;">DEPARTMENT</th>
                        <th style="min-width: 150px;">PLANT GATE STATUS</th>
                        <th style="min-width: 140px;">EXIT ELAPSED</th>
                        <th style="min-width: 130px;" class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pendingVisits as $v)
                    @php
                        $nameParts = preg_split('/\s+/', trim($v->visitor_name ?? 'Guest'));
                        $pInitials = strtoupper(substr($nameParts[0] ?? 'G', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                        $pAvatarBg = '#d97706';
                    @endphp
                    <tr class="{{ $v->is_exceeded_exit_threshold ? 'table-danger-subtle' : '' }}" style="{{ $v->is_exceeded_exit_threshold ? 'background-color: #fff5f5;' : '' }}">
                        <!-- 1. VISITOR DETAILS -->
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="report-avatar" style="background-color: {{ $pAvatarBg }};">
                                    {{ $pInitials }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6" style="line-height: 1.25;">{{ $v->visitor_name }}</div>
                                    <div class="stat-sub-label">{{ $v->visitor_company ?: 'Individual' }} @if($v->visitor_code) &bull; <code>{{ $v->visitor_code }}</code>@endif</div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. VISIT DATE -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $v->visit_date ? $v->visit_date->format('d M Y') : '—' }}</span>
                            </div>
                            <div class="stat-sub-label">Visit Scheduled</div>
                        </td>

                        <!-- 3. HOST STAFF -->
                        <td>
                            <div class="stat-primary-row">
                                <i class="bi bi-person"></i>
                                <span>{{ $v->organizer?->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="stat-sub-label">Host Officer</div>
                        </td>

                        <!-- 4. DEPARTMENT -->
                        <td>
                            @if($v->department)
                                <span class="dept-pill blue">
                                    <i class="bi bi-buildings"></i>
                                    <span>{{ $v->department }}</span>
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <!-- 5. PLANT GATE STATUS -->
                        <td>
                            @if($v->out_time)
                                <span class="dept-pill purple">
                                    <i class="bi bi-door-closed"></i>
                                    <span>Checked Out</span>
                                </span>
                                <div class="stat-sub-label">{{ $v->formatted_out_time }}</div>
                            @else
                                <span class="dept-pill green">
                                    <i class="bi bi-building"></i>
                                    <span>Inside Plant</span>
                                </span>
                                @if($v->in_time)
                                    <div class="stat-sub-label">In: {{ $v->formatted_in_time }}</div>
                                @endif
                            @endif
                        </td>

                        <!-- 6. EXIT ELAPSED -->
                        <td>
                            @if($v->out_time)
                                <div class="fw-bold {{ $v->is_exceeded_exit_threshold ? 'text-danger' : 'text-dark' }}">
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

                        <!-- 7. ACTIONS -->
                        <td class="text-end text-nowrap">
                            @if($v->feedback)
                                <a href="{{ route('feedbacks.show', $v->feedback) }}" class="btn-report-view">
                                    <i class="bi bi-eye"></i> View Feedback
                                </a>
                            @elseif($v->is_feedback_expired)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" title="Feedback collection expired because visit date has passed">
                                    <i class="bi bi-lock-fill me-1"></i> Closed
                                </span>
                            @else
                                <a href="{{ route('visits.feedback.create', $v) }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm" title="Submit feedback on behalf of visitor">
                                    <i class="bi bi-pencil-square me-1"></i> Submit
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-all text-success fs-2 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Pending Feedback</h6>
                            <p class="text-muted small mb-0">All visitor tours have completed their feedback for this period.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 fs-xs text-muted">
            <div>Showing 1 to {{ $pendingVisits->count() }} of {{ $pendingVisits->count() }} visits</div>
        </div>
    </div>
@endif
@endsection
