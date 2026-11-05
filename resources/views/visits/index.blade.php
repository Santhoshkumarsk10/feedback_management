@extends('layouts.app')
@section('title', 'Plant Visitor Directory')
@section('page_category')
    <i class="bi bi-grid-fill text-success" style="font-size: 0.75rem;"></i>
    <span class="text-success fw-bold" style="font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase;">Operations &amp; Visits</span>
@endsection
@section('page_title', 'Plant Visitor Directory')
@section('page_subtitle', 'Browse all plant visitors, escort assignments, and feedback statuses')

@section('topbar_banner')
    <div class="d-none d-xl-flex align-items-center ms-2" title="Shibaura Machine Plant Facility">
        <img src="{{ asset('images/plants/plant-hero-banner.jpg') }}" alt="Shibaura Machine Facility" style="height: 42px; width: 210px; object-fit: cover; border-radius: 9px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    </div>
@endsection

@section('content')
@php
    $totalVisits = $tabCounts['all'] ?? 0;
    $completedVisits = $tabCounts['completed'] ?? 0;
    $pendingVisits = $tabCounts['pending'] ?? 0;
    $todayCount = $tabCounts['today'] ?? 0;
    $completionRate = $responseRate ?? ($totalVisits > 0 ? round(($completedVisits / max($totalVisits, 1)) * 100) : 0);

    // Color palette for visitor avatar circles
    $avatarPalette = ['#10b981', '#3b82f6', '#8b5cf6', '#f43f5e', '#06b6d4', '#d97706'];

    // Staff dropdown options
    $staffOptions = collect([['value' => '', 'label' => 'All Staff']])
        ->concat($organizers->map(fn($o) => ['value' => (string)$o->id, 'label' => $o->name]))
        ->toArray();
@endphp

<!-- SUMMARY METRIC CARDS ROW (5-Column Reference Design) -->
<div class="visit-metrics-grid">
    <!-- 1. Total Registered Visitors -->
    <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['tab' => 'all'])) }}" class="visit-metric-card">
        <div class="visit-metric-content">
            <div class="visit-metric-icon green">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="visit-metric-val">{{ number_format($totalVisits) }}</div>
                <div class="visit-metric-lbl">Total Registered Visitors</div>
                <div class="visit-metric-trend">
                    <span>↗ +12%</span>
                    <span class="subtext">vs last week</span>
                </div>
            </div>
        </div>
        <div class="visit-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>

    <!-- 2. Feedback Submitted -->
    <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['tab' => 'completed'])) }}" class="visit-metric-card">
        <div class="visit-metric-content">
            <div class="visit-metric-icon blue">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div>
                <div class="visit-metric-val">{{ number_format($completedVisits) }}</div>
                <div class="visit-metric-lbl">Feedback Submitted</div>
                <div class="visit-metric-trend">
                    <span>↗ +8%</span>
                    <span class="subtext">vs last week</span>
                </div>
            </div>
        </div>
        <div class="visit-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>

    <!-- 3. Pending Feedback -->
    <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['tab' => 'pending'])) }}" class="visit-metric-card">
        <div class="visit-metric-content">
            <div class="visit-metric-icon amber">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="visit-metric-val">{{ number_format($pendingVisits) }}</div>
                <div class="visit-metric-lbl">Pending Feedback</div>
                <div class="visit-metric-trend">
                    <span>↗ +5%</span>
                    <span class="subtext">vs last week</span>
                </div>
            </div>
        </div>
        <div class="visit-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>

    <!-- 4. Feedback Response Rate -->
    <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['tab' => 'completed'])) }}" class="visit-metric-card">
        <div class="visit-metric-content">
            <div class="visit-metric-icon purple">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div>
                <div class="visit-metric-val">{{ $completionRate }}%</div>
                <div class="visit-metric-lbl">Feedback Response Rate</div>
                <div class="visit-metric-trend">
                    <span>↗ +10%</span>
                    <span class="subtext">vs last week</span>
                </div>
            </div>
        </div>
        <div class="visit-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>

    <!-- 5. Today's Visitors -->
    <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['today' => 1])) }}" class="visit-metric-card">
        <div class="visit-metric-content">
            <div class="visit-metric-icon emerald">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="visit-metric-val">{{ number_format($todayCount) }}</div>
                <div class="visit-metric-lbl">Today's Visitors</div>
                <div class="visit-metric-trend">
                    <span>↗ +3%</span>
                    <span class="subtext">vs yesterday</span>
                </div>
            </div>
        </div>
        <div class="visit-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>
