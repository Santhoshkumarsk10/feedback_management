@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', "Welcome back! Here's an overview of your visitor feedback.")

@section('content')
<div class="saas-dashboard-container">
    <!-- Active Shift Deadline Alerts if any -->
    @if(!empty($shiftAlerts))
        @foreach($shiftAlerts as $alert)
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 animate-pulse-subtle" 
                 style="background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%); border-left: 5px solid #dc2626 !important;"
                 role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bi bi-alarm-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="fw-bold text-danger mb-0 fs-6">
                                Shift Feedback Alert: {{ $alert['shift_name'] }}
                            </h6>
                            <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                {{ $alert['remaining_text'] }}
                            </span>
                        </div>
                        <div class="text-dark small mt-1">
                            <strong>{{ $alert['pending_count'] }} visitor(s)</strong> awaiting feedback before shift closure.
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('shift_queue', ['today_tab' => 'pending', 'shift_id' => $alert['shift_id']]) }}" 
                       class="btn btn-sm btn-danger fw-semibold px-3 shadow-xs">
                        View Pending Visitors
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    <!-- 1. Top 5 SaaS Metric Cards Row -->
    <div class="saas-metric-grid">
        <!-- 1. Total Visitors -->
        <div class="saas-metric-card">
            <div class="saas-icon-box blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Total Visitors</div>
                <div class="saas-metric-value">{{ $saasMetrics['total_visitors']['value'] }}</div>
                <div class="saas-trend-badge green">
                    <i class="bi bi-arrow-up-short fs-6"></i>
                    <span>{{ $saasMetrics['total_visitors']['growth'] }}</span>
                    <span class="saas-trend-sub">{{ $saasMetrics['total_visitors']['subtext'] }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Total Feedbacks -->
        <div class="saas-metric-card">
            <div class="saas-icon-box emerald">
                <i class="bi bi-chat-left-dots-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Total Feedbacks</div>
                <div class="saas-metric-value">{{ $saasMetrics['total_feedbacks']['value'] }}</div>
                <div class="saas-trend-badge green">
                    <i class="bi bi-arrow-up-short fs-6"></i>
                    <span>{{ $saasMetrics['total_feedbacks']['growth'] }}</span>
                    <span class="saas-trend-sub">{{ $saasMetrics['total_feedbacks']['subtext'] }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Average Rating -->
        <div class="saas-metric-card">
            <div class="saas-icon-box amber">
                <i class="bi bi-star-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Average Rating</div>
                <div class="saas-metric-value">{{ $saasMetrics['average_rating']['value'] }}</div>
                <div class="saas-trend-badge green">
                    <i class="bi bi-arrow-up-short fs-6"></i>
                    <span>{{ $saasMetrics['average_rating']['growth'] }}</span>
                    <span class="saas-trend-sub">{{ $saasMetrics['average_rating']['subtext'] }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Positive Feedbacks -->
        <div class="saas-metric-card">
            <div class="saas-icon-box rose">
                <i class="bi bi-emoji-smile-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Positive Feedbacks</div>
                <div class="saas-metric-value">{{ $saasMetrics['positive_feedbacks']['value'] }}</div>
                <div class="saas-trend-badge green">
                    <i class="bi bi-arrow-up-short fs-6"></i>
                    <span>{{ $saasMetrics['positive_feedbacks']['growth'] }}</span>
                    <span class="saas-trend-sub">{{ $saasMetrics['positive_feedbacks']['subtext'] }}</span>
                </div>
            </div>
        </div>

        <!-- 5. Pending Actions -->
        <div class="saas-metric-card">
            <div class="saas-icon-box purple">
                <i class="bi bi-chat-square-dots-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Pending Actions</div>
                <div class="saas-metric-value">{{ $saasMetrics['pending_actions']['value'] }}</div>
                <div class="saas-trend-badge rose">
                    <i class="bi bi-arrow-up-short fs-6"></i>
                    <span>{{ $saasMetrics['pending_actions']['growth'] }}</span>
                    <span class="saas-trend-sub">{{ $saasMetrics['pending_actions']['subtext'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Dual Charts Row: Feedback Trends & Rating Distribution -->
    <div class="row g-3 mb-4">
        <!-- Feedback Trends (Smooth Dual-Line Area Chart) -->
        <div class="col-12 col-xl-7">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Trends</h5>
                    <select class="saas-select-pill" id="trendsTimeFilter" onchange="updateTrendsPeriod(this.value)">
                        <option value="30">Last 30 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="7">Last 7 Days</option>
                    </select>
                </div>
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="feedbackTrendsChart"></canvas>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-4 mt-3 pt-2">
                    <div class="d-flex align-items-center gap-2 small fw-semibold text-secondary">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #2563eb; display: inline-block;"></span>
                        <span>Total Feedbacks</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small fw-semibold text-secondary">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <span>Positive Feedbacks</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Rating Distribution Donut -->
        <div class="col-12 col-xl-5">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Rating Distribution</h5>
                    <select class="saas-select-pill">
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="row align-items-center g-3 my-auto">
                    <div class="col-12 col-sm-6 text-center">
                        <div class="saas-donut-box">
                            <canvas id="ratingDistributionDonut"></canvas>
                            <div class="saas-donut-center">
                                <div class="saas-donut-number">{{ number_format($ratingDist['total']) }}</div>
                                <div class="saas-donut-label">Total Feedbacks</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex flex-column gap-1">
                            @foreach($ratingDist['items'] as $item)
                                <div class="saas-legend-item">
                                    <span class="saas-legend-dot" style="background: {{ $item['color'] }};"></span>
                                    <span class="saas-legend-name">{{ $item['label'] }}</span>
                                    <span class="saas-legend-pct">{{ $item['percent'] }}%</span>
                                    <span class="saas-legend-cnt">{{ $item['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Bottom Grid: Feedback by Category, Top Locations, Recent Feedbacks -->
    <div class="row g-3 mb-4">
        <!-- 1. Feedback by Category -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback by Category</h5>
                    <select class="saas-select-pill">
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="d-flex flex-column gap-1 my-auto">
                    @foreach($categoryFeedback as $cat)
                        <div class="saas-progress-row">
                            <span class="saas-progress-name" title="{{ $cat['name'] }}">{{ $cat['name'] }}</span>
                            <div class="saas-progress-track">
                                <div class="saas-progress-bar" style="width: {{ $cat['percent'] }}%; background: {{ $cat['color'] }};"></div>
                            </div>
                            <span class="saas-progress-val">{{ $cat['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 2. Top Locations -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Top Locations</h5>
                    <select class="saas-select-pill">
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="d-flex flex-column gap-1 my-auto">
                    @foreach($topLocations as $loc)
                        <div class="saas-rank-row">
                            <span class="saas-rank-num">{{ $loc['rank'] }}</span>
                            <span class="saas-rank-name" title="{{ $loc['name'] }}">{{ $loc['name'] }}</span>
                            <div class="saas-rank-track">
                                <div class="saas-progress-bar" style="width: {{ $loc['percent'] }}%; background: {{ $loc['color'] }};"></div>
                            </div>
                            <span class="saas-rank-pct">{{ $loc['percent'] }}%</span>
                            <span class="saas-rank-cnt">{{ $loc['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Recent Feedbacks -->
        <div class="col-12 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Recent Feedbacks</h5>
                    <a href="{{ route('feedbacks.index') }}" class="small fw-semibold text-primary text-decoration-none">
                        View All
                    </a>
                </div>
                <div class="d-flex flex-column">
                    @forelse($recentFeedbacks as $rf)
                        <a href="{{ $rf['url'] ?? route('feedbacks.index') }}" class="saas-feedback-item">
                            <div class="saas-avatar-pill" style="background: {{ $rf['bg_color'] }}; color: {{ $rf['text_color'] }};">
                                {{ $rf['initials'] }}
                            </div>
                            <div class="saas-feedback-info">
                                <div class="saas-feedback-top">
                                    <span class="saas-feedback-name">{{ $rf['name'] }}</span>
                                    <span class="saas-feedback-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $rf['rating'] ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                </div>
                                <div class="saas-feedback-time">{{ $rf['date_time'] }}</div>
                                <div class="saas-feedback-comment">{{ $rf['comment'] }}</div>
                            </div>
                            <i class="bi bi-chevron-right saas-feedback-arrow ms-2"></i>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-square-dots fs-3 text-secondary opacity-50 d-block mb-2"></i>
                            <div class="fw-semibold small text-dark">No feedback submitted yet</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Submitted visitor feedback will appear here in real-time.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Donut Summaries Row: Visitor Type & Feedback Status -->
    <div class="row g-3 mb-4">
        <!-- Visitor Type Donut -->
        <div class="col-12 col-md-6">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Visitor Type</h5>
                    <select class="saas-select-pill">
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="row align-items-center g-3 my-auto">
                    <div class="col-12 col-sm-5 text-center">
                        <div class="saas-donut-box">
                            <canvas id="visitorTypeDonut"></canvas>
                            <div class="saas-donut-center">
                                <div class="saas-donut-number">{{ number_format($visitorTypes['total']) }}</div>
                                <div class="saas-donut-label">Total Visitors</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-7">
                        <div class="d-flex flex-column gap-1">
                            @foreach($visitorTypes['items'] as $vt)
                                <div class="saas-legend-item">
                                    <span class="saas-legend-dot" style="background: {{ $vt['color'] }};"></span>
                                    <span class="saas-legend-name">{{ $vt['label'] }}</span>
                                    <span class="saas-legend-pct">{{ $vt['percent'] }}%</span>
                                    <span class="saas-legend-cnt">{{ $vt['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Status Donut -->
        <div class="col-12 col-md-6">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Status</h5>
                    <select class="saas-select-pill">
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="row align-items-center g-3 my-auto">
                    <div class="col-12 col-sm-5 text-center">
                        <div class="saas-donut-box">
                            <canvas id="feedbackStatusDonut"></canvas>
                            <div class="saas-donut-center">
                                <div class="saas-donut-number">{{ number_format($feedbackStatuses['total']) }}</div>
                                <div class="saas-donut-label">Total Feedbacks</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-7">
                        <div class="d-flex flex-column gap-1">
                            @foreach($feedbackStatuses['items'] as $fs)
                                <div class="saas-legend-item">
                                    <span class="saas-legend-dot" style="background: {{ $fs['color'] }};"></span>
                                    <span class="saas-legend-name">{{ $fs['label'] }}</span>
                                    <span class="saas-legend-pct">{{ $fs['percent'] }}%</span>
                                    <span class="saas-legend-cnt">{{ $fs['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Operations Footnote / Quick Queue Access -->
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-white rounded-3 border gap-2 shadow-xs">
        <div class="d-flex align-items-center gap-2 small">
            <span class="badge bg-primary text-white px-2 py-1">Operations</span>
            <span class="text-secondary">Current Duty Shift: <strong>{{ $currentShift ? $currentShift->name : 'Off Shift' }}</strong> ({{ $stats['today_total'] }} visitors logged today)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shift_queue') }}" class="btn btn-sm btn-outline-primary px-3 fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Today's Live Shift Queue
            </a>
            <a href="{{ route('visits.create') }}" class="btn btn-sm btn-primary px-3 fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Register New Visitor
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dual-Line Feedback Trends Chart
    const trendsCtx = document.getElementById('feedbackTrendsChart');
    if (trendsCtx) {
        const ctx2d = trendsCtx.getContext('2d');
        
        // Soft Blue Gradient
        const blueGrad = ctx2d.createLinearGradient(0, 0, 0, 240);
        blueGrad.addColorStop(0, 'rgba(37, 99, 235, 0.18)');
        blueGrad.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        // Soft Green Gradient
        const greenGrad = ctx2d.createLinearGradient(0, 0, 0, 240);
        greenGrad.addColorStop(0, 'rgba(16, 185, 129, 0.18)');
        greenGrad.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        const trendsData = @json($trendsData);

        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.labels,
                datasets: [
                    {
                        label: 'Total Feedbacks',
                        data: trendsData.total,
                        borderColor: '#2563eb',
                        backgroundColor: blueGrad,
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Positive Feedbacks',
                        data: trendsData.positive,
                        borderColor: '#10b981',
                        backgroundColor: greenGrad,
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
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
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11, weight: '500' },
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f8fafc'
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    }

    // Common Donut Options
    const commonDonutOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '76%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 8,
                cornerRadius: 8
            }
        }
    };

    // 2. Feedback Rating Distribution Donut
    const ratingDonutCtx = document.getElementById('ratingDistributionDonut');
    if (ratingDonutCtx) {
        const ratingItems = @json($ratingDist['items']);
        new Chart(ratingDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ratingItems.map(i => i.label),
                datasets: [{
                    data: ratingItems.map(i => i.count),
                    backgroundColor: ratingItems.map(i => i.color),
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: commonDonutOptions
        });
    }

    // 3. Visitor Type Donut
    const visitorTypeCtx = document.getElementById('visitorTypeDonut');
    if (visitorTypeCtx) {
        const vtItems = @json($visitorTypes['items']);
        new Chart(visitorTypeCtx, {
            type: 'doughnut',
            data: {
                labels: vtItems.map(i => i.label),
                datasets: [{
                    data: vtItems.map(i => i.count),
                    backgroundColor: vtItems.map(i => i.color),
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: commonDonutOptions
        });
    }

    // 4. Feedback Status Donut
    const feedbackStatusCtx = document.getElementById('feedbackStatusDonut');
    if (feedbackStatusCtx) {
        const fsItems = @json($feedbackStatuses['items']);
        new Chart(feedbackStatusCtx, {
            type: 'doughnut',
            data: {
                labels: fsItems.map(i => i.label),
                datasets: [{
                    data: fsItems.map(i => i.count),
                    backgroundColor: fsItems.map(i => i.color),
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: commonDonutOptions
        });
    }

    // Interactive date filter label helper
    window.setDateRange = function(range) {
        const labelEl = document.getElementById('currentDateRangeLabel');
        if (!labelEl) return;
        const now = new Date();
        if (range === 'today') {
            labelEl.textContent = now.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        } else if (range === '7days') {
            const past7 = new Date();
            past7.setDate(now.getDate() - 7);
            labelEl.textContent = past7.toLocaleDateString('en-US', { day: '2-digit', month: 'short' }) + ' - ' + now.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        } else if (range === 'month') {
            labelEl.textContent = '01 ' + now.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) + ' - ' + now.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        } else if (range === '30days') {
            const past30 = new Date();
            past30.setDate(now.getDate() - 30);
            labelEl.textContent = past30.toLocaleDateString('en-US', { day: '2-digit', month: 'short' }) + ' - ' + now.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    };
});
</script>
@endpush
