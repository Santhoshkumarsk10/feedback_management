@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', "Welcome back! Here's an overview of your visitor feedback.")

@section('content')
<div class="saas-dashboard-container">

    <!-- Top 5 Metric Cards Row (Matching Reference Design) -->
    <div class="saas-metric-grid">
        <!-- 1. Total Visitors -->
        <div class="saas-metric-card">
            <div class="saas-icon-box blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="saas-metric-title">Total Visitors</div>
                <div class="saas-metric-value">{{ $saasMetrics['total_visitors']['value'] }}</div>
                <div class="saas-trend-badge {{ $saasMetrics['total_visitors']['dir'] === 'up' ? 'green' : ($saasMetrics['total_visitors']['dir'] === 'down' ? 'rose' : 'gray') }}">
                    @if($saasMetrics['total_visitors']['dir'] === 'up')
                        <span>&#8599;</span>
                    @elseif($saasMetrics['total_visitors']['dir'] === 'down')
                        <span>&#8600;</span>
                    @else
                        <span>&#8599;</span>
                    @endif
                    <span>{{ ltrim($saasMetrics['total_visitors']['growth'], '+-') }}</span>
                    <span class="saas-trend-sub">vs. last period</span>
                </div>
            </div>
        </div>

        <!-- 2. Total Feedbacks -->
        <div class="saas-metric-card">
            <div class="saas-icon-box green">
                <i class="bi bi-chat-left-dots"></i>
            </div>
            <div>
                <div class="saas-metric-title">Total Feedbacks</div>
                <div class="saas-metric-value">{{ $saasMetrics['total_feedbacks']['value'] }}</div>
                <div class="saas-trend-badge {{ $saasMetrics['total_feedbacks']['dir'] === 'down' ? 'rose' : 'green' }}">
                    @if($saasMetrics['total_feedbacks']['dir'] === 'down')
                        <span>&#8600;</span>
                    @else
                        <span>&#8599;</span>
                    @endif
                    <span>{{ ltrim($saasMetrics['total_feedbacks']['growth'], '+-') }}</span>
                    <span class="saas-trend-sub">vs. last period</span>
                </div>
            </div>
        </div>

        <!-- 3. Average Rating -->
        <div class="saas-metric-card">
            <div class="saas-icon-box amber">
                <i class="bi bi-star"></i>
            </div>
            <div>
                <div class="saas-metric-title">Average Rating</div>
                <div class="saas-metric-value">{{ $saasMetrics['average_rating']['value'] }}</div>
                <div class="saas-trend-badge {{ $saasMetrics['average_rating']['dir'] === 'down' ? 'rose' : 'green' }}">
                    @if($saasMetrics['average_rating']['dir'] === 'down')
                        <span>&#8600;</span>
                    @else
                        <span>&#8599;</span>
                    @endif
                    <span>{{ ltrim($saasMetrics['average_rating']['growth'], '+-') }}</span>
                    <span class="saas-trend-sub">vs. last period</span>
                </div>
            </div>
        </div>

        <!-- 4. Positive Feedbacks -->
        <div class="saas-metric-card">
            <div class="saas-icon-box rose">
                <i class="bi bi-hand-thumbs-up"></i>
            </div>
            <div>
                <div class="saas-metric-title">Positive Feedbacks</div>
                <div class="saas-metric-value">{{ $saasMetrics['positive_feedbacks']['value'] }}</div>
                <div class="saas-trend-badge green">
                    <span>&#8599;</span>
                    <span>{{ ltrim($saasMetrics['positive_feedbacks']['growth'], '+-') }}</span>
                    <span class="saas-trend-sub">vs. last period</span>
                </div>
            </div>
        </div>

        <!-- 5. Pending Actions -->
        <div class="saas-metric-card">
            <div class="saas-icon-box purple">
                <i class="bi bi-card-checklist"></i>
            </div>
            <div>
                <div class="saas-metric-title">Pending Actions</div>
                <div class="saas-metric-value">{{ $saasMetrics['pending_actions']['value'] }}</div>
                <div class="saas-trend-badge {{ $saasMetrics['pending_actions']['dir'] === 'down' ? 'green' : ($saasMetrics['pending_actions']['dir'] === 'up' ? 'rose' : 'green') }}">
                    @if($saasMetrics['pending_actions']['dir'] === 'down')
                        <span>&#8600;</span>
                    @else
                        <span>&#8599;</span>
                    @endif
                    <span>{{ ltrim($saasMetrics['pending_actions']['growth'], '+-') }}</span>
                    <span class="saas-trend-sub">vs. last period</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Dual Charts Row: Feedback Trends & Rating Distribution -->
    <div class="saas-dual-charts-grid">
        <!-- Feedback Trends (Smooth Dual-Line Area Chart) -->
        <div class="feedback-trends-col">
            <div class="saas-panel-card h-100 d-flex flex-column justify-content-between">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Trends</h5>
                    <select class="saas-select-pill" id="trendsTimeFilter" onchange="updateTrendsPeriod(this.value)">
                        <option value="30">Last 30 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="7">Last 7 Days</option>
                    </select>
                </div>
                <div style="position: relative; height: 230px; width: 100%;">
                    <div id="trendsEmptyNotice" class="d-flex flex-column align-items-center justify-content-center h-100 text-muted {{ $hasTrendsData ? 'd-none' : '' }}" style="background: rgba(248, 250, 252, 0.7); border-radius: 8px;">
                        <i class="bi bi-bar-chart-line fs-2 text-secondary opacity-50 mb-1"></i>
                        <div class="fw-semibold small text-dark">No Feedback Trends Available</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No feedback submissions recorded in the database for this timeframe.</div>
                    </div>
                    <canvas id="feedbackTrendsChart" class="{{ !$hasTrendsData ? 'd-none' : '' }}"></canvas>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-4 mt-2 pt-2">
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
        <div class="feedback-rating-col">
            <div class="saas-panel-card h-100 d-flex flex-column justify-content-between">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Rating Distribution</h5>
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium" title="Filtered period">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>{{ $rangeLabel }}
                    </span>
                </div>
                @if($ratingDist['total'] > 0)
                    <div class="row align-items-center g-3 my-auto">
                        <div class="col-12 col-sm-6 text-center">
                            <div class="saas-donut-box">
                                <canvas id="ratingDistributionDonut"></canvas>
                                <div class="saas-donut-center">
                                    <div class="saas-donut-number">{{ number_format($ratingDist['total']) }}</div>
                                    <div class="saas-donut-label">Total<br>Feedbacks</div>
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
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                        <i class="bi bi-pie-chart text-secondary opacity-50 fs-2 mb-2"></i>
                        <div class="fw-semibold small text-dark">No Rating Data Available</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No feedback ratings have been submitted for this period.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- 2. Middle Grid: Feedback by Category, Top Departments, Recent Feedbacks -->
    <div class="row g-3 mb-4">
        <!-- 1. Feedback by Category -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card h-100">
                <div class="saas-panel-header">
                    <div>
                        <h5 class="saas-panel-title">Feedback by Category</h5>
                        <div class="text-muted small" style="font-size: 0.72rem;">Technical Centre Facilities (Section 4)</div>
                    </div>
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-medium" title="Questionnaire Section 4 Facilities Rating">
                        <i class="bi bi-star-fill text-warning me-1"></i>Rating 1-5
                    </span>
                </div>
                @if($hasCategoryData)
                    <div class="d-flex flex-column gap-1 my-auto">
                        @foreach($categoryFeedback as $cat)
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
                                        <span class="saas-progress-val">0★ (0)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                        <i class="bi bi-card-checklist text-secondary opacity-50 fs-2 mb-2"></i>
                        <div class="fw-semibold small text-dark">No Category Ratings Available</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No Section 4 facility ratings recorded for this period.</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Top Departments Visited -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="saas-panel-card h-100">
                <div class="saas-panel-header">
                    <div>
                        <h5 class="saas-panel-title">Top Departments Visited</h5>
                        <div class="text-muted small" style="font-size: 0.72rem;">Visitor volume by Plant Department</div>
                    </div>
                    <div class="dropdown">
                        <button class="widget-date-pill dropdown-toggle" type="button" id="departmentsDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filter Top Departments date range">
                            <i class="bi bi-diagram-3 text-primary"></i>
                            <span id="departmentsRangeLabel">{{ $rangeLabel }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end widget-date-dropdown-menu" aria-labelledby="departmentsDropdownBtn">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Preset Ranges</div>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('departments', 'today', 'Today')">
                                <span><i class="bi bi-clock me-2 text-muted"></i>Today</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('departments', '7days', 'Last 7 Days')">
                                <span><i class="bi bi-calendar-week me-2 text-muted"></i>Last 7 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('departments', 'month', 'This Month')">
                                <span><i class="bi bi-calendar-month me-2 text-muted"></i>This Month</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('departments', '30days', 'Last 30 Days')">
                                <span><i class="bi bi-calendar3-range me-2 text-muted"></i>Last 30 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('departments', 'all', 'All Time Records')">
                                <span><i class="bi bi-database me-2 text-muted"></i>All Time</span>
                            </a>
                            <hr class="dropdown-divider my-1">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Custom Range</div>
                            <form onsubmit="applyWidgetCustomRange(event, 'departments')" class="px-2 py-1 d-flex flex-column gap-2">
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">From</label>
                                    <input type="date" class="form-control form-control-sm" id="departments-from" required style="font-size: 0.75rem;">
                                </div>
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">To</label>
                                    <input type="date" class="form-control form-control-sm" id="departments-to" required style="font-size: 0.75rem;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-1 mt-1" style="font-size: 0.75rem;">Apply Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="departmentsWidgetContainer" class="d-flex flex-column flex-grow-1 justify-content-center">
                @if($hasTopLocations)
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
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                        <i class="bi bi-building-slash text-secondary opacity-50 fs-2 mb-2"></i>
                        <div class="fw-semibold small text-dark">No Department Visits Recorded</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No plant department visits recorded in this period.</div>
                    </div>
                @endif
                </div>
            </div>
        </div>

        <!-- 3. Recent Feedbacks -->
        <div class="col-12 col-xl-4">
            <div class="saas-panel-card h-100">
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
                        <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                            <i class="bi bi-chat-square-dots text-secondary opacity-50 fs-2 mb-2"></i>
                            <div class="fw-semibold small text-dark">No Feedback Submitted Yet</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">No visitor feedbacks found in the database for this period.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Donut Summaries Row: Visitor Type & Feedback Status -->
    <div class="row g-3 mb-4">
        <!-- Visitor Type Donut -->
        <div class="col-12 col-md-6">
            <div class="saas-panel-card h-100">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Visitor Type</h5>
                    <div class="dropdown">
                        <button class="widget-date-pill dropdown-toggle" type="button" id="visitorTypeDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filter Visitor Type date range">
                            <i class="bi bi-clock text-primary"></i>
                            <span id="visitor_typeRangeLabel">{{ $rangeLabel }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end widget-date-dropdown-menu" aria-labelledby="visitorTypeDropdownBtn">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Preset Ranges</div>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('visitor_type', 'today', 'Today')">
                                <span><i class="bi bi-clock me-2 text-muted"></i>Today</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('visitor_type', '7days', 'Last 7 Days')">
                                <span><i class="bi bi-calendar-week me-2 text-muted"></i>Last 7 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('visitor_type', 'month', 'This Month')">
                                <span><i class="bi bi-calendar-month me-2 text-muted"></i>This Month</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('visitor_type', '30days', 'Last 30 Days')">
                                <span><i class="bi bi-calendar3-range me-2 text-muted"></i>Last 30 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('visitor_type', 'all', 'All Time Records')">
                                <span><i class="bi bi-database me-2 text-muted"></i>All Time</span>
                            </a>
                            <hr class="dropdown-divider my-1">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Custom Range</div>
                            <form onsubmit="applyWidgetCustomRange(event, 'visitor_type')" class="px-2 py-1 d-flex flex-column gap-2">
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">From</label>
                                    <input type="date" class="form-control form-control-sm" id="visitor_type-from" required style="font-size: 0.75rem;">
                                </div>
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">To</label>
                                    <input type="date" class="form-control form-control-sm" id="visitor_type-to" required style="font-size: 0.75rem;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-1 mt-1" style="font-size: 0.75rem;">Apply Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="visitorTypeWidgetContainer" class="d-flex flex-column flex-grow-1 justify-content-center">
                @if($visitorTypes['total'] > 0)
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
                                    <div class="saas-legend-item-with-bar">
                                        <div class="saas-legend-item">
                                            <span class="saas-legend-dot" style="background: {{ $vt['color'] }};"></span>
                                            <span class="saas-legend-name">{{ $vt['label'] }}</span>
                                            <span class="saas-legend-pct">{{ $vt['percent'] }}%</span>
                                            <span class="saas-legend-cnt">{{ $vt['count'] }}</span>
                                        </div>
                                        <div class="saas-legend-bar-track">
                                            <div class="saas-legend-bar-fill" style="width: {{ $vt['percent'] }}%; background: {{ $vt['color'] }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                        <i class="bi bi-people text-secondary opacity-50 fs-2 mb-2"></i>
                        <div class="fw-semibold small text-dark">No Visitor Records Available</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No visitors recorded in the database for this period.</div>
                    </div>
                @endif
                </div>
            </div>
        </div>

        <!-- Feedback Status Donut -->
        <div class="col-12 col-md-6">
            <div class="saas-panel-card h-100">
                <div class="saas-panel-header">
                    <h5 class="saas-panel-title">Feedback Status</h5>
                    <div class="dropdown">
                        <button class="widget-date-pill dropdown-toggle" type="button" id="feedbackStatusDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filter Feedback Status date range">
                            <i class="bi bi-check2-circle text-primary"></i>
                            <span id="feedback_statusRangeLabel">{{ $rangeLabel }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end widget-date-dropdown-menu" aria-labelledby="feedbackStatusDropdownBtn">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Preset Ranges</div>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('feedback_status', 'today', 'Today')">
                                <span><i class="bi bi-clock me-2 text-muted"></i>Today</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('feedback_status', '7days', 'Last 7 Days')">
                                <span><i class="bi bi-calendar-week me-2 text-muted"></i>Last 7 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('feedback_status', 'month', 'This Month')">
                                <span><i class="bi bi-calendar-month me-2 text-muted"></i>This Month</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('feedback_status', '30days', 'Last 30 Days')">
                                <span><i class="bi bi-calendar3-range me-2 text-muted"></i>Last 30 Days</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="switchWidgetRange('feedback_status', 'all', 'All Time Records')">
                                <span><i class="bi bi-database me-2 text-muted"></i>All Time</span>
                            </a>
                            <hr class="dropdown-divider my-1">
                            <div class="px-2 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">Custom Range</div>
                            <form onsubmit="applyWidgetCustomRange(event, 'feedback_status')" class="px-2 py-1 d-flex flex-column gap-2">
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">From</label>
                                    <input type="date" class="form-control form-control-sm" id="feedback_status-from" required style="font-size: 0.75rem;">
                                </div>
                                <div>
                                    <label class="text-muted small mb-0" style="font-size: 0.7rem;">To</label>
                                    <input type="date" class="form-control form-control-sm" id="feedback_status-to" required style="font-size: 0.75rem;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-1 mt-1" style="font-size: 0.75rem;">Apply Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="feedbackStatusWidgetContainer" class="d-flex flex-column flex-grow-1 justify-content-center">
                @if($feedbackStatuses['total'] > 0)
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
                                    <div class="saas-legend-item-with-bar">
                                        <div class="saas-legend-item">
                                            <span class="saas-legend-dot" style="background: {{ $fs['color'] }};"></span>
                                            <span class="saas-legend-name">{{ $fs['label'] }}</span>
                                            <span class="saas-legend-pct">{{ $fs['percent'] }}%</span>
                                            <span class="saas-legend-cnt">{{ $fs['count'] }}</span>
                                        </div>
                                        <div class="saas-legend-bar-track">
                                            <div class="saas-legend-bar-fill" style="width: {{ $fs['percent'] }}%; background: {{ $fs['color'] }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                        <i class="bi bi-clipboard-x text-secondary opacity-50 fs-2 mb-2"></i>
                        <div class="fw-semibold small text-dark">No Feedback Status Records</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">No feedback statuses recorded for this period in the database.</div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
window.allWidgetsData = @json($allWidgetsData ?? []);

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

let ratingDonutChartInstance = null;
let visitorTypeChartInstance = null;
let feedbackStatusChartInstance = null;
let feedbackTrendsChartInstance = null;

const commonDonutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: {
        legend: { display: false }
    },
    animation: { duration: 400 }
};

function initRatingDistributionChart(items) {
    const canvas = document.getElementById('ratingDistributionDonut');
    if (!canvas) return;
    if (ratingDonutChartInstance) {
        ratingDonutChartInstance.destroy();
        ratingDonutChartInstance = null;
    }
    const hasData = items && items.some(i => i.count > 0);
    ratingDonutChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: hasData ? items.map(i => i.label) : ['No Feedbacks Yet'],
            datasets: [{
                data: hasData ? items.map(i => i.count) : [1],
                backgroundColor: hasData ? items.map(i => i.color) : ['#e2e8f0'],
                borderWidth: 0,
                hoverOffset: hasData ? 3 : 0
            }]
        },
        options: {
            ...commonDonutOptions,
            plugins: {
                ...commonDonutOptions.plugins,
                tooltip: { enabled: hasData }
            }
        }
    });
}