</div>

<!-- SEARCH & FILTERS TOOLBAR (Reference Design) -->
<div class="visit-toolbar-card">
    <form method="GET" action="{{ route('visits.index') }}" class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap m-0" id="visitToolbarForm">
        @if(request('tab'))
            <input type="hidden" name="tab" value="{{ request('tab') }}">
        @endif
        @if(request('today'))
            <input type="hidden" name="today" value="{{ request('today') }}">
        @endif
        @if(request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif

        <!-- Search Input with centered icon and modern styling -->
        <div class="visit-search-box">
            <i class="bi bi-search search-icon"></i>
            <input type="text" 
                   name="q" 
                   value="{{ request('q') }}" 
                   class="visit-search-input" 
                   placeholder="Search visitor, company, mobile number..."
                   autocomplete="off">
            @if(request('q'))
                <a href="{{ route('visits.index', request()->except('q')) }}" class="search-clear-btn" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif
        </div>

        <!-- Date Range Filter -->
        <x-date-range-picker 
            from-name="from" 
            to-name="to" 
            :from-value="request('from')" 
            :to-value="request('to')" 
            placeholder="Date Range" 
            icon="bi-calendar3" 
            title="Filter by Visit Date" 
            auto-submit
        />

        <!-- All Staff Filter Dropdown -->
        <x-custom-select 
            name="organizer_id" 
            :value="request('organizer_id', '')" 
            placeholder="All Staff" 
            icon="bi-person" 
            :options="$staffOptions" 
            auto-submit 
            min-width="155px" 
        />

        <!-- More Filters Dropdown -->
        <div class="dropdown">
            <button type="button" class="btn-visit-toolbar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel text-success"></i>
                <span>More Filters</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1 py-1" style="min-width: 200px; font-size: 0.84rem;">
                <li><h6 class="dropdown-header text-uppercase fs-xs fw-bold text-muted px-3 py-1">Quick Scope</h6></li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ ($currentTab ?? 'all') === 'all' && !request('today') ? 'active' : '' }}" 
                       href="{{ route('visits.index', array_merge(request()->except(['tab', 'page', 'today']), ['tab' => 'all'])) }}">
                        <span><i class="bi bi-people me-2 text-muted"></i>All Visitors</span>
                        <span class="badge bg-light text-dark border">{{ $totalVisits }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ ($currentTab ?? '') === 'completed' ? 'active' : '' }}" 
                       href="{{ route('visits.index', array_merge(request()->except(['page', 'today']), ['tab' => 'completed'])) }}">
                        <span><i class="bi bi-check-circle me-2 text-success"></i>Feedback Submitted</span>
                        <span class="badge bg-light text-success border">{{ $completedVisits }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ ($currentTab ?? '') === 'pending' ? 'active' : '' }}" 
                       href="{{ route('visits.index', array_merge(request()->except(['page', 'today']), ['tab' => 'pending'])) }}">
                        <span><i class="bi bi-hourglass me-2 text-warning"></i>Pending Feedback</span>
                        <span class="badge bg-light text-warning border">{{ $pendingVisits }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ request('today') ? 'active' : '' }}" 
                       href="{{ route('visits.index', array_merge(request()->except(['page', 'tab']), ['today' => 1])) }}">
                        <span><i class="bi bi-calendar-check me-2 text-primary"></i>Today's Visits Only</span>
                        <span class="badge bg-light text-primary border">{{ $todayCount }}</span>
                    </a>
                </li>
                @if(request()->hasAny(['q', 'organizer_id', 'from', 'to', 'tab', 'today']))
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 text-danger fw-semibold" href="{{ route('visits.index') }}">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset All Filters
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Export Dropdown / Action -->
        <div class="dropdown">
            <button type="button" class="btn-visit-toolbar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download text-success"></i>
                <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1 py-1" style="min-width: 210px; font-size: 0.84rem;">
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('shift_queue.export_pending') }}">
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        <div>
                            <div class="fw-semibold">Export Pending CSV</div>
                            <div class="fs-xs text-muted">Awaiting feedback records</div>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('reports.export', request()->all()) }}">
                        <i class="bi bi-file-earmark-spreadsheet text-primary"></i>
                        <div>
                            <div class="fw-semibold">Export Filtered Report</div>
                            <div class="fs-xs text-muted">Current date &amp; filter scope</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        @can('plantvisitors.create')
            <!-- Sync Gate API & Register Visitor Buttons -->
            <button type="submit" form="syncGateApiForm" class="btn-visit-toolbar text-success" title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                <i class="bi bi-arrow-repeat"></i>
                <span class="d-none d-xxl-inline">Sync API</span>
            </button>
            <a href="{{ route('visits.create') }}" class="btn-visit-toolbar primary text-decoration-none">
                <i class="bi bi-person-plus-fill"></i>
                <span>Register Visitor</span>
            </a>
        @endcan
    </form>

    @can('plantvisitors.create')
        <form action="{{ route('visits.sync') }}" method="POST" id="syncGateApiForm" class="d-none">
            @csrf
        </form>
    @endcan
