@extends('layouts.app')
@section('title', "Live Shift Queue")
@section('page_title', "Live Shift Queue")
@section('page_subtitle', "Real-time visitor queue & feedback status")

@section('topbar_actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dashboard') }}" class="btn-modern-secondary btn-sm" title="Go to Executive Dashboard">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="d-none d-sm-inline">Dashboard</span>
        </a>
        <a href="{{ route('visits.create') }}" class="btn-modern-primary btn-sm">
            <i class="bi bi-person-plus-fill"></i>
            <span>Register Visitor</span>
        </a>
    </div>
@endsection

@section('content')
    <!-- Shift Feedback Deadline Alert Notification for Staff (30m Before Shift Close) -->
    <div id="shiftCloseAlertWrapper">
        @if(!empty($shiftAlerts))
            @foreach($shiftAlerts as $alert)
                <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 animate-pulse-subtle" 
                     style="background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%); border-left: 5px solid #dc2626 !important;"
                     role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <div class="alert-icon-pulse bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-alarm-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="fw-bold text-danger mb-0 fs-6">
                                    ⚠️ Staff Alert: {{ $alert['shift_name'] }} Feedback Due
                                </h6>
                                <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                    <i class="bi bi-clock-history me-1"></i> {{ $alert['remaining_text'] }}
                                </span>
                                <span class="badge bg-light text-danger border border-danger-subtle fw-semibold">
                                    Shift Closes: {{ $alert['end_time_12h'] }}
                                </span>
                            </div>
                            <div class="text-dark small mt-1">
                                <strong>{{ $alert['pending_count'] }} visitor(s)</strong> in {{ $alert['shift_name'] }} have not filled feedback within the shift time limit. Please collect feedback before shift handover!
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('shift_queue', ['today_tab' => 'pending', 'shift_id' => $alert['shift_id']]) }}" 
                           class="btn btn-sm btn-danger fw-semibold px-3 shadow-xs">
                            <i class="bi bi-person-exclamation me-1"></i> View {{ $alert['shift_name'] }} Pending ({{ $alert['pending_count'] }})
                        </a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Live Duty Shift Tracker & Active Shift Monitor -->
    <div class="card-modern mb-4"
        style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-left: 4px solid var(--shibaura-blue);">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span
                            class="d-inline-flex align-items-center gap-1 badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 0.65rem; height: 0.65rem;"
                                role="status"></span>
                            LIVE DUTY SHIFT TRACKER
                        </span>
                        <span class="small text-muted" id="liveClockDisplay">
                            <i class="bi bi-clock me-1"></i>
                            {{ now(config('app.plant_timezone', 'Asia/Kolkata'))->format('h:i:s A') }} IST
                        </span>
                    </div>

                    <div class="d-flex flex-wrap align-items-baseline gap-2 mb-2">
                        <h4 class="fw-bold text-dark mb-0">
                            {{ $currentShift ? $currentShift->name : 'Off Shift Operations' }}
                        </h4>
                        @if ($currentShift)
                            <span class="badge bg-primary text-white fs-6 px-3 py-1">
                                {{ $currentShift->formatted_24h_range }}
                            </span>
                            <span class="text-secondary small fw-semibold">
                                ({{ $currentShift->formatted_12h_range }})
                            </span>
                        @endif
                    </div>

                    @if ($currentShift)
                        @php
                            $prog = $currentShift->shift_progress;
                        @endphp
                        <div class="mb-2" style="max-width: 520px;">
                            <div class="d-flex justify-content-between align-items-center mb-1 small">
                                <span class="text-muted fw-semibold">Shift Progress: {{ $prog['percent'] }}% Elapsed</span>
                                <span class="fw-bold text-primary"><i class="bi bi-hourglass-split"></i>
                                    {{ $prog['remaining_formatted'] }}</span>
                            </div>
                            <div class="progress-modern" style="height: 8px;">
                                <div class="progress-bar-emerald"
                                    style="width: {{ $prog['percent'] }}%; height: 100%; background: linear-gradient(90deg, #38bdf8, #06539d);">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="small text-muted d-flex flex-wrap align-items-center gap-3">
                        <span><i class="bi bi-person-badge text-primary"></i> On Duty Staff:
                            <strong>{{ $currentUser->name }}</strong> ({{ ucfirst($currentUser->role) }})</span>
                        @if ($currentUser->department)
                            <span><i class="bi bi-building text-secondary"></i> {{ $currentUser->department }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-5 text-lg-end">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <a href="{{ route('visits.create') }}"
                            class="btn btn-primary btn-sm px-3 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-person-plus-fill me-1"></i> Register New Visitor
                        </a>
                        <form action="{{ route('visits.sync') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm px-3 py-2 fw-semibold shadow-sm"
                                title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                                <i class="bi bi-arrow-repeat me-1"></i> Sync Gate API
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Today's Shift Feedback Metrics (5 Cards from Screenshot) -->
    <div class="row g-3 mb-4">
        <!-- Visitors Today -->
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card-widget cyan h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">VISITORS TODAY</span>
                    <div class="stat-icon-bubble cyan">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number" id="cardTodayTotal">{{ number_format($stats['today_total']) }}</div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-calendar2-day"></i> Plant Arrivals
                </span>
            </div>
        </div>

        <!-- Feedback Given -->
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card-widget emerald h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">FEEDBACK GIVEN</span>
                    <div class="stat-icon-bubble emerald">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number" id="cardTodayGiven">{{ number_format($stats['today_given']) }}</div>
                <span class="stat-pill-trend up" id="cardTodayGivenPct">
                    <i class="bi bi-pie-chart-fill"></i> {{ $stats['today_given_pct'] }}% Completed
                </span>
            </div>
        </div>

        <!-- Feedback Pending -->
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card-widget amber h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">FEEDBACK PENDING</span>
                    <div class="stat-icon-bubble amber">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div class="stat-metric-number" id="cardTodayPending">{{ number_format($stats['today_pending']) }}</div>
                <span class="stat-pill-trend {{ $stats['today_pending'] > 0 ? 'down' : 'up' }}" id="cardTodayPendingPct">
                    <i class="bi bi-clock-history"></i> {{ $stats['today_pending_pct'] }}% Pending
                </span>
            </div>
        </div>

        <!-- Today's Avg Rating -->
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card-widget indigo h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">TODAY'S AVG RATING</span>
                    <div class="stat-icon-bubble indigo">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number" id="cardTodayAvg">
                    {{ $stats['today_avg'] ? number_format($stats['today_avg'], 1) : '—' }}
                    @if($stats['today_avg'])<span class="fs-5 text-muted fw-normal">/5</span>@endif
                </div>
                <span class="stat-pill-trend {{ ($stats['today_avg'] ?? 0) >= 4 ? 'up' : 'neutral' }}">
                    <i class="bi bi-award"></i> Shift Sentiment
                </span>
            </div>
        </div>

        <!-- API Sync Time -->
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card-widget slate h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">API SYNC TIME</span>
                    <div class="stat-icon-bubble slate">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
                <div class="stat-metric-number fs-5 text-truncate" id="cardLastSync" style="font-size: 1.25rem !important;">
                    {{ $stats['last_sync_at'] ? \Carbon\Carbon::parse($stats['last_sync_at'])->diffForHumans(null, true) . ' ago' : 'Live' }}
                </div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-hdd-network"></i> Gate Pass Feed
                </span>
            </div>
        </div>
    </div>

    <!-- Today's Visitors: Queue & Operations Board -->
    <div class="card-modern mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="card-title">
                    <i class="bi bi-calendar2-day-fill text-primary"></i>
                    <span>Today's Plant Visitors & Feedback Queue</span>
                </div>
                <div class="text-muted small mt-1">Track visitor feedback status for today's shift and submit evaluations on behalf of visitors</div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Scope toggle: All Visitors vs My Visitors -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('shift_queue', array_merge(request()->query(), ['scope' => 'all'])) }}"
                        class="btn {{ ($scope ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All Visitors ({{ $todayCounts['scope_all'] }})
                    </a>
                    <a href="{{ route('shift_queue', array_merge(request()->query(), ['scope' => 'my'])) }}"
                        class="btn {{ ($scope ?? '') === 'my' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        My Visitors ({{ $todayCounts['scope_my'] }})
                    </a>
                </div>

                <!-- Shift Filter Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-clock me-1"></i>
                        @if($shiftFilter)
                            {{ $shifts->firstWhere('id', $shiftFilter)?->name ?? 'Shift' }}
                        @else
                            All Shifts
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item {{ !$shiftFilter ? 'active' : '' }}"
                               href="{{ route('shift_queue', array_merge(request()->except('shift_id'))) }}">
                               All Shifts
                            </a>
                        </li>
                        @foreach($shifts as $s)
                            <li>
                                <a class="dropdown-item {{ $shiftFilter == $s->id ? 'active' : '' }}"
                                   href="{{ route('shift_queue', array_merge(request()->query(), ['shift_id' => $s->id])) }}">
                                   {{ $s->name }} ({{ $s->formatted_24h_range }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <a href="{{ route('shift_queue.export_pending', request()->query()) }}" class="btn btn-sm btn-outline-secondary" title="Export today pending list to CSV">
                    <i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i> Export Pending
                </a>

                <form action="{{ route('visits.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success"
                        title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync 3rd Party
                    </button>
                </form>

                <a href="{{ route('visits.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Visitor
                </a>

                <a href="{{ route('visits.index', ['today' => 1]) }}" class="btn btn-sm btn-link text-decoration-none">
                    <span>Full Log</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Filter Tabs Header: Pending vs Completed vs All -->
        <div class="filter-tabs-header bg-light border-bottom px-3 pt-2">
            <div class="filter-tabs-nav">
                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'pending'])) }}"
                    class="filter-tab-btn {{ ($todayTab ?? 'pending') === 'pending' ? 'active' : '' }}"
                    style="{{ ($todayTab ?? 'pending') === 'pending' ? 'border-bottom: 3px solid #f59e0b; color: #b45309; font-weight: 700;' : '' }}">
                    <i class="bi bi-clock-history text-warning"></i>
                    <span>Awaiting Feedback (Pending)</span>
                    <span
                        class="badge {{ $todayCounts['pending'] > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white' }} rounded-pill ms-1" id="badgePendingCount">
                        {{ $todayCounts['pending'] }}
                    </span>
                </a>

                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'completed'])) }}"
                    class="filter-tab-btn {{ ($todayTab ?? '') === 'completed' ? 'active' : '' }}"
                    style="{{ ($todayTab ?? '') === 'completed' ? 'border-bottom: 3px solid #10b981; color: #047857; font-weight: 700;' : '' }}">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Feedback Completed</span>
                    <span class="badge bg-success rounded-pill ms-1" id="badgeCompletedCount">
                        {{ $todayCounts['completed'] }}
                    </span>
                </a>

                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'all'])) }}"
                    class="filter-tab-btn {{ ($todayTab ?? '') === 'all' ? 'active' : '' }}">
                    <i class="bi bi-people-fill text-primary"></i>
                    <span>All Today's Visitors</span>
                    <span class="badge bg-secondary rounded-pill ms-1" id="badgeAllCount">
                        {{ $todayCounts['all'] }}
                    </span>
                </a>
            </div>
        </div>

        <!-- Quick Search Bar -->
        <div class="p-3 bg-white border-bottom">
            <form method="GET" action="{{ route('shift_queue') }}" class="row g-2 align-items-center">
                @if(request('today_tab'))
                    <input type="hidden" name="today_tab" value="{{ request('today_tab') }}">
                @endif
                @if(request('scope'))
                    <input type="hidden" name="scope" value="{{ request('scope') }}">
                @endif
                @if(request('shift_id'))
                    <input type="hidden" name="shift_id" value="{{ request('shift_id') }}">
                @endif

                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search visitor name, company, mobile, pass code..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary px-3">Search</button>
                    @if(request('search'))
                        <a href="{{ route('shift_queue', request()->except('search')) }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                    @endif
                </div>

                @if(request('search') || request('shift_id') || (request('scope') && request('scope') !== 'all'))
                    <div class="col-12 text-muted small mt-1">
                        <span>Active filters:</span>
                        @if(request('search'))
                            <span class="badge bg-light text-dark border me-1">Search: {{ request('search') }}</span>
                        @endif
                        @if(request('shift_id'))
                            <span class="badge bg-light text-dark border me-1">Shift: {{ $shifts->firstWhere('id', request('shift_id'))?->name }}</span>
                        @endif
                        @if(request('scope') === 'my')
                            <span class="badge bg-light text-dark border me-1">Scope: My Visitors</span>
                        @endif
                        <a href="{{ route('shift_queue') }}" class="text-danger ms-1 text-decoration-none"><i class="bi bi-x-circle"></i> Reset All</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Visitor Information</th>
                        <th>In / Out Time & Gate Status</th>
                        <th>Duty Shift & Dept</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayVisits as $v)
                        <tr style="{{ $v->is_exceeded_exit_threshold ? 'background-color: #fff1f2; border-left: 4px solid #ef4444;' : ($v->is_checked_out && !$v->feedback ? 'background-color: #f8fafc; border-left: 4px solid #f59e0b;' : '') }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark fs-6">{{ $v->visitor_name }}</span>
                                    @if ($v->visitor_code)
                                        <span
                                            class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0"
                                            style="font-size: 0.72rem;">
                                            <i class="bi bi-qr-code me-1"></i> {{ $v->visitor_code }}
                                        </span>
                                    @endif
                                </div>
                                @if ($v->visitor_designation)
                                    <div class="small text-secondary fw-semibold">{{ $v->visitor_designation }}</div>
                                @endif
                                <div class="small text-muted d-flex align-items-center gap-2 mt-1">
                                    @if ($v->visitor_company)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-building me-1"></i> {{ $v->visitor_company }}
                                        </span>
                                    @endif
                                    @if ($v->visitor_mobile)
                                        <span><i class="bi bi-telephone text-muted"></i> {{ $v->visitor_mobile }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <!-- Status Badge: Priority for Checked Out -->
                                    <div>
                                        @if ($v->out_time)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0" style="font-size: 0.72rem;">
                                                <i class="bi bi-box-arrow-right me-1"></i> Checked Out
                                            </span>
                                        @elseif ($v->in_time)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size: 0.72rem;">
                                                <i class="bi bi-geo-alt-fill me-1"></i> Inside Plant
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-0" style="font-size: 0.72rem;">
                                                Registered
                                            </span>
                                        @endif

                                        @if ($v->is_exceeded_exit_threshold)
                                            <span class="badge bg-danger text-white px-2 py-0 ms-1 fw-bold" style="font-size: 0.70rem;" title="Checked out >2 hours ago without feedback!">
                                                <i class="bi bi-exclamation-triangle-fill"></i> >2h Post-Exit Alert
                                            </span>
                                        @endif

                                        @if ($v->is_shift_closing_soon_alert)
                                            <span class="badge bg-danger text-white px-2 py-0 ms-1 fw-bold shadow-xs animate-pulse-subtle" style="font-size: 0.70rem;" title="Shift closing within 30m without feedback! {{ $v->shift_close_alert_text }}">
                                                <i class="bi bi-alarm-fill"></i> {{ $v->shift_close_alert_text }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        @if ($v->formatted_in_time)
                                            <span><strong>In:</strong> {{ $v->formatted_in_time }}</span>
                                        @endif
                                        @if ($v->formatted_out_time)
                                            <span class="ms-2"><strong>Out:</strong> {{ $v->formatted_out_time }}</span>
                                        @endif
                                        @if ($v->time_since_exit)
                                            <div class="text-secondary fw-semibold mt-0">
                                                <i class="bi bi-hourglass-split"></i> Exited {{ $v->time_since_exit }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($v->shift)
                                    @php
                                        $shiftBadgeColor = match ($v->shift->code) {
                                            'SHIFT-A' => 'badge-indigo',
                                            'SHIFT-B' => 'badge-amber',
                                            'SHIFT-C' => 'badge-purple',
                                            default => 'badge-slate',
                                        };
                                    @endphp
                                    <span class="badge-modern {{ $shiftBadgeColor }}">
                                        <i class="bi bi-clock"></i> {{ $v->shift->name }}
                                    </span>
                                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                        {{ $v->shift->formatted_24h_range }}
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                                @if ($v->department || $v->purpose)
                                    <div class="small text-secondary mt-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-compass"></i> {{ $v->department ?: $v->purpose }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="user-avatar-chip" style="width: 28px; height: 28px; font-size: 0.72rem;">
                                        {{ strtoupper(substr($v->organizer->name ?? 'S', 0, 2)) }}
                                    </span>
                                    <div>
                                        <span
                                            class="fw-semibold text-dark">{{ $v->organizer->name ?? 'Unassigned' }}</span>
                                        @if ($v->organizer?->department)
                                            <div class="small text-muted" style="font-size: 0.72rem;">
                                                {{ $v->organizer?->department }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($v->feedback)
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill"></i> Completed
                                        ({{ $v->feedback->overall_rating }} ★)
                                    </span>
                                    @if ($v->feedback->is_staff_assisted)
                                        <div class="small text-muted mt-1" style="font-size: 0.70rem;">
                                            <i class="bi bi-person-check-fill text-primary"></i> Staff-assisted
                                        </div>
                                    @endif
                                @elseif ($v->is_feedback_expired)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" title="Feedback collection expired because visit day has ended">
                                        <i class="bi bi-clock-history text-secondary"></i> Expired (Day Ended)
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-hourglass-split text-warning"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($v->feedback)
                                    <a href="{{ route('feedbacks.show', $v->feedback) }}"
                                        class="btn btn-sm btn-outline-secondary py-1 px-3">
                                        <i class="bi bi-eye"></i> View Feedback
                                    </a>
                                @elseif ($v->is_feedback_expired)
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-3 disabled" disabled title="Feedback collection has expired for past visits">
                                        <i class="bi bi-lock-fill me-1"></i> Closed
                                    </button>
                                @else
                                    <a href="{{ route('visits.feedback.create', $v) }}"
                                        class="btn btn-sm {{ $v->is_exceeded_exit_threshold ? 'btn-danger text-white' : 'btn-warning text-dark' }} fw-bold py-1 px-3 shadow-sm"
                                        title="Fill feedback evaluation along with visitor">
                                        <i class="bi bi-pencil-square me-1"></i> Submit on Behalf
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-75"></i>
                                @if (($todayTab ?? 'pending') === 'pending')
                                    <div class="fw-bold text-dark fs-6">No Pending Feedback for Today!</div>
                                    <div class="small">All registered visitors for today's shifts have submitted their evaluation.</div>
                                @elseif (($todayTab ?? '') === 'completed')
                                    <div class="fw-bold text-dark fs-6">No Completed Feedback Yet Today.</div>
                                    <div class="small">Evaluations will appear here once submitted.</div>
                                @else
                                    <div class="fw-bold text-dark fs-6">No visitors recorded yet for today.</div>
                                    <a href="{{ route('visits.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-person-plus-fill me-1"></i> Register Today's First Visitor
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live Duty Shift Clock
            function updateLiveClock() {
                const el = document.getElementById('liveClockDisplay');
                if (el) {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                    el.innerHTML = '<i class="bi bi-clock me-1"></i> ' + timeStr + ' IST';
                }
            }
            updateLiveClock();
            setInterval(updateLiveClock, 1000);

            // Live Polling (every 30s) for real-time count updates
            function pollLiveDashboard() {
                const params = new URLSearchParams(window.location.search);
                fetch('{{ route('dashboard.live_feed') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success && data.counts) {
                        const totalEl = document.getElementById('cardTodayTotal');
                        const givenEl = document.getElementById('cardTodayGiven');
                        const pendingEl = document.getElementById('cardTodayPending');
                        const badgePend = document.getElementById('badgePendingCount');
                        const badgeComp = document.getElementById('badgeCompletedCount');
                        const badgeAll = document.getElementById('badgeAllCount');
                        const syncEl = document.getElementById('cardLastSync');

                        if (totalEl) totalEl.innerText = data.counts.all.toLocaleString();
                        if (givenEl) givenEl.innerText = data.counts.completed.toLocaleString();
                        if (pendingEl) pendingEl.innerText = data.counts.pending.toLocaleString();
                        if (badgePend) badgePend.innerText = data.counts.pending;
                        if (badgeComp) badgeComp.innerText = data.counts.completed;
                        if (badgeAll) badgeAll.innerText = data.counts.all;
                        if (syncEl && data.last_sync_formatted) syncEl.innerText = data.last_sync_formatted;

                        // Dynamically update Shift Deadline Alerts if returned
                        if (data.shift_alerts) {
                            const alertWrap = document.getElementById('shiftCloseAlertWrapper');
                            if (alertWrap) {
                                if (data.shift_alerts.length > 0) {
                                    alertWrap.innerHTML = data.shift_alerts.map(alert => `
                                        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 animate-pulse-subtle" 
                                             style="background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%); border-left: 5px solid #dc2626 !important;"
                                             role="alert">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="alert-icon-pulse bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 44px; height: 44px;">
                                                    <i class="bi bi-alarm-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <h6 class="fw-bold text-danger mb-0 fs-6">
                                                            ⚠️ Staff Alert: \${alert.shift_name} Feedback Due
                                                        </h6>
                                                        <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                                            <i class="bi bi-clock-history me-1"></i> \${alert.remaining_text}
                                                        </span>
                                                        <span class="badge bg-light text-danger border border-danger-subtle fw-semibold">
                                                            Shift Closes: \${alert.end_time_12h}
                                                        </span>
                                                    </div>
                                                    <div class="text-dark small mt-1">
                                                        <strong>\${alert.pending_count} visitor(s)</strong> in \${alert.shift_name} have not filled feedback within the shift time limit. Please collect feedback before shift handover!
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('shift_queue') }}?today_tab=pending&shift_id=\${alert.shift_id}" 
                                                   class="btn btn-sm btn-danger fw-semibold px-3 shadow-xs">
                                                    <i class="bi bi-person-exclamation me-1"></i> View \${alert.shift_name} Pending (\${alert.pending_count})
                                                </a>
                                            </div>
                                        </div>
                                    `).join('');
                                } else {
                                    alertWrap.innerHTML = '';
                                }
                            }
                        }
                    }
                })
                .catch(() => {});
            }
            setInterval(pollLiveDashboard, 30000);
        });
    </script>
@endpush
