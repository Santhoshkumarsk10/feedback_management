@extends('layouts.app')
@section('title', 'Feedback Submissions')
@section('page_title', 'Visitor Feedback Records')
@section('page_subtitle', 'Audit all visitor tour submissions, satisfaction scores, and qualitative suggestions')

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('feedbacks.index', array_merge(request()->except(['tier', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTier ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-chat-heart-fill"></i>
                <span>All Feedback</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => '5_star'])) }}"
               class="filter-tab-btn {{ ($currentTier ?? '') === '5_star' ? 'active' : '' }}">
                <i class="bi bi-star-fill text-warning"></i>
                <span>5 Stars (Top)</span>
                <span class="filter-tab-badge">{{ $tabCounts['5_star'] ?? 0 }}</span>
            </a>
            <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => '4_star'])) }}"
               class="filter-tab-btn {{ ($currentTier ?? '') === '4_star' ? 'active' : '' }}">
                <i class="bi bi-star-half text-warning"></i>
                <span>4 Stars (Good)</span>
                <span class="filter-tab-badge">{{ $tabCounts['4_star'] ?? 0 }}</span>
            </a>
            <a href="{{ route('feedbacks.index', array_merge(request()->except(['page']), ['tier' => 'critical'])) }}"
               class="filter-tab-btn {{ ($currentTier ?? '') === 'critical' ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <span>≤ 3 Stars (Attention)</span>
                <span class="filter-tab-badge">{{ $tabCounts['critical'] ?? 0 }}</span>
            </a>
        </div>

        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">
                <i class="bi bi-list-check"></i> 10 per page
            </span>
        </div>
    </div>

    <!-- Filter Controls Bar -->
    <div class="filter-controls-body">
        <form method="GET" action="{{ route('feedbacks.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="feedbackFilterForm" onsubmit="const hasVal = Array.from(this.querySelectorAll('input:not([type=hidden]), select')).some(el => el.value && el.value.trim() !== ''); const urlParams = new URLSearchParams(window.location.search); urlParams.delete('tier'); urlParams.delete('page'); if(!hasVal && !urlParams.toString()){ event.preventDefault(); const firstTrigger = this.querySelector('button.custom-select-trigger, input:not([type=hidden])'); if(firstTrigger) { firstTrigger.focus(); firstTrigger.classList.add('border-danger'); setTimeout(() => firstTrigger.classList.remove('border-danger'), 2000); } }">
            @if(request('tier'))
                <input type="hidden" name="tier" value="{{ request('tier') }}">
            @endif

            <x-custom-select 
                name="shift_id" 
                :value="request('shift_id')" 
                placeholder="All Shifts" 
                search-placeholder="Search shifts..." 
                icon="bi-clock-history" 
                min-width="170px" 
                :options="collect([['value' => '', 'label' => 'All Shifts']])->concat($shifts->map(fn($s) => ['value' => $s->id, 'label' => $s->name . ' (' . $s->start_time_short . '–' . $s->end_time_short . ')']))" 
                auto-submit
            />

            <x-custom-select 
                name="organizer_id" 
                :value="request('organizer_id')" 
                placeholder="All Staff" 
                search-placeholder="Search staff..." 
                icon="bi-person-badge" 
                min-width="180px" 
                :options="$organizers->map(fn($o) => ['value' => $o->id, 'label' => $o->name])" 
                auto-submit
            />

            <x-custom-select 
                name="rating" 
                :value="request('rating')" 
                placeholder="Exact Star Rating" 
                search-placeholder="Search rating..." 
                icon="bi-star-fill" 
                min-width="170px" 
                :options="[
                    ['value' => '5', 'label' => '5 ★ (High / Excellent)'],
                    ['value' => '4', 'label' => '4 ★ (Good)'],
                    ['value' => '3', 'label' => '3 ★ (Attention / Neutral)'],
                    ['value' => '2', 'label' => '2 ★ (Poor)'],
                    ['value' => '1', 'label' => '1 ★ (Critical)'],
                ]" 
                auto-submit
            />

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">From:</span>
                <x-date-picker name="from" :value="request('from')" placeholder="dd/mm/yyyy" title="From Date" />
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted">To:</span>
                <x-date-picker name="to" :value="request('to')" placeholder="dd/mm/yyyy" title="To Date" />
            </div>

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['organizer_id', 'shift_id', 'rating', 'from', 'to', 'tier']))
                <a href="{{ route('feedbacks.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Feedback Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-chat-heart-fill text-success"></i>
            <span>Recorded Visitor Reviews</span>
        </div>
        <span class="badge-modern badge-slate">{{ $feedbacks->total() }} Submissions</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Submitted Time</th>
                    <th>Visitor & Company</th>
                    <th>Assigned Organizer</th>
                    <th>Overall Rating</th>
                    <th style="width: 35%;">Visitor Comments</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($feedbacks as $f)
                <tr>
                    <td style="white-space: nowrap;">
                        <div class="small fw-bold text-dark">{{ $f->submitted_at->format('d M Y') }}</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $f->submitted_at->format('H:i A') }}</div>
                        @if($f->visit?->shift)
                            @php
                                $shiftBadgeColor = match($f->visit->shift->code) {
                                    'SHIFT-A' => 'badge-indigo',
                                    'SHIFT-B' => 'badge-amber',
                                    'SHIFT-C' => 'badge-purple',
                                    default => 'badge-slate'
                                };
                            @endphp
                            <span class="badge-modern {{ $shiftBadgeColor }} mt-1" style="font-size: 0.68rem;" title="{{ $f->visit->shift->formatted_24h_range }}">
                                <i class="bi bi-clock"></i> {{ $f->visit->shift->name }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark">{{ $f->visit->visitor_name }}</span>
                            @if($f->visit?->visitor_code)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1 py-0" style="font-size: 0.68rem;">
                                    {{ $f->visit->visitor_code }}
                                </span>
                            @endif
                        </div>
                        @if($f->visit->visitor_company)
                            <div class="small text-muted">
                                <i class="bi bi-building"></i> {{ $f->visit->visitor_company }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
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
                        <a href="{{ route('feedbacks.show', $f) }}" class="btn-modern-secondary btn-sm py-1 px-3">
                            <span>Details</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-chat-left-quote fs-2 d-block mb-2 text-slate-300"></i>
                        No feedback submissions found matching the criteria.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $feedbacks->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $feedbacks->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $feedbacks->total() }}</span> reviews (10 per page)
    </div>
    <div>
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection
