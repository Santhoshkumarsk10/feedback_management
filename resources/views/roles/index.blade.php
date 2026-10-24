@extends('layouts.app')
@section('title', 'Role Master Management')
@section('page_title', 'Access Roles & Permissions')
@section('page_subtitle', 'Configure system roles, tour guide responsibilities, and operational access levels')

@section('topbar_actions')
    @can('rolemaster.create')
        <a href="{{ route('roles.create') }}" class="btn-modern-primary btn-sm">
            <i class="bi bi-shield-plus"></i>
            <span>Add Role</span>
        </a>
    @else
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
            <i class="bi bi-eye"></i> View Only
        </span>
    @endcan
    @can('permissionmaster.view')
        <a href="{{ route('permissions.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-key-fill text-primary"></i>
            <span>Permission Master</span>
        </a>
    @endcan
@endsection

@section('summary_cards')
@php
    $totalRoles = $tabCounts['all'] ?? $tabCounts['total'] ?? 0;
    $activeRoles = $tabCounts['active'] ?? 0;
    $inactiveRoles = $tabCounts['inactive'] ?? 0;
@endphp
<div class="row g-3">
    <!-- 1. Total Roles -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-roles.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalRoles) }}</div>
                        <div class="saas-stat-label">Total roles</div>
                    </div>
                </div>
                <span class="saas-stat-badge slate">Personas</span>
            </div>
        </div>
    </div>

    <!-- 2. Active Roles -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/system-roles.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($activeRoles) }}</div>
                        <div class="saas-stat-label">Active Roles</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Operational</span>
            </div>
        </div>
    </div>

    <!-- 3. Inactive Roles -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/custom-roles.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($inactiveRoles) }}</div>
                        <div class="saas-stat-label">Inactive Roles</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Standby</span>
            </div>
        </div>
    </div>

    <!-- 4. Permission Grants -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/permissions.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">Granted</div>
                        <div class="saas-stat-label">Permissions</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Matrix access</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <!-- Left: Filter Pills -->
    <div class="saas-filter-pills">
        <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']))) }}"
           class="saas-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
            <span>All roles</span>
            <span class="pill-counter">{{ $tabCounts['all'] ?? $tabCounts['total'] ?? 0 }}</span>
        </a>
        <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
            <span>Active</span>
            <span class="pill-counter">{{ $tabCounts['active'] ?? 0 }}</span>
        </a>
        <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
            <span>Inactive</span>
            <span class="pill-counter">{{ $tabCounts['inactive'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Right: Search & Filter -->
    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center gap-2 m-0" id="roleSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="saas-search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" class="saas-search-input" placeholder="Search role title, slug, description...">
            </div>

            <button type="submit" class="saas-btn-filter">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('roles.index') }}" class="saas-btn-filter text-decoration-none" title="Clear filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Roles Table Card -->
<div class="saas-table-card">
    <div class="saas-table-header d-flex align-items-center justify-content-between p-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-shaded text-primary"></i>
            <span class="fw-bold text-dark">Defined System Roles</span>
        </div>
        <span class="badge-modern badge-slate">{{ $roles->total() }} Roles Listed</span>
    </div>


    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Role Title</th>
                    <th style="width: 15%;">Identifier</th>
                    <th style="width: 20%;">Permissions</th>
                    <th style="width: 18%;">Scope & Description</th>
                    <th class="text-center" style="width: 10%;">Members</th>
                    <th style="width: 6%;">Status</th>
                    <th class="text-end" style="width: 6%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($roles as $r)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="saas-stat-icon {{ $r->is_system ? 'indigo' : 'amber' }}" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 8px;">
                                <i class="bi {{ $r->is_system ? 'bi-shield-check' : 'bi-person-badge' }}"></i>
                            </span>
                            <div>
                                <span class="fw-bold text-dark fs-6">{{ $r->display_name ?: $r->name }}</span>
                                @if($r->is_system)
                                    <span class="badge-modern badge-shibaura ms-1" style="font-size: 0.65rem;">System</span>
                                @endif
                            </div>
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
                            <span class="badge-modern badge-indigo py-1">
                                <i class="bi bi-key-fill text-indigo-400"></i> {{ $r->permissions_count }} Permissions
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="small text-muted line-clamp-1" style="max-width: 280px;">
                            {{ $r->description ?: 'No additional description provided.' }}
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('users.index', ['role' => $r->name]) }}" class="badge-modern badge-slate text-decoration-none" title="Filter users with this role">
                            <i class="bi bi-people me-1"></i> {{ $r->assigned_users_count ?? $r->users_count }} Members
                        </a>
                    </td>
                    <td>
                        @if(!$r->is_system)
                            <form method="POST" action="{{ route('roles.toggle', $r) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <label class="saas-status-switch m-0" title="Click to toggle status">
                                    <input type="checkbox" class="saas-switch-input" {{ $r->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="saas-switch-slider"></span>
                                    <span class="saas-switch-label {{ $r->is_active ? '' : 'inactive' }}">{{ $r->is_active ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </form>
                        @else
                            <span class="saas-switch-label"><i class="bi bi-lock-fill"></i> Core</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-2 align-items-center">
                            @can('rolemaster.edit')
                                <a href="{{ route('roles.edit', $r) }}" class="btn-action-edit-pill" title="Edit role & permissions">
                                    <i class="bi bi-pencil-fill"></i>
                                    <span>Edit</span>
                                </a>
                            @endcan
                            @can('rolemaster.delete')
                                @if(!$r->is_system)
                                    <form method="POST" action="{{ route('roles.destroy', $r) }}" class="d-inline"
                                          data-confirm="Are you sure you want to delete role '{{ $r->display_name ?: $r->name }}'?">
                                        @csrf @method('DELETE')
                                        <button class="btn-action-delete-pill" type="submit" title="Delete role" @disabled(($r->assigned_users_count ?? $r->users_count) > 0)>
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            @if(!auth()->user()->can('rolemaster.edit') && !auth()->user()->can('rolemaster.delete'))
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-eye"></i> View Only
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-modern py-4">
                            <div class="empty-icon-bubble mb-3">
                                <i class="bi bi-shield text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No roles found</h5>
                            <p class="text-muted small mb-3">No access roles match the selected filter.</p>
                            @can('rolemaster.create')
                                <a href="{{ route('roles.create') }}" class="btn-modern-primary btn-sm shadow-xs">
                                    <i class="bi bi-shield-plus"></i> Add Role
                                </a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} roles @if($roles->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div class="saas-pagination">
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