function initVisitorTypeChart(items) {
    const canvas = document.getElementById('visitorTypeDonut');
    if (!canvas) return;
    if (visitorTypeChartInstance) {
        visitorTypeChartInstance.destroy();
        visitorTypeChartInstance = null;
    }
    const hasData = items && items.some(i => i.count > 0);
    visitorTypeChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: hasData ? items.map(i => i.label) : ['No Visitors Recorded'],
            datasets: [{
                data: hasData ? items.map(i => i.count) : [1],
                backgroundColor: hasData ? items.map(i => i.color) : ['#e2e8f0'],
                borderWidth: 0,
                hoverOffset: hasData ? 3 : 0
            }]
        },
        options: {
            ...commonDonutOptions,
            plugins: {
                ...commonDonutOptions.plugins,
                tooltip: { enabled: hasData }
            }
        }
    });
}

function initFeedbackStatusChart(items) {
    const canvas = document.getElementById('feedbackStatusDonut');
    if (!canvas) return;
    if (feedbackStatusChartInstance) {
        feedbackStatusChartInstance.destroy();
        feedbackStatusChartInstance = null;
    }
    const hasData = items && items.some(i => i.count > 0);
    feedbackStatusChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: hasData ? items.map(i => i.label) : ['No Records'],
            datasets: [{
                data: hasData ? items.map(i => i.count) : [1],
                backgroundColor: hasData ? items.map(i => i.color) : ['#e2e8f0'],
                borderWidth: 0,
                hoverOffset: hasData ? 3 : 0
            }]
        },
        options: {
            ...commonDonutOptions,
            plugins: {
                ...commonDonutOptions.plugins,
                tooltip: { enabled: hasData }
            }
        }
    });
}

