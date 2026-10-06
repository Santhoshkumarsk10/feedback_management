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
            <div class="stat-metric-number">{{ number_format($stats['avg'], 1) }}<span class="fs-5 text-muted fw-normal">/5</span></div>
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
            <div class="stat-metric-number {{ $stats['low'] > 0 ? 'text-danger' : 'text-slate-700' }}">{{ $stats['low'] }}</div>
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
                    $responseRate = $o->visits_count > 0 ? min(100, round(($o->feedbacks_count / $o->visits_count) * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        @if($index === 0)
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
                        @if($o->department)
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
                                <div class="progress-bar-emerald" style="width: {{ $responseRate }}%; height: 100%;"></div>
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
                                <span class="small text-muted d-block">{{ $f->submitted_at->format('d M Y') }}</span>
                                <span class="small text-slate-400" style="font-size: 0.7rem;">{{ $f->submitted_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $f->visit->visitor_name }}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-building"></i> {{ $f->visit->visitor_company ?? 'Individual' }}
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
                                <a href="{{ route('feedbacks.show', $f) }}" class="btn-action-icon view" title="View details">
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
                        @if($f->comments)
                            <div class="p-2 rounded bg-light small text-slate-700 fst-italic mb-2 border-start border-2 border-danger">
                                “{{ \Illuminate\Support\Str::limit($f->comments, 90) }}”
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="small text-muted">{{ $f->submitted_at->format('d M Y, H:i') }}</span>
                            <a href="{{ route('feedbacks.show', $f) }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
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
                        <p class="text-muted small mb-0">No low ratings (≤ 2 ★) recorded recently. Keep up the high standards!</p>
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
document.addEventListener('DOMContentLoaded', function () {
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
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { family: 'Plus Jakarta Sans', size: 11 },
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
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { family: 'Plus Jakarta Sans', size: 11 },
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
});
</script>
@endpush
