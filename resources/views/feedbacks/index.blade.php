@extends('layouts.app')
@section('title', 'Feedback Submissions')
@section('page_title', 'Visitor Feedback Records')
@section('page_subtitle', 'Audit all visitor tour submissions, satisfaction scores, and qualitative suggestions')

@section('summary_cards')
@php
    $totalFeedbacks = $tabCounts['all'] ?? 0;
    $fiveStar = $tabCounts['5_star'] ?? 0;
    $fourStar = $tabCounts['4_star'] ?? 0;
    $critical = $tabCounts['critical'] ?? 0;
@endphp
<div class="row g-3">
    <!-- 1. Total Feedback -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-feedbacks.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalFeedbacks) }}</div>
                        <div class="saas-stat-label">Total feedback submissions</div>
                    </div>
                </div>
                <span class="saas-stat-badge cyan">Surveyed</span>
            </div>
        </div>
    </div>

    <!-- 2. 5-Star Ratings -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/positive-feedbacks.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($fiveStar) }}</div>
                        <div class="saas-stat-label">5-Star exceptional reviews</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Top Tier</span>
            </div>
        </div>
    </div>

    <!-- 3. 4-Star Ratings -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/average-rating.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($fourStar) }}</div>
                        <div class="saas-stat-label">4-Star positive reviews</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Good</span>
            </div>
        </div>
    </div>

    <!-- 4. Critical Ratings -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/pending-actions.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($critical) }}</div>
                        <div class="saas-stat-label">≤ 3-Star feedback actions</div>
                    </div>
                </div>
                <span class="saas-stat-badge rose">Attention</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <div class="saas-filter-pills">
        <a href="{{ route('feedbacks.index', array_merge(request()->except(['tier', 'page']))) }}"
           class="saas-pill-btn {{ ($currentTier ?? 'all') === 'all' ? 'active' : '' }}">
            <span>All feedback</span>
            <span class="pill-counter">{{ $tabCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => '5_star'])) }}"
           class="saas-pill-btn {{ ($currentTier ?? '') === '5_star' ? 'active' : '' }}">
            <span>5 Stars (Top)</span>
            <span class="pill-counter">{{ $tabCounts['5_star'] ?? 0 }}</span>
        </a>
        <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => '4_star'])) }}"
           class="saas-pill-btn {{ ($currentTier ?? '') === '4_star' ? 'active' : '' }}">
            <span>4 Stars (Good)</span>
            <span class="pill-counter">{{ $tabCounts['4_star'] ?? 0 }}</span>
        </a>
        <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => 'critical'])) }}"
           class="saas-pill-btn {{ ($currentTier ?? '') === 'critical' ? 'active' : '' }}">
            <span>≤ 3 Stars (Attention)</span>
            <span class="pill-counter">{{ $tabCounts['critical'] ?? 0 }}</span>
        </a>
    </div>

    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('feedbacks.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="feedbackFilterForm">
            @if(request('tier'))
                <input type="hidden" name="tier" value="{{ request('tier') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <x-custom-select 
                name="plant_id" 
                :value="request('plant_id')" 
                placeholder="All Plant Units" 
                search-placeholder="Search plant..." 
                icon="bi-buildings" 
                min-width="170px" 
                :options="$plants->map(fn($p) => ['value' => $p->id, 'label' => $p->code . ' — ' . $p->name])" 
                auto-submit
            />

            <x-custom-select 
                name="organizer_id" 
                :value="request('organizer_id')" 
                placeholder="All Staff" 
                search-placeholder="Search staff..." 
                icon="bi-person-badge" 
                min-width="160px" 
                :options="$organizers->map(fn($o) => ['value' => $o->id, 'label' => $o->name])" 
                auto-submit
            />

            <x-custom-select 
                name="rating" 
                :value="request('rating')" 
                placeholder="Exact Star Rating" 
                search-placeholder="Search rating..." 
                icon="bi-star-fill" 
                min-width="160px" 
                :options="[
                    ['value' => '5', 'label' => '5 ★ (High / Excellent)'],
                    ['value' => '4', 'label' => '4 ★ (Good)'],
                    ['value' => '3', 'label' => '3 ★ (Attention / Neutral)'],
                    ['value' => '2', 'label' => '2 ★ (Poor)'],
                    ['value' => '1', 'label' => '1 ★ (Critical)'],
                ]" 
                auto-submit
            />

            <x-date-range-picker 
                from-name="from" 
                to-name="to" 
                :from-value="request('from')" 
                :to-value="request('to')" 
                placeholder="Date range" 
                title="Filter by Submission Date" 
                auto-submit
            />

            <button type="submit" class="saas-btn-filter" title="Apply Filters">
                <i class="bi bi-funnel-fill"></i>
                <span class="d-none d-sm-inline">Filter</span>
            </button>

            @if(request()->hasAny(['plant_id', 'organizer_id', 'rating', 'from', 'to', 'tier']))
                <a href="{{ route('feedbacks.index') }}" class="saas-btn-filter text-danger" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Feedback Table Card -->