function renderDepartments(locations, hasLocations) {
    const container = document.getElementById('departmentsWidgetContainer');
    if (!container) return;
    if (!hasLocations || !locations || locations.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                <i class="bi bi-building-slash text-secondary opacity-50 fs-2 mb-2"></i>
                <div class="fw-semibold small text-dark">No Department Visits Recorded</div>
                <div class="small text-muted" style="font-size: 0.75rem;">No plant department visits recorded in this period.</div>
            </div>
        `;
        return;
    }

    let rowsHtml = '';
    locations.forEach(loc => {
        rowsHtml += `
            <div class="saas-rank-row">
                <span class="saas-rank-num">${escapeHtml(loc.rank)}</span>
                <span class="saas-rank-name" title="${escapeHtml(loc.name)}">${escapeHtml(loc.name)}</span>
                <div class="saas-rank-track">
                    <div class="saas-progress-bar" style="width: ${loc.percent}%; background: ${escapeHtml(loc.color)};"></div>
                </div>
                <span class="saas-rank-pct">${loc.percent}%</span>
                <span class="saas-rank-cnt">${loc.count}</span>
            </div>
        `;
    });

    container.innerHTML = `<div class="d-flex flex-column gap-1 my-auto">${rowsHtml}</div>`;
}

function renderVisitorType(vtData) {
    const container = document.getElementById('visitorTypeWidgetContainer');
    if (!container) return;
    if (!vtData || !vtData.has_data || vtData.total === 0) {
        if (visitorTypeChartInstance) {
            visitorTypeChartInstance.destroy();
            visitorTypeChartInstance = null;
        }
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                <i class="bi bi-people text-secondary opacity-50 fs-2 mb-2"></i>
                <div class="fw-semibold small text-dark">No Visitor Records Available</div>
                <div class="small text-muted" style="font-size: 0.75rem;">No visitors recorded in the database for this period.</div>
            </div>
        `;
        return;
    }

    let legendHtml = '';
    (vtData.items || []).forEach(item => {
        legendHtml += `
            <div class="saas-legend-item-with-bar">
                <div class="saas-legend-item">
                    <span class="saas-legend-dot" style="background: ${escapeHtml(item.color)};"></span>
                    <span class="saas-legend-name">${escapeHtml(item.label)}</span>
                    <span class="saas-legend-pct">${item.percent}%</span>
                    <span class="saas-legend-cnt">${item.count}</span>
                </div>
                <div class="saas-legend-bar-track">
                    <div class="saas-legend-bar-fill" style="width: ${item.percent}%; background: ${escapeHtml(item.color)};"></div>
                </div>
            </div>
        `;
    });

    container.innerHTML = `
        <div class="row align-items-center g-3 my-auto">
            <div class="col-12 col-sm-5 text-center">
                <div class="saas-donut-box">
                    <canvas id="visitorTypeDonut"></canvas>
                    <div class="saas-donut-center">
                        <div class="saas-donut-number">${Number(vtData.total).toLocaleString()}</div>
                        <div class="saas-donut-label">Total Visitors</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-7">
                <div class="d-flex flex-column gap-1">
                    ${legendHtml}
                </div>
            </div>
        </div>
    `;

    initVisitorTypeChart(vtData.items);
}

