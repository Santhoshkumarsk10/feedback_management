@extends('layouts.app')
@section('title', 'Plant Master Management')
@section('page_title', 'Manufacturing Plants & Facilities')
@section('page_subtitle', 'Manage Shibaura Machine manufacturing plants, technical divisions, and customer demo centers')

@section('topbar_actions')
    <a href="{{ route('plants.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-building-add"></i>
        <span>Register New Plant</span>
    </a>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('plants.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-buildings-fill"></i>
                <span>All Facilities</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('plants.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Operational (Active)</span>
                <span class="filter-tab-badge">{{ $tabCounts['active'] ?? 0 }}</span>
            </a>
            <a href="{{ route('plants.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
                <i class="bi bi-pause-circle-fill text-muted"></i>
                <span>Inactive / Maintenance</span>
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
        <form method="GET" action="{{ route('plants.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="plantSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search plant code, facility name, location..." 
                :suggestions="$allPlantSuggestions ?? []"
                header-title="Plant Directory Suggestions"
            />

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('plants.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Plants Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-building-gear text-primary"></i>
            <span>Plant Directory</span>
        </div>
        <span class="badge-modern badge-slate">{{ $plants->total() }} Plants Listed</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="width: 140px;">Plant Code</th>
                    <th>Facility & Capability</th>
                    <th>Location / City</th>
                    <th>Contact Info</th>
                    <th class="text-center">Team Members</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($plants as $p)
                <tr>
                    <td>
                        <span class="badge-modern badge-shibaura fw-bold">
                            <i class="bi bi-qr-code"></i> {{ $p->code }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ $p->name }}</div>
                        @if($p->description)
                            <div class="small text-muted line-clamp-1" style="max-width: 380px;">
                                {{ $p->description }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($p->location)
                            <div class="small text-dark">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $p->location }}
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->contact_email)
                            <div class="small text-dark">
                                <i class="bi bi-envelope text-muted me-1"></i> {{ $p->contact_email }}
                            </div>
                        @endif
                        @if($p->contact_phone)
                            <div class="small text-muted">
                                <i class="bi bi-telephone text-muted me-1"></i> {{ $p->contact_phone }}
                            </div>
                        @endif
                        @if(!$p->contact_email && !$p->contact_phone)
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('users.index', ['plant_id' => $p->id]) }}" class="badge-modern badge-slate text-decoration-none" title="View users in this plant">
                            <i class="bi bi-people me-1"></i> {{ $p->users_count }} Users
                        </a>
                    </td>
                    <td>
                        @if($p->is_active)
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
                            <a href="{{ route('plants.edit', $p) }}" class="btn-action-icon edit" title="Edit plant">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form method="POST" action="{{ route('plants.toggle', $p) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn-action-icon" type="submit" title="{{ $p->is_active ? 'Deactivate plant' : 'Activate plant' }}">
                                    <i class="bi {{ $p->is_active ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }} fs-5"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('plants.destroy', $p) }}" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn-action-icon delete" type="submit" title="Delete plant" @disabled($p->users_count > 0)>
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-building fs-2 d-block mb-2 text-slate-300"></i>
                        No manufacturing plant facilities match the criteria.
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
        Showing <span class="fw-bold text-dark">{{ $plants->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $plants->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $plants->total() }}</span> plants (10 per page)
    </div>
    <div>
        {{ $plants->links() }}
    </div>
</div>
@endsection
