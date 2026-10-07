@extends('layouts.app')
@section('title', 'Plant Visitors Log')
@section('page_title', 'Plant Visitor Directory')
@section('page_subtitle', 'Browse all plant visitors, assigned shift operations, and feedback statuses')

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('visits.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>All Visitors</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'completed'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'completed' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Feedback Completed</span>
                <span class="filter-tab-badge">{{ $tabCounts['completed'] ?? 0 }}</span>
            </a>
            <a href="{{ route('visits.index', array_merge(request()->except(['page']), ['tab' => 'pending'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'pending' ? 'active' : '' }}">
                <i class="bi bi-clock-history text-amber-500"></i>
                <span>Awaiting Feedback</span>
                <span class="filter-tab-badge">{{ $tabCounts['pending'] ?? 0 }}</span>
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
        <form method="GET" action="{{ route('visits.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search visitor name, company, mobile..." 
                :suggestions="$visitSuggestions ?? []"
                header-title="Visitor Directory Suggestions"
            />

            <x-custom-select 
                name="shift_id" 
                :value="request('shift_id')" 
                placeholder="All Shifts" 
                search-placeholder="Search shift..." 
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

            @if(request()->hasAny(['q', 'shift_id', 'organizer_id', 'from', 'to', 'tab']))
                <a href="{{ route('visits.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Visitors Table Card -->
<div class="card-modern">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="card-title">
            <i class="bi bi-person-badge-fill text-primary"></i>
            <span>Recorded Plant Visits</span>
            @if(!empty($isTodayFilter))
                <span class="badge bg-primary text-white ms-2 fs-xs">Today's Visits</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('visits.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success" title="Poll & Sync new visitors from 3rd Party Gate/ERP API">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync 3rd Party
                </button>
            </form>
            <a href="{{ route('visits.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Register Visitor
            </a>
            <span class="badge-modern badge-slate">{{ $visits->total() }} Visits Total</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Visit Date</th>
                    <th>Shift</th>
                    <th>Visitor Information & ID</th>
                    <th>Assigned Staff</th>
                    <th>Purpose of Visit</th>
                    <th class="text-end">Feedback Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($visits as $v)
                <tr>
                    <td style="white-space: nowrap;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 rounded bg-light text-center" style="min-width: 48px; border: 1px solid #e2e8f0;">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">{{ $v->visit_date->format('M') }}</div>
                                <div class="fw-bold text-dark fs-6" style="line-height: 1;">{{ $v->visit_date->format('d') }}</div>
                            </div>
                            <span class="small text-muted">{{ $v->visit_date->format('Y') }}</span>
                        </div>
                    </td>
                    <td>
                        @if($v->shift)
                            @php
                                $shiftBadgeColor = match($v->shift->code) {
                                    'SHIFT-A' => 'badge-indigo',
                                    'SHIFT-B' => 'badge-amber',
                                    'SHIFT-C' => 'badge-purple',
                                    default => 'badge-slate'
                                };
                            @endphp
                            <span class="badge-modern {{ $shiftBadgeColor }}">
                                <i class="bi bi-clock"></i> {{ $v->shift->name }}
                            </span>
                            <div class="text-muted small mt-1" style="font-size: 0.72rem;">{{ $v->shift->formatted_24h_range }}</div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6">{{ $v->visitor_name }}</span>
                            @if($v->visitor_code)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0" style="font-size: 0.72rem;">
                                    <i class="bi bi-qr-code me-1"></i> {{ $v->visitor_code }}
                                </span>
                            @endif
                        </div>
                        @if($v->visitor_designation)
                            <div class="small text-secondary fw-semibold">{{ $v->visitor_designation }}</div>
                        @endif
                        <div class="small text-muted d-flex align-items-center gap-2 mt-1">
                            @if($v->visitor_company)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1"></i> {{ $v->visitor_company }}
                                </span>
                            @endif
                            @if($v->visitor_mobile)
                                <span><i class="bi bi-telephone text-muted"></i> {{ $v->visitor_mobile }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($v->organizer)
                            <div class="d-flex align-items-center">
                                <span class="user-avatar-chip" style="width: 28px; height: 28px; font-size: 0.72rem;">
                                    {{ strtoupper(substr($v->organizer->name, 0, 2)) }}
                                </span>
                                <div>
                                    <span class="fw-semibold text-dark">{{ $v->organizer->name }}</span>
                                    @if($v->organizer->department)
                                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $v->organizer->department }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="badge bg-light text-muted border">
                                <i class="bi bi-person-dash"></i> Unassigned
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($v->purpose)
                            <span class="badge-modern badge-slate">{{ $v->purpose }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($v->feedback)
                            <div class="d-inline-flex align-items-center gap-2">
                                @include('partials.rating', ['value' => $v->feedback->overall_rating])
                                <a href="{{ route('feedbacks.show', $v->feedback) }}" class="btn-action-icon view" title="View feedback submission">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 0.72rem;">
                                    <i class="bi bi-hourglass-split text-warning"></i> Pending
                                </span>
                                <a href="{{ route('visits.feedback.create', $v) }}" class="btn btn-sm btn-warning text-dark fw-bold py-1 px-2" style="font-size: 0.75rem;" title="Submit feedback on behalf of visitor">
                                    <i class="bi bi-pencil-square me-1"></i> Submit on Behalf
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-slate-300"></i>
                        No visit records match the selected filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $visits->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $visits->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $visits->total() }}</span> visits (10 per page)
    </div>
    <div>
        {{ $visits->links() }}
    </div>
</div>
@endsection