function renderFeedbackStatus(fsData) {
    const container = document.getElementById('feedbackStatusWidgetContainer');
    if (!container) return;
    if (!fsData || !fsData.has_data || fsData.total === 0) {
        if (feedbackStatusChartInstance) {
            feedbackStatusChartInstance.destroy();
            feedbackStatusChartInstance = null;
        }
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center py-4 my-auto text-muted">
                <i class="bi bi-clipboard-x text-secondary opacity-50 fs-2 mb-2"></i>
                <div class="fw-semibold small text-dark">No Feedback Status Records</div>
                <div class="small text-muted" style="font-size: 0.75rem;">No feedback statuses recorded for this period in the database.</div>
            </div>
        `;
        return;
    }

    let legendHtml = '';
    (fsData.items || []).forEach(item => {
        legendHtml += `
            <div class="saas-legend-item-with-bar">
                <div class="saas-legend-item">
                    <span class="saas-legend-dot" style="background: ${escapeHtml(item.color)};"></span>
                    <span class="saas-legend-name">${escapeHtml(item.label)}</span>
                    <span class="saas-legend-pct">${item.percent}%</span>
                    <span class="saas-legend-cnt">${item.count}</span>
                </div>
                <div class="saas-legend-bar-track">
                    <div class="saas-legend-bar-fill" style="width: ${item.percent}%; background: ${escapeHtml(item.color)};"></div>
                </div>
            </div>
        `;
    });

    container.innerHTML = `
        <div class="row align-items-center g-3 my-auto">
            <div class="col-12 col-sm-5 text-center">
                <div class="saas-donut-box">
                    <canvas id="feedbackStatusDonut"></canvas>
                    <div class="saas-donut-center">
                        <div class="saas-donut-number">${Number(fsData.total).toLocaleString()}</div>
                        <div class="saas-donut-label">Total Feedbacks</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-7">
                <div class="d-flex flex-column gap-1">
                    ${legendHtml}
                </div>
            </div>
        </div>
    `;

    initFeedbackStatusChart(fsData.items);
}

window.switchWidgetRange = function(widgetKey, rangeKey, fallbackLabel) {
    closeWidgetDropdown(widgetKey);

    if (window.allWidgetsData && window.allWidgetsData[rangeKey]) {
        const d = window.allWidgetsData[rangeKey];
        const label = d.label || fallbackLabel;
        updateWidgetLabel(widgetKey, label);

        if (widgetKey === 'departments') {
            renderDepartments(d.topLocations, d.hasTopLocations);
        } else if (widgetKey === 'visitor_type') {
            renderVisitorType(d.visitorTypes);
        } else if (widgetKey === 'feedback_status') {
            renderFeedbackStatus(d.feedbackStatuses);
        }
        return;
    }

    fetchWidgetData(widgetKey, { range: rangeKey });
};

window.applyWidgetCustomRange = function(event, widgetKey) {
    event.preventDefault();
    const fromInput = document.getElementById(widgetKey + '-from');
    const toInput = document.getElementById(widgetKey + '-to');
    if (!fromInput || !toInput || !fromInput.value || !toInput.value) return;

    closeWidgetDropdown(widgetKey);
    fetchWidgetData(widgetKey, { from: fromInput.value, to: toInput.value });
};

function fetchWidgetData(widgetKey, params) {
    const url = new URL('{{ route("dashboard.widget_data") }}', window.location.origin);
    url.searchParams.set('widget', widgetKey);
    for (const [k, v] of Object.entries(params)) {
        url.searchParams.set(k, v);
    }

    const container = document.getElementById(
        widgetKey === 'departments' ? 'departmentsWidgetContainer' :
        widgetKey === 'visitor_type' ? 'visitorTypeWidgetContainer' :
        'feedbackStatusWidgetContainer'
    );
    if (container) container.style.opacity = '0.5';

    fetch(url.toString(), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(res => {
        if (container) container.style.opacity = '1';
        if (!res || !res.success) return;

        if (res.range_label) {
            updateWidgetLabel(widgetKey, res.range_label);
        }

        if (widgetKey === 'departments') {
            renderDepartments(res.topLocations, res.hasTopLocations);
        } else if (widgetKey === 'visitor_type') {
            renderVisitorType(res.visitorTypes);
        } else if (widgetKey === 'feedback_status') {
            renderFeedbackStatus(res.feedbackStatuses);
        }
    })
    .catch(err => {
        if (container) container.style.opacity = '1';
        console.error('Failed to load widget data', err);
    });
}

function updateWidgetLabel(widgetKey, label) {
    const labelEl = document.getElementById(
        widgetKey === 'departments' ? 'departmentsRangeLabel' :
        widgetKey === 'visitor_type' ? 'visitor_typeRangeLabel' :
        'feedback_statusRangeLabel'
    );
    if (labelEl) labelEl.textContent = label;
}

function closeWidgetDropdown(widgetKey) {
    const btn = document.getElementById(
        widgetKey === 'departments' ? 'departmentsDropdownBtn' :
        widgetKey === 'visitor_type' ? 'visitorTypeDropdownBtn' :
        'feedbackStatusDropdownBtn'
    );
    if (btn && window.bootstrap && window.bootstrap.Dropdown) {
        const instance = bootstrap.Dropdown.getInstance(btn);
        if (instance) instance.hide();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Dual-Line Feedback Trends Chart
    const trendsCtx = document.getElementById('feedbackTrendsChart');
    const allTrendsData = @json($allTrends ?? ['30' => $trendsData, '14' => $trendsData, '7' => $trendsData]);

    if (trendsCtx) {
        const ctx2d = trendsCtx.getContext('2d');
        const totalGrad = ctx2d.createLinearGradient(0, 0, 0, 240);
        totalGrad.addColorStop(0, 'rgba(37, 99, 235, 0.22)');
        totalGrad.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        const posGrad = ctx2d.createLinearGradient(0, 0, 0, 240);
        posGrad.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
        posGrad.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        feedbackTrendsChartInstance = new Chart(ctx2d, {
            type: 'line',
            data: {
                labels: allTrendsData['30'] ? allTrendsData['30'].labels : [],
                datasets: [
                    {
                        label: 'Total Feedbacks',
                        data: allTrendsData['30'] ? allTrendsData['30'].total : [],
                        borderColor: '#2563eb',
                        backgroundColor: totalGrad,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Positive Feedbacks',
                        data: allTrendsData['30'] ? allTrendsData['30'].positive : [],
                        borderColor: '#10b981',
                        backgroundColor: posGrad,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11 },
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }


    window.updateTrendsPeriod = function(days) {
        if (!feedbackTrendsChartInstance) return;
        const nextData = allTrendsData[days] || allTrendsData['30'];
        const emptyNotice = document.getElementById('trendsEmptyNotice');
        const chartCanvas = document.getElementById('feedbackTrendsChart');

        if (nextData && nextData.has_data) {
            if (emptyNotice) emptyNotice.classList.add('d-none');
            if (chartCanvas) chartCanvas.classList.remove('d-none');

            feedbackTrendsChartInstance.data.labels = nextData.labels;
            feedbackTrendsChartInstance.data.datasets[0].data = nextData.total;
            feedbackTrendsChartInstance.data.datasets[1].data = nextData.positive;
            feedbackTrendsChartInstance.update();
        } else {
            if (emptyNotice) emptyNotice.classList.remove('d-none');
            if (chartCanvas) chartCanvas.classList.add('d-none');
        }
    };

    // 2. Initial Donut Charts
    const initialRatingItems = @json($ratingDist['items'] ?? []);
    if (initialRatingItems.length > 0) {
        initRatingDistributionChart(initialRatingItems);
    }

    const initialVtItems = @json($visitorTypes['items'] ?? []);
    if (initialVtItems.length > 0) {
        initVisitorTypeChart(initialVtItems);
    }

    const initialFsItems = @json($feedbackStatuses['items'] ?? []);
    if (initialFsItems.length > 0) {
        initFeedbackStatusChart(initialFsItems);
    }
});
</script>
@endpush