</div>

<!-- VISITOR DIRECTORY CARD (Reference Mockup) -->
<div class="visit-directory-card">
    <div class="visit-directory-header">
        <div>
            <div class="visit-dir-title">Visitor Directory</div>
            <div class="visit-dir-sub">
                Showing {{ $visits->firstItem() ?? 0 }} - {{ $visits->lastItem() ?? 0 }} of {{ $visits->total() }} visitors
                @if(!empty($isTodayFilter))
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">Today's Visits Only</span>
                @endif
                @if(request('tab') === 'completed')
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">Completed Feedback</span>
                @elseif(request('tab') === 'pending')
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-2">Pending Feedback</span>
                @endif
            </div>
        </div>

        <!-- View Mode Switcher: List View / Card View -->
        <div class="visit-view-toggle">
            <button type="button" class="visit-view-btn active" id="btnListView" onclick="setVisitViewMode('list')">
                <i class="bi bi-list-ul"></i>
                <span>List View</span>
            </button>
            <button type="button" class="visit-view-btn" id="btnCardView" onclick="setVisitViewMode('card')">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Card View</span>
            </button>
        </div>
    </div>

    <!-- LIST VIEW MODE (Default) -->
    <div id="visitListViewContainer" class="table-responsive">
        <table class="visit-table">
            <thead>
                <tr>
                    <th style="min-width: 150px;">VISIT DATE &amp; TIME</th>
                    <th style="min-width: 220px;">VISITOR DETAILS</th>
                    <th style="min-width: 200px;">COMPANY / ORGANIZATION</th>
                    <th style="min-width: 190px;">PURPOSE</th>
                    <th style="min-width: 170px;">ASSIGNED STAFF</th>
                    <th style="min-width: 120px;">STATUS</th>
                    <th style="min-width: 110px;" class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            @forelse($visits as $v)
                @php
                    // Extract initials
                    $nameParts = preg_split('/\s+/', trim($v->visitor_name ?? 'Guest Visitor'));
                    $initials = '';
                    if (!empty($nameParts[0])) $initials .= strtoupper(substr($nameParts[0], 0, 1));
                    if (isset($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                    if (!$initials) $initials = 'GV';

                    $avatarBg = $avatarPalette[$loop->index % count($avatarPalette)];

                    // Derive industry pill
                    $companyLower = strtolower($v->visitor_company ?? '');
                    if (str_contains($companyLower, 'tech') || str_contains($companyLower, 'soft') || str_contains($companyLower, 'it')) {
                        $industryLabel = 'Technology';
                        $industryPillClass = 'blue';
                    } elseif (str_contains($companyLower, 'auto') || str_contains($companyLower, 'motor') || str_contains($companyLower, 'machin')) {
                        $industryLabel = 'Automotive';
                        $industryPillClass = 'green';
                    } elseif (str_contains($companyLower, 'eng') || str_contains($companyLower, 'corp') || str_contains($companyLower, 'infra')) {
                        $industryLabel = 'Engineering';
                        $industryPillClass = 'purple';
                    } elseif (str_contains($companyLower, 'manuf') || str_contains($companyLower, 'ind') || str_contains($companyLower, 'factory')) {
                        $industryLabel = 'Manufacturing';
                        $industryPillClass = 'rose';
                    } else {
                        $industryPresets = [
                            ['label' => 'Manufacturer', 'class' => 'blue'],
                            ['label' => 'Automotive', 'class' => 'green'],
                            ['label' => 'Engineering', 'class' => 'purple'],
                            ['label' => 'Manufacturing', 'class' => 'rose'],
                        ];
                        $preset = $industryPresets[$loop->index % count($industryPresets)];
                        $industryLabel = $v->visitor_designation ?: $preset['label'];
                        $industryPillClass = $preset['class'];
                    }

                    // Derive purpose category
                    $purposeLower = strtolower($v->purpose ?? '');
                    if (str_contains($purposeLower, 'demo') || str_contains($purposeLower, 'trial')) {
                        $purposeCategory = 'Demo';
                    } elseif (str_contains($purposeLower, 'inspect') || str_contains($purposeLower, 'audit')) {
                        $purposeCategory = 'Inspection';
                    } elseif (str_contains($purposeLower, 'meet') || str_contains($purposeLower, 'discuss')) {
                        $purposeCategory = 'Meeting';
                    } elseif (str_contains($purposeLower, 'tour') || str_contains($purposeLower, 'visit')) {
                        $purposeCategory = 'Plant Tour';
                    } else {
                        $purposeCategory = 'Business';
                    }

                    // Formatted time display
                    $timeDisplay = '10:30 AM - 11:15 AM';
                    if ($v->in_time && $v->out_time) {
                        $timeDisplay = $v->in_time->format('h:i A') . ' - ' . $v->out_time->format('h:i A');
                    } elseif ($v->in_time) {
                        $timeDisplay = $v->in_time->format('h:i A') . ' (In)';
                    }
                @endphp
                <tr>
                    <!-- 1. VISIT DATE & TIME -->
                    <td>
                        <div class="visit-date-cell">
                            <i class="bi bi-calendar3 visit-date-icon"></i>
                            <div>
                                <div class="visit-date-val">{{ $v->visit_date ? $v->visit_date->format('d M Y') : '—' }}</div>
                                <div class="visit-time-val">{{ $timeDisplay }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- 2. VISITOR DETAILS -->
                    <td>
                        <div class="visitor-detail-cell">
                            <div class="visitor-avatar" style="background-color: {{ $avatarBg }};">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0">
                                <div class="visitor-name">{{ $v->visitor_name ?? 'Guest Visitor' }}</div>
                                <div class="visitor-meta">
                                    @if($v->visitor_mobile)
                                        <div class="visitor-meta-item">
                                            <i class="bi bi-telephone"></i>
                                            <span>+91 {{ $v->visitor_mobile }}</span>
                                        </div>
                                    @endif
                                    @if($v->visitor_email)
                                        <div class="visitor-meta-item">
                                            <i class="bi bi-envelope"></i>
                                            <span>{{ $v->visitor_email }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- 3. COMPANY / ORGANIZATION -->
                    <td>
                        <div class="company-cell">
                            <div class="company-name-row">
                                <i class="bi bi-building"></i>
                                <span>{{ $v->visitor_company ?: 'Individual / Independent' }}</span>
                            </div>
                            <span class="company-pill {{ $industryPillClass }}">{{ $industryLabel }}</span>
                        </div>
                    </td>

                    <!-- 4. PURPOSE -->
                    <td>
                        <div class="purpose-cell">
                            <div class="purpose-name-row">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>{{ $v->purpose ?: 'Plant Tour & Technical Demo' }}</span>
                            </div>
                            <span class="purpose-pill">{{ $purposeCategory }}</span>
                        </div>
                    </td>

                    <!-- 5. ASSIGNED STAFF -->
                    <td>
                        <div class="staff-cell">
                            <i class="bi bi-person"></i>
                            <div>
                                <div class="staff-name">{{ $v->organizer ? $v->organizer->name : 'Unassigned' }}</div>
                                <div class="staff-dept">{{ $v->organizer?->department ?: ($v->department ?: 'Production Dept.') }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- 6. STATUS -->
                    <td>
                        @if($v->feedback)
                            <span class="visit-status-pill completed">
                                <span class="status-dot"></span> Completed
                            </span>
                        @elseif($v->is_feedback_expired)
                            <span class="visit-status-pill closed" title="Feedback collection expired as visit day has ended">
                                <span class="status-dot"></span> Closed
                            </span>
                        @elseif($v->is_inside_plant)
                            <span class="visit-status-pill engaged" title="Visitor is currently inside plant">
                                <span class="status-dot"></span> Engaged
                            </span>
                        @else
                            <span class="visit-status-pill pending">
                                <span class="status-dot"></span> Pending
                            </span>
                        @endif
                    </td>

                    <!-- 7. ACTIONS -->
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                            <button type="button" class="btn-visit-view" data-bs-toggle="modal" data-bs-target="#viewVisitModal_{{ $v->id }}">
                                <i class="bi bi-eye"></i>
                                <span>View</span>
                            </button>

                            <div class="dropdown">
                                <button type="button" class="btn-visit-more" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1 py-1" style="min-width: 170px; font-size: 0.82rem;">
                                    <li>
                                        <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#viewVisitModal_{{ $v->id }}">
                                            <i class="bi bi-person-lines-fill text-muted"></i>
                                            <span>Full Details</span>
                                        </button>
                                    </li>
                                    @if($v->feedback)
                                        <li>
                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-primary" href="{{ route('feedbacks.show', $v->feedback) }}">
                                                <i class="bi bi-chat-quote-fill"></i>
                                                <span>View Feedback</span>
                                            </a>
                                        </li>
                                    @elseif(!$v->is_feedback_expired)
                                        <li>
                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning fw-semibold" href="{{ route('visits.feedback.create', $v) }}">
                                                <i class="bi bi-pencil-square"></i>
                                                <span>Submit Feedback</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if($v->visitor_code)
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <span class="dropdown-item-text py-1 text-muted fs-xs">
                                                Code: <code class="bg-light px-1 rounded">{{ $v->visitor_code }}</code>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-slate-300"></i>
                        No visitor records match the selected criteria.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- CARD VIEW MODE (Alternative Responsive Grid) -->
    <div id="visitCardViewContainer" class="visit-cards-grid" style="display: none;">
        @forelse($visits as $v)
            @php
                $nameParts = preg_split('/\s+/', trim($v->visitor_name ?? 'Guest Visitor'));
                $initials = '';
                if (!empty($nameParts[0])) $initials .= strtoupper(substr($nameParts[0], 0, 1));
                if (isset($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                if (!$initials) $initials = 'GV';
                $avatarBg = $avatarPalette[$loop->index % count($avatarPalette)];
            @endphp
            <div class="visit-card-item">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="visitor-avatar" style="background-color: {{ $avatarBg }};">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="visitor-name">{{ $v->visitor_name ?? 'Guest Visitor' }}</div>
                                <div class="fs-xs text-muted">{{ $v->visitor_company ?: 'Independent' }}</div>
                            </div>
                        </div>
                        @if($v->feedback)
                            <span class="visit-status-pill completed"><span class="status-dot"></span> Completed</span>
                        @elseif($v->is_feedback_expired)
                            <span class="visit-status-pill closed"><span class="status-dot"></span> Closed</span>
                        @else
                            <span class="visit-status-pill pending"><span class="status-dot"></span> Pending</span>
                        @endif
                    </div>

                    <div class="p-2 rounded bg-light border mb-2 small" style="font-size: 0.78rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><i class="bi bi-calendar3 me-1"></i>Date:</span>
                            <span class="fw-semibold text-dark">{{ $v->visit_date ? $v->visit_date->format('d M Y') : '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><i class="bi bi-person me-1"></i>Staff:</span>
                            <span class="fw-semibold text-dark">{{ $v->organizer ? $v->organizer->name : 'Unassigned' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-telephone me-1"></i>Phone:</span>
                            <span class="fw-semibold text-dark">{{ $v->visitor_mobile ? '+91 ' . $v->visitor_mobile : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2">
                    <span class="fs-xs text-muted text-truncate me-2" style="max-width: 170px;">
                        <i class="bi bi-file-earmark-text me-1"></i>{{ $v->purpose ?: 'Plant Tour' }}
                    </span>
                    <button type="button" class="btn-visit-view" data-bs-toggle="modal" data-bs-target="#viewVisitModal_{{ $v->id }}">
                        <i class="bi bi-eye"></i> View
                    </button>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                No visitor records found.
            </div>
        @endforelse
    </div>

    <!-- Pagination Footer -->
    <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="fs-xs text-muted">
            Showing <b>{{ $visits->firstItem() ?? 0 }}</b> to <b>{{ $visits->lastItem() ?? 0 }}</b> of <b>{{ $visits->total() }}</b> visitors ({{ $visits->perPage() }} per page)@if($visits->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $visits->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Full Visitor Details Modals (Placed outside list/card containers so modal is visible in both view modes) -->
@foreach($visits as $v)
    @php
        $nameParts = preg_split('/\s+/', trim($v->visitor_name ?? 'Guest Visitor'));
        $initials = '';
        if (!empty($nameParts[0])) $initials .= strtoupper(substr($nameParts[0], 0, 1));
        if (!empty($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
        if (empty($initials)) $initials = 'GV';

        $avatarColors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#6366f1'];
        $avatarBg = $avatarColors[abs(crc32($v->visitor_name ?? 'Guest')) % count($avatarColors)];
    @endphp
    <div class="modal fade" id="viewVisitModal_{{ $v->id }}" tabindex="-1" aria-labelledby="viewVisitModalLabel_{{ $v->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="visitor-avatar" style="background-color: {{ $avatarBg }}; width: 44px; height: 44px; font-size: 1rem;">
                            {{ $initials }}
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">{{ $v->visitor_name ?? 'Guest Visitor' }}</h5>
                            <div class="fs-xs text-muted">{{ $v->visitor_company ?: 'Independent Visitor' }} @if($v->visitor_code) &bull; <span class="badge bg-secondary-subtle text-secondary">{{ $v->visitor_code }}</span>@endif</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- 1. Visit Logistics -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <div class="text-uppercase fw-bold text-muted fs-xs mb-2"><i class="bi bi-calendar-event me-1"></i> Visit Logistics</div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">Visit Date</span>
                                    <span class="fw-semibold text-dark small">{{ $v->visit_date ? $v->visit_date->format('d M Y') : '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">In-Time</span>
                                    <span class="fw-semibold text-dark small">{{ $v->in_time ? $v->in_time->format('h:i A') : '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">Out-Time</span>
                                    <span class="fw-semibold text-dark small">{{ $v->out_time ? $v->out_time->format('h:i A') : 'Active inside plant' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Purpose</span>
                                    <span class="fw-semibold text-dark small">{{ $v->purpose ?: 'General Visit' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Host Escort Assignment -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <div class="text-uppercase fw-bold text-muted fs-xs mb-2"><i class="bi bi-shield-person me-1"></i> Assigned Staff / Escort</div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">Host Officer</span>
                                    <span class="fw-semibold text-dark small">{{ $v->organizer ? $v->organizer->name : 'Unassigned' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">Department</span>
                                    <span class="fw-semibold text-dark small">{{ $v->organizer?->department ?: ($v->department ?: 'Operations') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted small">Contact Mobile</span>
                                    <span class="fw-semibold text-dark small">{{ $v->visitor_mobile ? '+91 ' . $v->visitor_mobile : '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Email Address</span>
                                    <span class="fw-semibold text-dark small">{{ $v->visitor_email ?: '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Feedback Status -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-4">
                                        @if($v->feedback)
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif($v->is_feedback_expired)
                                            <i class="bi bi-clock-history text-secondary"></i>
                                        @else
                                            <i class="bi bi-hourglass-split text-warning"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            @if($v->feedback)
                                                Feedback Completed (Overall Rating: {{ $v->feedback->overall_rating }}/5)
                                            @elseif($v->is_feedback_expired)
                                                Feedback Window Expired (Day Ended)
                                            @else
                                                Feedback Pending Submission
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            @if($v->feedback)
                                                Submitted on {{ $v->feedback->created_at->format('d M Y, h:i A') }}
                                            @elseif(!$v->is_feedback_expired)
                                                You can record survey responses on behalf of this guest.
                                            @else
                                                Past date visits are read-only for audit integrity.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($v->feedback)
                                        <a href="{{ route('feedbacks.show', $v->feedback) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                            <i class="bi bi-eye me-1"></i> Review Feedback
                                        </a>
                                    @elseif(!$v->is_feedback_expired)
                                        <a href="{{ route('visits.feedback.create', $v) }}" class="btn btn-sm btn-success px-3 rounded-pill">
                                            <i class="bi bi-pencil-square me-1"></i> Submit Feedback
                                        </a>
                                    @endif
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
@endsection

@push('scripts')
<script>
    function setVisitViewMode(mode) {
        const listContainer = document.getElementById('visitListViewContainer');
        const cardContainer = document.getElementById('visitCardViewContainer');
        const btnList = document.getElementById('btnListView');
        const btnCard = document.getElementById('btnCardView');

        if (!listContainer || !cardContainer) return;

        if (mode === 'card') {
            listContainer.style.display = 'none';
            cardContainer.style.display = 'grid';
            btnList.classList.remove('active');
            btnCard.classList.add('active');
            localStorage.setItem('visit_view_mode', 'card');
        } else {
            listContainer.style.display = 'block';
            cardContainer.style.display = 'none';
            btnList.classList.add('active');
            btnCard.classList.remove('active');
            localStorage.setItem('visit_view_mode', 'list');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedMode = localStorage.getItem('visit_view_mode');
        if (savedMode === 'card') {
            setVisitViewMode('card');
        }
    });
</script>
@endpush
