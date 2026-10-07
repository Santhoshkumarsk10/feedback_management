@extends('layouts.app')
@section('title', 'Shift Master Management')
@section('page_title', 'Manufacturing Shift Master')
@section('page_subtitle', 'Manage factory operating shifts, production schedules, and visitor tour time slots')

@section('topbar_actions')
    <a href="{{ route('shifts.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-clock-history"></i>
        <span>Register New Shift</span>
    </a>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('shifts.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-clock-fill"></i>
                <span>All Shifts</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('shifts.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Active Schedules</span>
                <span class="filter-tab-badge">{{ $tabCounts['active'] ?? 0 }}</span>
            </a>
            <a href="{{ route('shifts.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
                <i class="bi bi-pause-circle-fill text-muted"></i>
                <span>Inactive Shifts</span>
                <span class="filter-tab-badge">{{ $tabCounts['inactive'] ?? 0 }}</span>
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
        <form method="GET" action="{{ route('shifts.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="shiftSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search shift code, shift name, or notes..." 
                :suggestions="$allShiftSuggestions ?? []"
                header-title="Shift Directory Suggestions"
            />

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('shifts.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
        @error('q')
            <div class="text-danger small mt-2 w-100 d-flex align-items-center gap-1">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror
    </div>
</div>

<!-- Shifts Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-stopwatch text-primary"></i>
            <span>Plant Shift Schedule Directory</span>
        </div>
        <span class="badge-modern badge-slate">{{ $shifts->total() }} Shifts Registered</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="width: 140px;">Shift Code</th>
                    <th>Shift Name & Details</th>
                    <th>Operational Hours (24H)</th>
                    <th>Standard Time (12H)</th>
                    <th class="text-center">Duration</th>
                    <th>Shift Type</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($shifts as $s)
                <tr>
                    <td>
                        <span class="badge-modern badge-shibaura fw-bold">
                            <i class="bi bi-tag-fill"></i> {{ $s->code }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6">{{ $s->name }}</span>
                            @if($s->is_currently_active)
                                <span class="badge-modern badge-emerald py-0 px-2 fs-xs" title="Currently running right now">
                                    <span class="status-dot active"></span> Live Now
                                </span>
                            @endif
                        </div>
                        @if($s->description)
                            <div class="small text-muted line-clamp-1" style="max-width: 320px;">
                                {{ $s->description }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold text-dark font-monospace fs-6">
                            <i class="bi bi-clock me-1 text-primary"></i> {{ $s->formatted_24h_range }}
                        </div>
                    </td>
                    <td>
                        <div class="small text-muted">
                            {{ $s->formatted_12h_range }}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge-modern badge-slate fw-semibold">
                            <i class="bi bi-hourglass-split me-1 text-secondary"></i> {{ $s->duration }}
                        </span>
                    </td>
                    <td>
                        @if($s->is_overnight)
                            <span class="badge-modern badge-indigo" title="Overnight shift crossing 00:00 midnight">
                                <i class="bi bi-moon-stars-fill text-indigo-400"></i> Overnight
                            </span>
                        @else
                            <span class="badge-modern badge-cyan" title="Same-day operational shift">
                                <i class="bi bi-sun-fill text-amber-500"></i> Day Shift
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($s->is_active)
                            <span class="badge-modern badge-emerald">
                                <span class="status-dot active"></span> Active
                            </span>
                        @else
                            <span class="badge-modern badge-slate">
                                <span class="status-dot inactive"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('shifts.edit', $s) }}" class="btn-action-icon edit" title="Edit shift">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form method="POST" action="{{ route('shifts.toggle', $s) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn-action-icon" type="submit" title="{{ $s->is_active ? 'Deactivate shift' : 'Activate shift' }}">
                                    <i class="bi {{ $s->is_active ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }} fs-5"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('shifts.destroy', $s) }}" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $s->name }} ({{ $s->code }})?')">
                                @csrf @method('DELETE')
                                <button class="btn-action-icon delete" type="submit" title="Delete shift">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-clock fs-2 d-block mb-2 text-slate-300"></i>
                        No manufacturing shifts match the selected filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Footer Container -->
<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $shifts->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $shifts->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $shifts->total() }}</span> shifts (10 per page)
    </div>
    <div>
        {{ $shifts->links() }}
    </div>
</div>
@endsection
