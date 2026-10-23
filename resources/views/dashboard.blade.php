@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', "Welcome back! Here's an overview of your visitor feedback.")

@section('content')
<div class="saas-dashboard-container">

    <!-- 1. Top 5 SaaS Metric Cards Row -->
    <div class="saas-metric-grid">
        <!-- 1. Total Visitors -->
        <div class="saas-metric-card">
            <img src="/icons/total-visitors.svg" width="48" height="48" alt="">
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
            <img src="/icons/total-feedbacks.svg" width="48" height="48" alt="">
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
            <img src="/icons/average-rating.svg" width="48" height="48" alt="">
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
            <img src="/icons/positive-feedbacks.svg" width="48" height="48" alt="">
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
            <img src="/icons/pending-actions.svg" width="48" height="48" alt="">
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
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium" title="Filtered period">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>{{ $rangeLabel }}
                    </span>
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

    <!-- 3. Bottom Grid: Feedback by Category, Top Departments, Recent Feedbacks -->
    <div class="row g-3 mb-4">
        <!-- 1. Feedback by Category -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <div>
                        <h5 class="saas-panel-title">Feedback by Category</h5>
                        <div class="text-muted small" style="font-size: 0.72rem;">Technical Centre Facilities (Section 4)</div>
                    </div>
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium" title="Questionnaire Section 4 Facilities Rating">
                        <i class="bi bi-star-fill text-warning me-1"></i>Rating 1-5
                    </span>
                </div>
                <div class="d-flex flex-column gap-1 my-auto">
                    @forelse($categoryFeedback as $cat)
                        <div class="saas-progress-row">
                            <span class="saas-progress-name" title="{{ $cat['name'] }}">{{ $cat['name'] }}</span>
                            <div class="saas-progress-track">
                                <div class="saas-progress-bar" style="width: {{ $cat['percent'] }}%; background: {{ $cat['color'] }};"></div>
                            </div>
                            <div class="d-flex align-items-center gap-1 justify-content-end" style="min-width: 45px;">
                                @if(isset($cat['avg']) && $cat['avg'] > 0)
                                    <span class="fw-bold text-dark small" style="font-size: 0.78rem;">{{ number_format($cat['avg'], 1) }}★</span>
                                    <span class="text-muted" style="font-size: 0.68rem;">({{ $cat['count'] }})</span>
                                @else
                                    <span class="saas-progress-val">0</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No facility rating questions configured in Section 4.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 2. Top Departments Visited -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <div>
                        <h5 class="saas-panel-title">Top Departments Visited</h5>
                        <div class="text-muted small" style="font-size: 0.72rem;">Visitor volume by Plant Department</div>
                    </div>
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium" title="Visits by Department for {{ $rangeLabel }}">
                        <i class="bi bi-diagram-3 me-1 text-primary"></i>{{ $rangeLabel }}
                    </span>
                </div>
                <div class="d-flex flex-column gap-1 my-auto">
                    @forelse($topLocations as $loc)
                        <div class="saas-rank-row">
                            <span class="saas-rank-num">{{ $loc['rank'] }}</span>
                            <span class="saas-rank-name" title="{{ $loc['name'] }}">{{ $loc['name'] }}</span>
                            <div class="saas-rank-track">
                                <div class="saas-progress-bar" style="width: {{ $loc['percent'] }}%; background: {{ $loc['color'] }};"></div>
                            </div>
                            <span class="saas-rank-pct">{{ $loc['percent'] }}%</span>
                            <span class="saas-rank-cnt">{{ $loc['count'] }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No department visits recorded in this period.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Recent Feedbacks -->
        <div class="col-12 col-xl-4">
            <div class="saas-panel-card">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Recent Feedbacks</h5>
                    <a href="{{ route('feedbacks.index') }}" class="small fw-semibold text-primary text-decoration-none">
                        View All <i class="bi bi-arrow-right"></i>
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
                                    <span class="saas-feedback-time" title="{{ $rf['full_date'] ?? '' }}">{{ $rf['date_time'] }}</span>
                                    <span class="saas-feedback-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $rf['rating'] ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                        @endfor
                                    </span>
                                    
                                </div>
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
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium">
                        <i class="bi bi-pie-chart me-1 text-primary"></i>{{ $rangeLabel }}
                    </span>
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
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium">
                        <i class="bi bi-check2-circle me-1 text-primary"></i>{{ $rangeLabel }}
                    </span>
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
            <span class="text-secondary">Today's Activity: <strong><span id="footnoteTodayVisitors">{{ $stats['today_total'] }}</span> visitors</strong> logged today</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shift_queue') }}" class="btn btn-sm btn-outline-primary px-3 fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Today's Live Visitor Queue
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
    // 1. Dual-Line Feedback Trends Chart with dynamic switching (30, 14, 7 days)
    const trendsCtx = document.getElementById('feedbackTrendsChart');
    let feedbackTrendsChartInstance = null;
    const allTrendsData = @json($allTrends ?? ['30' => $trendsData, '14' => $trendsData, '7' => $trendsData]);

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

        const initialTrends = allTrendsData['30'] || @json($trendsData);

        feedbackTrendsChartInstance = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: initialTrends.labels,
                datasets: [
                    {
                        label: 'Total Feedbacks',
                        data: initialTrends.total,
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
                        data: initialTrends.positive,
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
                            precision: 0,
                            font: { size: 11 },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    }

    // Dynamic Period Switcher for Trends Chart
    window.updateTrendsPeriod = function(period) {
        if (!feedbackTrendsChartInstance || !allTrendsData[period]) return;
        const target = allTrendsData[period];
        feedbackTrendsChartInstance.data.labels = target.labels;
        feedbackTrendsChartInstance.data.datasets[0].data = target.total;
        feedbackTrendsChartInstance.data.datasets[1].data = target.positive;
        feedbackTrendsChartInstance.update();
    };

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
        const hasRatingData = ratingItems.some(i => i.count > 0);
        new Chart(ratingDonutCtx, {
            type: 'doughnut',
            data: {
                labels: hasRatingData ? ratingItems.map(i => i.label) : ['No Feedbacks Yet'],
                datasets: [{
                    data: hasRatingData ? ratingItems.map(i => i.count) : [1],
                    backgroundColor: hasRatingData ? ratingItems.map(i => i.color) : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: hasRatingData ? 3 : 0
                }]
            },
            options: {
                ...commonDonutOptions,
                plugins: {
                    ...commonDonutOptions.plugins,
                    tooltip: {
                        enabled: hasRatingData
                    }
                }
            }
        });
    }

    // 3. Visitor Type Donut
    const visitorTypeCtx = document.getElementById('visitorTypeDonut');
    if (visitorTypeCtx) {
        const vtItems = @json($visitorTypes['items']);
        const hasVtData = vtItems.some(i => i.count > 0);
        new Chart(visitorTypeCtx, {
            type: 'doughnut',
            data: {
                labels: hasVtData ? vtItems.map(i => i.label) : ['No Visitors Recorded'],
                datasets: [{
                    data: hasVtData ? vtItems.map(i => i.count) : [1],
                    backgroundColor: hasVtData ? vtItems.map(i => i.color) : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: hasVtData ? 3 : 0
                }]
            },
            options: {
                ...commonDonutOptions,
                plugins: {
                    ...commonDonutOptions.plugins,
                    tooltip: {
                        enabled: hasVtData
                    }
                }
            }
        });
    }

    // 4. Feedback Status Donut
    const feedbackStatusCtx = document.getElementById('feedbackStatusDonut');
    if (feedbackStatusCtx) {
        const fsItems = @json($feedbackStatuses['items']);
        const hasFsData = fsItems.some(i => i.count > 0);
        new Chart(feedbackStatusCtx, {
            type: 'doughnut',
            data: {
                labels: hasFsData ? fsItems.map(i => i.label) : ['No Records'],
                datasets: [{
                    data: hasFsData ? fsItems.map(i => i.count) : [1],
                    backgroundColor: hasFsData ? fsItems.map(i => i.color) : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: hasFsData ? 3 : 0
                }]
            },
            options: {
                ...commonDonutOptions,
                plugins: {
                    ...commonDonutOptions.plugins,
                    tooltip: {
                        enabled: hasFsData
                    }
                }
            }
        });
    }

    // 5. Live Feed Real-Time Background Heartbeat (every 30s)
    setInterval(function() {
        fetch('{{ route("dashboard.live_feed") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data || !data.success) return;
            const footnoteEl = document.getElementById('footnoteTodayVisitors');
            if (footnoteEl && data.counts && data.counts.all !== undefined) {
                footnoteEl.textContent = data.counts.all;
            }
        })
        .catch(err => {
            // Idle background poll
        });
    }, 30000);
});
</script>
@endpush
