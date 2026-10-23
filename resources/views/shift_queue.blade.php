@extends('layouts.app')
@section('title', "Today's Live Queue")
@section('page_title', "Today's Live Queue")
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
    <!-- Live Daily Operations & Staff Monitor -->
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
                            LIVE VISITOR OPERATIONS
                        </span>
                        <span class="small text-muted" id="liveClockDisplay">
                            <i class="bi bi-clock me-1"></i>
                            {{ now(config('app.plant_timezone', 'Asia/Kolkata'))->format('h:i:s A') }} IST
                        </span>
                    </div>

                    <div class="d-flex flex-wrap align-items-baseline gap-2 mb-2">
                        <h4 class="fw-bold text-dark mb-0">
                            Today's Plant Operations
                        </h4>
                        <span class="badge bg-primary text-white fs-6 px-3 py-1">
                            {{ now(config('app.plant_timezone', 'Asia/Kolkata'))->format('d M Y') }}
                        </span>
                    </div>

                    <div class="small text-muted d-flex flex-wrap align-items-center gap-3">
                        <span><i class="bi bi-person-badge text-primary"></i> Logged Staff:
                            <strong>{{ $currentUser->name }}</strong> ({{ ucfirst($currentUser->role) }})</span>
                        @if ($currentUser->department)
                            <span><i class="bi bi-building text-secondary"></i> {{ $currentUser->department }}</span>
                        @endif
                        @if ($currentUser->plant)
                            <span><i class="bi bi-geo-alt text-secondary"></i> {{ $currentUser->plant->name }}</span>
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

    <!-- Section: Today's Feedback Metrics -->
    <div class="row g-3 mb-4">
        <!-- Visitors Today -->
        <div class="col-6 col-md-4 col-xl">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/visitors-today.svg" width="48" height="48" alt="Visitors Today">
                        <div>
                            <div class="saas-stat-value" id="cardTodayTotal">{{ number_format($stats['today_total']) }}</div>
                            <div class="saas-stat-label">Visitors Today</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge cyan">Arrivals</span>
                </div>
            </div>
        </div>

        <!-- Feedback Given -->
        <div class="col-6 col-md-4 col-xl">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/feedback-given.svg" width="48" height="48" alt="Feedback Given">
                        <div>
                            <div class="saas-stat-value" id="cardTodayGiven">{{ number_format($stats['today_given']) }}</div>
                            <div class="saas-stat-label">Feedback Given</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge emerald" id="cardTodayGivenPct">{{ $stats['today_given_pct'] }}% Done</span>
                </div>
            </div>
        </div>

        <!-- Feedback Pending -->
        <div class="col-6 col-md-4 col-xl">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/feedback-pending.svg" width="48" height="48" alt="Feedback Pending">
                        <div>
                            <div class="saas-stat-value" id="cardTodayPending">{{ number_format($stats['today_pending']) }}</div>
                            <div class="saas-stat-label">Feedback Pending</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge amber" id="cardTodayPendingPct">{{ $stats['today_pending_pct'] }}% Pending</span>
                </div>
            </div>
        </div>

        <!-- Today's Avg Rating -->
        <div class="col-6 col-md-4 col-xl">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/average-rating.svg" width="48" height="48" alt="Sentiment">
                        <div>
                            <div class="saas-stat-value" id="cardTodayAvg">
                                {{ $stats['today_avg'] ? number_format($stats['today_avg'], 1) : '—' }}@if($stats['today_avg'])<span class="fs-6 text-muted fw-normal">/5</span>@endif
                            </div>
                            <div class="saas-stat-label">Today's Avg Rating</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge indigo">Sentiment</span>
                </div>
            </div>
        </div>

        <!-- API Sync Time -->
        <div class="col-6 col-md-4 col-xl">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/api-sync.svg" width="48" height="48" alt="Gate Pass Sync">
                        <div>
                            <div class="saas-stat-value fs-5 text-truncate" id="cardLastSync" style="font-size: 1.25rem !important;">
                                {{ $stats['last_sync_at'] ? \Carbon\Carbon::parse($stats['last_sync_at'])->diffForHumans(null, true) . ' ago' : 'Live' }}
                            </div>
                            <div class="saas-stat-label">API Sync Feed</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge slate">Gate Pass</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Visitors: Queue & Operations Board -->
    <div class="saas-table-card mb-4">
        <div class="saas-table-header">
            <div>
                <div class="saas-table-title">Today's Plant Visitors & Feedback Queue</div>
                <div class="saas-table-subtitle">Track visitor feedback status for today's visits and submit evaluations on behalf of visitors</div>
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

                <a href="{{ route('shift_queue.export_pending', request()->query()) }}" class="saas-btn-filter text-decoration-none" title="Export today pending list to CSV">
                    <i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i> Export Pending
                </a>

                <form action="{{ route('visits.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="saas-btn-filter text-success border-success-subtle bg-white"
                        title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Gate API
                    </button>
                </form>

                <a href="{{ route('visits.create') }}" class="btn-action-edit-pill text-decoration-none">
                    <i class="bi bi-person-plus-fill"></i> Add Visitor
                </a>

                <a href="{{ route('visits.index', ['today' => 1]) }}" class="btn btn-sm btn-link text-decoration-none text-muted">
                    <span>Full Log</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Filter Tabs & Controls Toolbar -->
        <div class="saas-toolbar-card border-0 rounded-0 border-bottom mb-0 shadow-none px-3 py-2 bg-light">
            <div class="saas-filter-pills">
                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'pending'])) }}"
                    class="saas-pill-btn {{ ($todayTab ?? 'pending') === 'pending' ? 'active' : '' }}">
                    <i class="bi bi-clock-history text-warning"></i>
                    <span>Awaiting feedback</span>
                    <span class="pill-counter" id="badgePendingCount">{{ $todayCounts['pending'] }}</span>
                </a>

                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'completed'])) }}"
                    class="saas-pill-btn {{ ($todayTab ?? '') === 'completed' ? 'active' : '' }}">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Feedback completed</span>
                    <span class="pill-counter" id="badgeCompletedCount">{{ $todayCounts['completed'] }}</span>
                </a>

                <a href="{{ route('shift_queue', array_merge(request()->query(), ['today_tab' => 'all'])) }}"
                    class="saas-pill-btn {{ ($todayTab ?? '') === 'all' ? 'active' : '' }}">
                    <i class="bi bi-people-fill text-primary"></i>
                    <span>All today's visitors</span>
                    <span class="pill-counter" id="badgeAllCount">{{ $todayCounts['all'] }}</span>
                </a>
            </div>

            <div class="saas-toolbar-controls">
                <form method="GET" action="{{ route('shift_queue') }}" class="d-flex align-items-center gap-2">
                    @if(request('today_tab'))
                        <input type="hidden" name="today_tab" value="{{ request('today_tab') }}">
                    @endif
                    @if(request('scope'))
                        <input type="hidden" name="scope" value="{{ request('scope') }}">
                    @endif

                    <div class="saas-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="saas-search-input" placeholder="Search visitor, company, mobile..."
                               value="{{ request('search') }}">
                    </div>

                    <button type="submit" class="saas-btn-filter">Search</button>
                    @if(request('search'))
                        <a href="{{ route('shift_queue', request()->except('search')) }}" class="saas-btn-filter text-muted">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>VISITOR INFORMATION</th>
                        <th>GATE IN / OUT STATUS</th>
                        <th>DEPARTMENT / PURPOSE</th>
                        <th>ASSIGNED STAFF</th>
                        <th>STATUS</th>
                        <th class="text-end">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayVisits as $v)
                        <tr style="{{ $v->is_exceeded_exit_threshold ? 'background-color: #fff1f2; border-left: 4px solid #ef4444;' : ($v->is_checked_out && !$v->feedback ? 'background-color: #f8fafc; border-left: 4px solid #f59e0b;' : '') }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark fs-6">{{ $v->visitor_name }}</span>
                                    @if ($v->visitor_code)
                                        <span class="saas-code-badge">
                                            <i class="bi bi-qr-code me-1"></i>{{ $v->visitor_code }}
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
                                                <i class="bi bi-exclamation-triangle-fill"></i> >2h Alert
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
                                @if ($v->department)
                                    <div class="fw-semibold text-dark small">
                                        <i class="bi bi-building me-1 text-primary"></i> {{ $v->department }}
                                    </div>
                                @endif
                                @if ($v->purpose)
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-compass me-1"></i> {{ $v->purpose }}
                                    </div>
                                @endif
                                @if (!$v->department && !$v->purpose)
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
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
                                        class="btn-action-edit-pill text-decoration-none">
                                        <i class="bi bi-eye"></i> View Feedback
                                    </a>
                                @elseif ($v->is_feedback_expired)
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-3 disabled" disabled title="Feedback collection has expired for past visits">
                                        <i class="bi bi-lock-fill me-1"></i> Closed
                                    </button>
                                @else
                                    <a href="{{ route('visits.feedback.create', $v) }}"
                                        class="btn-action-edit-pill text-decoration-none" style="background: {{ $v->is_exceeded_exit_threshold ? '#fff1f2' : '#fffbeb' }}; color: {{ $v->is_exceeded_exit_threshold ? '#e11d48' : '#b45309' }}; border-color: {{ $v->is_exceeded_exit_threshold ? '#fecdd3' : '#fde68a' }};"
                                        title="Fill feedback evaluation along with visitor">
                                        <i class="bi bi-pencil-square"></i> Submit on Behalf
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
                                    <div class="small">All registered visitors for today have submitted their evaluation.</div>
                                @elseif (($todayTab ?? '') === 'completed')
                                    <div class="fw-bold text-dark fs-6">No Completed Feedback Yet Today.</div>
                                    <div class="small">Evaluations will appear here once submitted.</div>
                                @else
                                    <div class="fw-bold text-dark fs-6">No visitors recorded yet for today.</div>
                                    <a href="{{ route('visits.create') }}" class="btn-action-edit-pill text-decoration-none mt-2 d-inline-block">
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
            // Live Clock
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
                    }
                })
                .catch(() => {});
            }
            setInterval(pollLiveDashboard, 30000);
        });
    </script>
@endpush
