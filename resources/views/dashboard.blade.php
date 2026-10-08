@extends('layouts.app')
@section('title', 'Executive Operations Dashboard')
@section('page_title', 'Plant Operations Overview')
@section('page_subtitle', 'Live analytics on visitor tours, organizer performance, and plant feedback sentiment')

@section('topbar_actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('reports.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span class="d-none d-sm-inline">Reports</span>
        </a>
        <a href="{{ route('feedbacks.index') }}" class="btn-modern-primary btn-sm">
            <i class="bi bi-chat-text-fill"></i>
            <span>View All Feedback</span>
        </a>
    </div>
@endsection

@section('content')
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
                        <a href="{{ route('mobile.app') }}" target="_blank"
                            class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold">
                            <i class="bi bi-tablet me-1"></i> Tablet App <i class="bi bi-box-arrow-up-right fs-xs ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Visitors: Pending vs Completed Operations Board -->
    <div class="card-modern mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="card-title">
                    <i class="bi bi-calendar2-day-fill text-primary"></i>
                    <span>Today's Plant Visitors & Feedback Queue</span>
                </div>
                <div class="text-muted small mt-1">Track visitor feedback status for today's shift and submit evaluations on
                    behalf of visitors</div>
            </div>
            <div class="d-flex align-items-center gap-2">
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
                    <span>View Full Log</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Filter Tabs Header: Pending vs Completed vs All -->
        <div class="filter-tabs-header bg-light border-bottom px-3 pt-2">
            <div class="filter-tabs-nav">
                <a href="{{ route('dashboard', ['today_tab' => 'pending']) }}"
                    class="filter-tab-btn {{ ($todayTab ?? 'pending') === 'pending' ? 'active' : '' }}"
                    style="{{ ($todayTab ?? 'pending') === 'pending' ? 'border-bottom: 3px solid #f59e0b; color: #b45309; font-weight: 700;' : '' }}">
                    <i class="bi bi-clock-history text-warning"></i>
                    <span>Awaiting Feedback (Pending)</span>
                    <span
                        class="badge {{ $todayCounts['pending'] > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white' }} rounded-pill ms-1">
                        {{ $todayCounts['pending'] }}
                    </span>
                </a>

                <a href="{{ route('dashboard', ['today_tab' => 'completed']) }}"
                    class="filter-tab-btn {{ ($todayTab ?? '') === 'completed' ? 'active' : '' }}"
                    style="{{ ($todayTab ?? '') === 'completed' ? 'border-bottom: 3px solid #10b981; color: #047857; font-weight: 700;' : '' }}">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Feedback Completed</span>
                    <span class="badge bg-success rounded-pill ms-1">
                        {{ $todayCounts['completed'] }}
                    </span>
                </a>

                <a href="{{ route('dashboard', ['today_tab' => 'all']) }}"
                    class="filter-tab-btn {{ ($todayTab ?? '') === 'all' ? 'active' : '' }}">
                    <i class="bi bi-people-fill text-primary"></i>
                    <span>All Today's Visitors</span>
                    <span class="badge bg-secondary rounded-pill ms-1">
                        {{ $todayCounts['all'] }}
                    </span>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Visitor Information</th>
                        <th>Duty Shift</th>
                        <th>Assigned Staff</th>
                        <th>Purpose of Visit</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayVisits as $v)
                        <tr>
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
                                        {{ $v->shift->formatted_24h_range }}</div>
                                @else
                                    <span class="text-muted small">—</span>
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
                                <span class="badge-modern badge-slate">{{ $v->purpose ?: 'Plant Tour' }}</span>
                            </td>
                            <td>
                                @if ($v->feedback)
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill"></i> Completed
                                        ({{ $v->feedback->overall_rating }} ★)
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
                                @else
                                    <a href="{{ route('visits.feedback.create', $v) }}"
                                        class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 shadow-sm"
                                        title="Fill feedback evaluation along with visitor">
                                        <i class="bi bi-pencil-square me-1"></i> Submit on Behalf (அவருக்காக பதிவு செய்க)
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-75"></i>
                                @if (($todayTab ?? 'pending') === 'pending')
                                    <div class="fw-bold text-dark fs-6">No Pending Feedback for Today!</div>
                                    <div class="small">All registered visitors for today's shifts have submitted their
                                        evaluation.</div>
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

    <!-- Key Performance Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Visits -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget cyan">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Total Visits</span>
                    <div class="stat-icon-bubble cyan">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ number_format($stats['visits']) }}</div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-geo-alt"></i> Tours Logged
                </span>
            </div>
        </div>

        <!-- Feedbacks -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget emerald">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Feedback</span>
                    <div class="stat-icon-bubble emerald">
                        <i class="bi bi-chat-heart-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ number_format($stats['feedbacks']) }}</div>
                <span class="stat-pill-trend up">
                    <i class="bi bi-check2"></i>
                    {{ $stats['visits'] > 0 ? round(($stats['feedbacks'] / $stats['visits']) * 100) : 0 }}% response
                </span>
            </div>
        </div>

        <!-- Avg Rating -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget amber">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Avg Rating</span>
                    <div class="stat-icon-bubble amber">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ number_format($stats['avg'], 1) }}<span
                        class="fs-5 text-muted fw-normal">/5</span></div>
                <span class="stat-pill-trend {{ $stats['avg'] >= 4 ? 'up' : ($stats['avg'] >= 3 ? 'neutral' : 'down') }}">
                    <i class="bi bi-award-fill"></i> {{ $stats['avg'] >= 4 ? 'Excellent' : 'Average' }}
                </span>
            </div>
        </div>

        <!-- Plant Organizers -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget indigo">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Organizers</span>
                    <div class="stat-icon-bubble indigo">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ number_format($stats['organizers']) }}</div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-shield-check"></i> Staff Members
                </span>
            </div>
        </div>

        <!-- Unique Visitors -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget slate">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Visitors</span>
                    <div class="stat-icon-bubble slate">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ number_format($stats['visitors']) }}</div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-building"></i> Unique Guests
                </span>
            </div>
        </div>

        <!-- Low Ratings -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget rose">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Low (≤2 ★)</span>
                    <div class="stat-icon-bubble rose">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number {{ $stats['low'] > 0 ? 'text-danger' : 'text-slate-700' }}">
                    {{ $stats['low'] }}</div>
                <span class="stat-pill-trend {{ $stats['low'] > 0 ? 'down' : 'up' }}">
                    <i class="bi {{ $stats['low'] > 0 ? 'bi-bell-fill' : 'bi-check-all' }}"></i>
                    {{ $stats['low'] > 0 ? 'Needs Attention' : 'Zero Issues' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card-modern card-modern-hover h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-bar-chart-fill text-success"></i>
                        <span>Rating Distribution (1★ to 5★)</span>
                    </div>
                    <span class="badge-modern badge-slate">{{ array_sum($distribution) }} Ratings</span>
                </div>
                <div class="card-body">
                    <canvas id="ratingChart" height="210"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-modern card-modern-hover h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-graph-up-arrow text-primary"></i>
                        <span>Visits Timeline (Last 6 Months)</span>
                    </div>
                    <span class="badge-modern badge-indigo">Trend Analysis</span>
                </div>
                <div class="card-body">
                    <canvas id="monthChart" height="210"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizer Performance Leaderboard -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-trophy-fill text-warning"></i>
                <span>Organizer Tour & Feedback Performance</span>
            </div>
            <a href="{{ route('users.index') }}" class="btn-action-icon view" title="View all users">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Organizer</th>
                        <th>Department</th>
                        <th>Visits Handled</th>
                        <th>Feedbacks Received</th>
                        <th>Feedback Rate</th>
                        <th class="text-end">Avg Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaders as $index => $o)
                        @php
                            $responseRate =
                                $o->visits_count > 0
                                    ? min(100, round(($o->feedbacks_count / $o->visits_count) * 100))
                                    : 0;
                        @endphp
                        <tr>
                            <td>
                                @if ($index === 0)
                                    <span class="badge-modern badge-amber"><i class="bi bi-award-fill"></i> #1</span>
                                @elseif($index === 1)
                                    <span class="badge-modern badge-slate"><i class="bi bi-award"></i> #2</span>
                                @elseif($index === 2)
                                    <span class="badge-modern badge-rose"><i class="bi bi-award"></i> #3</span>
                                @else
                                    <span class="text-muted fw-semibold ps-2">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="user-avatar-chip">
                                    {{ strtoupper(substr($o->name, 0, 2)) }}
                                </span>
                                <span class="fw-semibold text-dark">{{ $o->name }}</span>
                            </td>
                            <td>
                                @if ($o->department)
                                    <span class="badge-modern badge-indigo">{{ $o->department }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ $o->visits_count }}</span>
                                <span class="text-muted small">tours</span>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $o->feedbacks_count }}</span>
                                <span class="text-muted small">reviews</span>
                            </td>
                            <td style="min-width: 140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-modern flex-grow-1">
                                        <div class="progress-bar-emerald"
                                            style="width: {{ $responseRate }}%; height: 100%;"></div>
                                    </div>
                                    <span class="small fw-semibold text-muted">{{ $responseRate }}%</span>
                                </div>
                            </td>
                            <td class="text-end">
                                @include('partials.rating', ['value' => $o->avg_rating])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-3 d-block mb-1 text-slate-400"></i>
                                No organizers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Feedbacks & Critical Attention -->
    <div class="row g-4">
        <!-- Recent Feedback Stream -->
        <div class="col-lg-7">
            <div class="card-modern h-100">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-clock-history text-teal"></i>
                        <span>Recent Visitor Feedbacks</span>
                    </div>
                    <a href="{{ route('feedbacks.index') }}" class="small fw-semibold text-decoration-none text-success">
                        All reviews <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Visitor</th>
                                <th>Organizer</th>
                                <th>Rating</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $f)
                                <tr>
                                    <td>
                                        <span
                                            class="small text-muted d-block">{{ $f->submitted_at->format('d M Y') }}</span>
                                        <span class="small text-slate-400"
                                            style="font-size: 0.7rem;">{{ $f->submitted_at->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $f->visit->visitor_name }}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-building"></i>
                                            {{ $f->visit->visitor_company ?? 'Individual' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-slate">
                                            <i class="bi bi-person"></i> {{ $f->organizer->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @include('partials.rating', ['value' => $f->overall_rating])
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('feedbacks.show', $f) }}" class="btn-action-icon view"
                                            title="View details">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No feedbacks recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low-Rated Reviews (Attention Required) -->
        <div class="col-lg-5">
            <div class="card-modern h-100 border-start border-danger border-3">
                <div class="card-header bg-danger-subtle bg-opacity-25">
                    <div class="card-title text-danger">
                        <i class="bi bi-shield-exclamation text-danger"></i>
                        <span>Needs Attention (≤ 2 ★)</span>
                    </div>
                    <span class="badge-modern badge-rose">{{ count($low) }} Flagged</span>
                </div>
                <div class="p-3">
                    @forelse($low as $f)
                        <div class="p-3 mb-3 bg-white rounded-3 border border-danger-subtle shadow-xs">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark">{{ $f->visit->visitor_name }}</span>
                                    <span class="text-muted small">({{ $f->visit->visitor_company ?? 'Visitor' }})</span>
                                    <div class="small text-muted mt-0">
                                        Organizer: <strong>{{ $f->organizer->name }}</strong>
                                    </div>
                                </div>
                                <div>
                                    @include('partials.rating', ['value' => $f->overall_rating])
                                </div>
                            </div>
                            @if ($f->comments)
                                <div
                                    class="p-2 rounded bg-light small text-slate-700 fst-italic mb-2 border-start border-2 border-danger">
                                    “{{ \Illuminate\Support\Str::limit($f->comments, 90) }}”
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="small text-muted">{{ $f->submitted_at->format('d M Y, H:i') }}</span>
                                <a href="{{ route('feedbacks.show', $f) }}"
                                    class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
                                    Investigate <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="stat-icon-bubble emerald mx-auto mb-2" style="width: 52px; height: 52px;">
                                <i class="bi bi-emoji-smile-fill fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Excellent Plant Feedback!</h6>
                            <p class="text-muted small mb-0">No low ratings (≤ 2 ★) recorded recently. Keep up the high
                                standards!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                    // Rating distribution bar chart
                    const ratingCtx = document.getElementById('ratingChart');
                    if (ratingCtx) {
                        new Chart(ratingCtx, {
                            type: 'bar',
                            data: {
                                labels: ['1 ★ Critical', '2 ★ Poor', '3 ★ Average', '4 ★ Good', '5 ★ Excellent'],
                                datasets: [{
                                    label: 'Feedback Count',
                                    data: @json($distribution),
                                    backgroundColor: [
                                        '#ef4444',
                                        '#f97316',
                                        '#f59e0b',
                                        '#10b981',
                                        '#059669'
                                    ],
                                    borderRadius: 8,
                                    borderSkipped: false,
                                    barThickness: 28,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        titleFont: {
                                            family: 'Plus Jakarta Sans',
                                            size: 13
                                        },
                                        bodyFont: {
                                            family: 'Plus Jakarta Sans',
                                            size: 12
                                        },
                                        padding: 10,
                                        cornerRadius: 8
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            font: {
                                                family: 'Plus Jakarta Sans',
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: '#64748b'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0,
                                            font: {
                                                family: 'Plus Jakarta Sans',
                                                size: 11
                                            },
                                            color: '#94a3b8'
                                        },
                                        grid: {
                                            color: '#f1f5f9'
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Monthly Visits Trend Line Chart
                    const monthCtx = document.getElementById('monthChart');
                    if (monthCtx) {
                        const gradient = monthCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
                        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

                        new Chart(monthCtx, {
                                type: 'line',
                                data: {
                                    labels: @json($months->keys()),
                                    datasets: [{
                                        label: 'Visits',
                                        data: @json($months->values()),
                                        tension: 0.38,
                                        borderColor: '#6366f1',
                                        borderWidth: 3,
                                        pointBackgroundColor: '#ffffff',
                                        pointBorderColor: '#6366f1',
                                        pointBorderWidth: 2,
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        fill: true,
                                        backgroundColor: gradient
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            backgroundColor: '#0f172a',
                                            padding: 10,
                                            cornerRadius: 8
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                font: {
                                                    family: 'Plus Jakarta Sans',
                                                    size: 11,
                                                    weight: '600'
                                                },
                                                color: '#64748b'
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                precision: 0,
                                                font: {
                                                    family: 'Plus Jakarta Sans',
                                                    size: 11
                                                },
                                                color: '#94a3b8'
                                            },
                                            grid: {
                                                color: '#f1f5f9'
                                            }
                                        }
                                    }
                                }
                            });
                        }

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
                    });
    </script>
@endpush
