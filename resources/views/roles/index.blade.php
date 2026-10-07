@extends('layouts.app')
@section('title', 'Role Master Management')
@section('page_title', 'Access Roles & Permissions')
@section('page_subtitle', 'Configure system roles, tour guide responsibilities, and operational access levels')

@section('topbar_actions')
    <a href="{{ route('roles.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-shield-plus"></i>
        <span>Create New Role</span>
    </a>
    <a href="{{ route('permissions.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-key-fill text-primary"></i>
        <span>Permission Master</span>
    </a>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>All Roles</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'system'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'system' ? 'active' : '' }}">
                <i class="bi bi-shield-check text-primary"></i>
                <span>System Core Roles</span>
                <span class="filter-tab-badge">{{ $tabCounts['system'] ?? 0 }}</span>
            </a>
            <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'custom'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'custom' ? 'active' : '' }}">
                <i class="bi bi-person-badge text-amber-500"></i>
                <span>Custom / Operational</span>
                <span class="filter-tab-badge">{{ $tabCounts['custom'] ?? 0 }}</span>
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
        <form method="GET" action="{{ route('roles.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search role title, identifier slug, description..." 
                :suggestions="$roleSuggestions ?? []"
                header-title="Role Directory Suggestions"
            />

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('roles.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Roles Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-shield-shaded text-primary"></i>
            <span>Defined System Roles</span>
        </div>
        <span class="badge-modern badge-slate">{{ $roles->total() }} Roles Listed</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Role Title</th>
                    <th>Identifier (Slug)</th>
                    <th>Permissions & Capabilities</th>
                    <th>Scope & Description</th>
                    <th>Classification</th>
                    <th class="text-center">Assigned Users</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($roles as $r)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="stat-icon-bubble {{ $r->is_system ? 'shibaura' : 'amber' }}" style="width: 32px; height: 32px; font-size: 0.95rem;">
                                <i class="bi {{ $r->is_system ? 'bi-shield-check' : 'bi-person-badge' }}"></i>
                            </span>
                            <span class="fw-bold text-dark fs-6">{{ $r->display_name ?: $r->name }}</span>
                        </div>
                    </td>
                    <td>
                        <code class="text-primary fw-semibold">{{ $r->name }}</code>
                    </td>
                    <td>
                        @if($r->name === 'superadmin')
                            <span class="badge-modern badge-emerald fw-bold">
                                <i class="bi bi-check-all"></i> All System Permissions
                            </span>
                        @else
                            <div class="d-flex align-items-center gap-1 flex-wrap" style="max-width: 280px;">
                                <span class="badge-modern badge-indigo py-1">
                                    <i class="bi bi-key-fill text-indigo-400"></i> {{ $r->permissions_count }} Permissions
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="small text-muted line-clamp-1" style="max-width: 320px;">
                            {{ $r->description ?: 'No additional description provided.' }}
                        </div>
                    </td>
                    <td>
                        @if($r->is_system)
                            <span class="badge-modern badge-shibaura" title="Core system role - protected from deletion">
                                <i class="bi bi-lock-fill"></i> System Core
                            </span>
                        @else
                            <span class="badge-modern badge-slate">
                                <i class="bi bi-sliders"></i> Custom Role
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('users.index', ['role' => $r->name]) }}" class="badge-modern badge-slate text-decoration-none" title="Filter users with this role">
                            <i class="bi bi-people me-1"></i> {{ $r->assigned_users_count ?? $r->users_count }} Members
                        </a>
                    </td>
                    <td>
                        @if($r->is_active)
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
                            <a href="{{ route('roles.edit', $r) }}" class="btn-action-icon edit" title="Edit role & permissions">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @if(!$r->is_system)
                                <form method="POST" action="{{ route('roles.toggle', $r) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn-action-icon" type="submit" title="{{ $r->is_active ? 'Deactivate role' : 'Activate role' }}">
                                        <i class="bi {{ $r->is_active ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }} fs-5"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('roles.destroy', $r) }}" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete role {{ $r->display_name ?: $r->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-icon delete" type="submit" title="Delete role" @disabled(($r->assigned_users_count ?? $r->users_count) > 0)>
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            @else
                                <span class="d-inline-block px-2 text-muted" title="Core system roles are locked from deletion">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-shield fs-2 d-block mb-2 text-slate-300"></i>
                        No roles match the selected filter.
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
        Showing <span class="fw-bold text-dark">{{ $roles->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $roles->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $roles->total() }}</span> roles (10 per page)
    </div>
    <div>
        {{ $roles->links() }}
    </div>
</div>
@endsection