<div class="saas-table-card">


    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 140px;">SUBMITTED TIME</th>
                    <th>VISITOR & COMPANY</th>
                    <th>PLANT FACILITY</th>
                    <th>ASSIGNED STAFF</th>
                    <th>OVERALL RATING</th>
                    <th style="width: 30%;">VISITOR COMMENTS</th>
                    <th class="text-end">ACTION</th>
                </tr>
            </thead>
            <tbody>
            @forelse($feedbacks as $f)
                <tr>
                    <td style="white-space: nowrap;">
                        <div class="small fw-bold text-dark">{{ $f->submitted_at->format('d M Y') }}</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $f->submitted_at->format('H:i A') }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark">{{ $f->visit->visitor_name }}</span>
                            @if($f->visit?->visitor_code)
                                <span class="saas-code-badge">
                                    {{ $f->visit->visitor_code }}
                                </span>
                            @endif
                        </div>
                        @if($f->visit->visitor_company)
                            <div class="small text-muted mt-1">
                                <i class="bi bi-building me-1"></i>{{ $f->visit->visitor_company }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($f->plant)
                            <span class="saas-code-badge" title="{{ $f->plant->name }}">
                                <i class="bi bi-buildings me-1"></i>{{ $f->plant->code }}
                            </span>
                            <div class="small text-muted text-truncate mt-1" style="max-width: 160px; font-size: 0.72rem;">{{ $f->plant->name }}</div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-chip" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                {{ strtoupper(substr($f->organizer->name, 0, 2)) }}
                            </span>
                            <span class="fw-semibold text-dark">{{ $f->organizer->name }}</span>
                        </div>
                    </td>
                    <td>
                        @include('partials.rating', ['value' => $f->overall_rating])
                    </td>
                    <td>
                        @if($f->comments)
                            <div class="p-2 rounded bg-light small text-slate-700 text-truncate" style="max-width: 360px;" title="{{ $f->comments }}">
                                “{{ $f->comments }}”
                            </div>
                        @else
                            <span class="text-muted small fst-italic">No additional remarks</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('feedbacks.show', $f) }}" class="btn-action-edit-pill text-decoration-none">
                            <i class="bi bi-eye"></i> Details
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-chat-left-quote fs-2 d-block mb-2 text-slate-300"></i>
                        No feedback submissions found matching the criteria.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing <b>{{ $feedbacks->firstItem() ?? 0 }}</b> to <b>{{ $feedbacks->lastItem() ?? 0 }}</b> of <b>{{ $feedbacks->total() }}</b> reviews ({{ $feedbacks->perPage() }} per page)@if($feedbacks->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $feedbacks->links() }}
        </div>
    </div>
</div>
@endsection
