@extends('layouts.app')
@section('title', 'Executive Operations Dashboard')
@section('page_title', 'Plant Operations Overview')
@section('page_subtitle', 'Live analytics on visitor tours, organizer performance, and plant feedback sentiment')

@section('topbar_actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shift_queue') }}" class="btn-modern-primary btn-sm">
            <i class="bi bi-clock-history"></i>
            <span>Today's Shift Queue</span>
            @if(($todayCounts['pending'] ?? 0) > 0)
                <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $todayCounts['pending'] }}</span>
            @endif
        </a>
        <a href="{{ route('reports.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span class="d-none d-sm-inline">Reports</span>
        </a>
        <a href="{{ route('feedbacks.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-chat-text-fill"></i>
            <span class="d-none d-sm-inline">All Feedback</span>
        </a>
    </div>
@endsection

@section('content')
    <!-- Shift Feedback Deadline Alert Notification for Staff -->
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
                                ⚠️ Shift Feedback Alert: {{ $alert['shift_name'] }}
                            </h6>
                            <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                <i class="bi bi-clock-history me-1"></i> {{ $alert['remaining_text'] }}
                            </span>
                            <span class="badge bg-light text-danger border border-danger-subtle fw-semibold">
                                Shift Closes: {{ $alert['end_time_12h'] }}
                            </span>
                        </div>
                        <div class="text-dark small mt-1">
                            <strong>{{ $alert['pending_count'] }} visitor(s)</strong> have not submitted feedback within the shift time limit. Please review and collect pending feedback before the shift handover closes!
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('shift_queue', ['today_tab' => 'pending', 'shift_id' => $alert['shift_id']]) }}" 
                       class="btn btn-sm btn-danger fw-semibold px-3 shadow-xs">
                        <i class="bi bi-person-exclamation me-1"></i> View Pending Visitors ({{ $alert['pending_count'] }})
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Live Duty Shift Tracker & Active Shift Monitor with Direct Shift Queue Access -->
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
                    <div class="shift-status-card p-3 p-md-3 d-inline-block text-start w-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="shift-status-icon-badge">
                                    <i class="bi bi-calendar2-week-fill"></i>
                                </span>
                                <div>
                                    <h6 class="shift-status-title">Today's Shift Status</h6>
                                </div>
                            </div>
                            @php
                                $pendingCount = $todayCounts['pending'] ?? 0;
                            @endphp
                            @if ($pendingCount > 0)
                                <span class="shift-badge-pill status-pending" title="Action required: collect feedback before shift ends">
                                    <span class="status-pulse-dot warning"></span>
                                    <span>{{ $pendingCount }} Awaiting</span>
                                </span>
                            @else
                                <span class="shift-badge-pill status-cleared" title="All visitors for today have submitted feedback">
                                    <span class="status-pulse-dot success"></span>
                                    <span>All Cleared</span>
                                </span>
                            @endif
                        </div>

                        <!-- Micro Metrics Tiles -->
                        <div class="shift-metrics-grid">
                            <div class="shift-metric-tile">
                                <div class="shift-metric-label">
                                    <i class="bi bi-people-fill text-primary"></i> Arrivals
                                </div>
                                <div class="shift-metric-value">
                                    {{ $stats['today_total'] }}
                                </div>
                                <div class="shift-metric-sub">Logged</div>
                            </div>
                            <div class="shift-metric-tile">
                                <div class="shift-metric-label">
                                    <i class="bi bi-check-circle-fill text-success"></i> Done
                                </div>
                                <div class="shift-metric-value {{ $stats['today_given'] > 0 ? 'highlight-emerald' : '' }}">
                                    {{ $stats['today_given'] }}
                                </div>
                                <div class="shift-metric-sub">Completed</div>
                            </div>
                            <div class="shift-metric-tile">
                                <div class="shift-metric-label">
                                    <i class="bi bi-star-fill text-warning"></i> Avg Score
                                </div>
                                <div class="shift-metric-value {{ $stats['today_avg'] ? 'highlight-amber' : '' }}">
                                    {{ $stats['today_avg'] ? number_format($stats['today_avg'], 1) : '—' }}
                                    @if($stats['today_avg'])
                                        <small class="fs-7 text-warning">★</small>
                                    @endif
                                </div>
                                <div class="shift-metric-sub">Rating</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('shift_queue') }}" class="btn-shift-queue-primary flex-grow-1">
                                <i class="bi bi-clock-history me-2"></i>
                                <span>Open Live Shift Queue</span>
                                <i class="bi bi-chevron-right ms-auto opacity-75 small"></i>
                            </a>
                            <a href="{{ route('visits.create') }}" class="btn-shift-queue-secondary" title="Register New Visitor" data-bs-toggle="tooltip">
                                <i class="bi bi-person-plus-fill fs-6"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Organizers -->
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

        <!-- 2. Visitors -->
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

        <!-- 3. Total Visitors -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget cyan">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Total Visitors</span>
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

        <!-- 4. Feedback -->
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

        <!-- 5. Avg Rating -->
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

        <!-- 6. Lowest Rating -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card-widget rose">
                <div class="stat-widget-header">
                    <span class="stat-label-text">Lowest Rating</span>
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
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="card-title">
                        <i class="bi bi-graph-up-arrow text-primary"></i>
                        <span id="timelineTitle">Visits Timeline (Last 6 Months)</span>
                    </div>
                    <div class="timeline-filter-group" role="group" aria-label="Visits Timeline Granularity">
                        <button type="button" class="timeline-filter-btn" data-period="daily" onclick="switchTimeline('daily')">
                            Daily
                        </button>
                        <button type="button" class="timeline-filter-btn" data-period="weekly" onclick="switchTimeline('weekly')">
                            Weekly
                        </button>
                        <button type="button" class="timeline-filter-btn active" data-period="monthly" onclick="switchTimeline('monthly')">
                            Monthly
                        </button>
                    </div>
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

                    // Visits Timeline Line Chart with Daily, Weekly, and Monthly Switching
                    const monthCtx = document.getElementById('monthChart');
                    const timelineDatasets = {!! json_encode($timeline ?? []) !!};

                    let visitsChart = null;
                    if (monthCtx) {
                        const gradient = monthCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
                        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

                        visitsChart = new Chart(monthCtx, {
                            type: 'line',
                            data: {
                                labels: (timelineDatasets.monthly && timelineDatasets.monthly.labels) ? timelineDatasets.monthly.labels : [],
                                datasets: [{
                                    label: 'Visits',
                                    data: (timelineDatasets.monthly && timelineDatasets.monthly.values) ? timelineDatasets.monthly.values : [],
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
                                        cornerRadius: 8,
                                        callbacks: {
                                            label: function(context) {
                                                return ' ' + context.parsed.y + ' Visit' + (context.parsed.y === 1 ? '' : 's');
                                            }
                                        }
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

                    window.switchTimeline = function(period) {
                        if (!visitsChart || !timelineDatasets[period]) return;

                        // Update Active button state
                        document.querySelectorAll('.timeline-filter-btn').forEach(btn => {
                            btn.classList.toggle('active', btn.getAttribute('data-period') === period);
                        });

                        // Update Title
                        const titleEl = document.getElementById('timelineTitle');
                        if (titleEl) {
                            if (period === 'daily') titleEl.textContent = 'Visits Timeline (Daily — Last 14 Days)';
                            else if (period === 'weekly') titleEl.textContent = 'Visits Timeline (Weekly — Last 8 Weeks)';
                            else titleEl.textContent = 'Visits Timeline (Last 6 Months)';
                        }

                        // Update Chart Data with smooth animation
                        visitsChart.data.labels = timelineDatasets[period].labels;
                        visitsChart.data.datasets[0].data = timelineDatasets[period].values;
                        visitsChart.update();
                    };

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
