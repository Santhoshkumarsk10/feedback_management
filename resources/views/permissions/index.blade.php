@extends('layouts.app')
@section('title', 'Capability Permissions Directory')
@section('page_title', 'Capability Permissions Directory')
@section('page_subtitle', 'Manage granular system authorizations, module capabilities, and role mappings.')

@section('topbar_actions')
    @if(auth()->user()?->isSuperAdmin())
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
            <i class="bi bi-plus-circle"></i>
            <span>Add Permission</span>
        </button>
    @else
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-3 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.76rem; font-weight: 600;">
            <i class="bi bi-person-fill-gear text-purple"></i> Coordinator Management
        </span>
    @endif
    @can('rolemaster.view')
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
            <i class="bi bi-shield-shaded text-primary"></i>
            <span>Role Master</span>
        </a>
    @endcan
@endsection

@section('content')
@php
    $totalPerms = $totalCount ?? 0;
    $totalMods = is_countable($allModules) ? count($allModules) : 0;
    $currentMod = request('module');
@endphp

<!-- SUMMARY METRIC CARDS ROW (4-Column Reference Design) -->
<div class="perm-metrics-grid">
    <!-- 1. Total Permissions -->
    <a href="{{ route('permissions.index', array_merge(request()->except(['module', 'page']))) }}" class="perm-metric-card">
        <div class="perm-metric-content">
            <div class="perm-metric-icon blue">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div>
                <div class="perm-metric-val">{{ number_format($totalPerms) }}</div>
                <div class="perm-metric-lbl">Total permissions</div>
            </div>
        </div>
        <div class="perm-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>

    <!-- 2. Security Modules -->
    <div class="perm-metric-card" onclick="document.getElementById('filterButtonDropdown')?.click();" title="Click to view all modules" role="button">
        <div class="perm-metric-content">
            <div class="perm-metric-icon green">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div>
                <div class="perm-metric-val">{{ number_format($totalMods) }}</div>
                <div class="perm-metric-lbl">Security modules</div>
            </div>
        </div>
        <div class="perm-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>

    <!-- 3. Filtered Scope -->
    <div class="perm-metric-card" onclick="document.getElementById('filterButtonDropdown')?.click();" title="Click to change module filter" role="button">
        <div class="perm-metric-content">
            <div class="perm-metric-icon purple">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <div class="perm-metric-val fs-3 text-truncate" style="max-width: 140px;">{{ $currentMod ? ucfirst($currentMod) : 'All' }}</div>
                <div class="perm-metric-lbl">Filtered scope</div>
            </div>
        </div>
        <div class="perm-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>

    <!-- 4. Gate Policy -->
    <div class="perm-metric-card">
        <div class="perm-metric-content">
            <div class="perm-metric-icon amber">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="perm-metric-val">RBAC</div>
                <div class="perm-metric-lbl">Gate policy</div>
            </div>
        </div>
        <div class="perm-metric-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>
</div>

<!-- SEARCH & FILTERS TOOLBAR (Filter Button Module Selector - Replaces horizontal scroll pills) -->
<div class="perm-toolbar-row">
    <!-- Left: Search Box & Filter Button -->
    <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
        <!-- Search Input -->
        <form method="GET" action="{{ route('permissions.index') }}" class="d-flex align-items-center gap-2 m-0" id="permissionSearchForm" style="min-width: 260px; max-width: 420px; flex-grow: 1;">
            @if(request('module'))
                <input type="hidden" name="module" value="{{ request('module') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="visit-search-box w-100">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       class="visit-search-input" 
                       placeholder="Search capability, slug key..."
                       autocomplete="off">
                @if(request('q'))
                    <a href="{{ route('permissions.index', request()->except('q')) }}" class="search-clear-btn" title="Clear search">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Filter Button with Vertical Modules Dropdown (No horizontal scroll) -->
        <div class="dropdown">
            <button type="button" 
                    class="btn-visit-toolbar dropdown-toggle {{ request('module') && request('module') !== 'all' ? 'active border-primary text-primary bg-primary-subtle' : '' }}" 
                    data-bs-toggle="dropdown" 
                    data-bs-auto-close="outside"
                    aria-expanded="false" 
                    id="filterButtonDropdown"
                    style="height: 38px;">
                <i class="bi bi-funnel{{ request('module') && request('module') !== 'all' ? '-fill text-primary' : '' }}"></i>
                <span>{{ request('module') && request('module') !== 'all' ? request('module') : 'Filter' }}</span>
                @if(request('module') && request('module') !== 'all')
                    <span class="badge bg-primary text-white rounded-pill ms-1" style="font-size: 0.72rem;">
                        {{ $moduleCounts[request('module')] ?? 0 }}
                    </span>
                @else
                    <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size: 0.72rem;">
                        All ({{ $totalCount }})
                    </span>
                @endif
            </button>

            <div class="dropdown-menu dropdown-menu-start shadow-lg border-0 rounded-3 mt-1 p-0" style="min-width: 310px; max-width: 350px; z-index: 1050;">
                <!-- Header with Module Count and Live Search -->
                <div class="p-3 pb-2 border-bottom bg-light rounded-top-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-shield-lock me-1"></i>Filter by Module
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.68rem;">
                            {{ count($allModules) }} Modules
                        </span>
                    </div>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 0.8rem;"></i>
                        <input type="text" 
                               class="form-control form-control-sm bg-white ps-4 rounded-2 border" 
                               id="moduleSearchInput" 
                               placeholder="Type to find module..." 
                               autocomplete="off">
                    </div>
                </div>

                <!-- Vertical Module List -->
                <div class="dropdown-modules-list py-1" style="max-height: 280px; overflow-y: auto;">
                    <!-- All Modules -->
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between module-dropdown-item {{ !request('module') || request('module') === 'all' ? 'active fw-bold' : '' }}" 
                       data-module-name="all modules"
                       href="{{ route('permissions.index', array_merge(request()->except(['module', 'page']))) }}">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-grid-fill {{ !request('module') || request('module') === 'all' ? 'text-white' : 'text-primary' }}"></i>
                            <span>All Modules</span>
                        </span>
                        <span class="badge {{ !request('module') || request('module') === 'all' ? 'bg-white text-primary' : 'bg-light text-secondary border' }} rounded-pill">
                            {{ $totalCount }}
                        </span>
                    </a>

                    <div class="dropdown-divider my-1"></div>

                    <!-- Individual Modules -->
                    @foreach($allModules as $m)
                        @php
                            $isAct = request('module') === $m;
                            $count = $moduleCounts[$m] ?? 0;
                        @endphp
                        <a class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between module-dropdown-item {{ $isAct ? 'active fw-bold' : '' }}" 
                           data-module-name="{{ strtolower($m) }}"
                           href="{{ route('permissions.index', array_merge(request()->except(['page']), ['module' => $m])) }}">
                            <span class="d-flex align-items-center gap-2 text-truncate me-2">
                                <i class="bi bi-folder2 {{ $isAct ? 'text-white' : 'text-muted' }}"></i>
                                <span class="text-truncate">{{ $m }}</span>
                            </span>
                            <span class="badge {{ $isAct ? 'bg-white text-primary' : 'bg-light text-secondary border' }} rounded-pill flex-shrink-0">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach
                </div>

                @if(request('module') && request('module') !== 'all')
                    <div class="p-2 border-top bg-light rounded-bottom-3 text-center">
                        <a href="{{ route('permissions.index', array_merge(request()->except(['module', 'page']))) }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold p-0" style="font-size: 0.8rem;">
                            <i class="bi bi-x-circle me-1"></i>Clear Module Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reset Button -->
        @if(request()->filled('q') || (request()->filled('module') && request('module') !== 'all'))
            <a href="{{ route('permissions.index') }}" class="btn-visit-toolbar text-danger" style="height: 38px;" title="Reset all filters">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span class="d-none d-sm-inline">Reset</span>
            </a>
        @endif
    </div>

    <!-- Right: Active Filter Scope / Count Indicator -->
    <div class="d-flex align-items-center gap-2">
        @if(request('module') && request('module') !== 'all')
            <div class="d-inline-flex align-items-center gap-2 bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-3" style="font-size: 0.82rem; font-weight: 500;">
                <i class="bi bi-funnel-fill"></i>
                <span>Module: <strong>{{ request('module') }}</strong> ({{ $moduleCounts[request('module')] ?? 0 }})</span>
                <a href="{{ route('permissions.index', array_merge(request()->except(['module', 'page']))) }}" class="text-primary text-decoration-none ms-1" title="Clear module filter" style="line-height: 1;">
                    <i class="bi bi-x-circle-fill"></i>
                </a>
            </div>
        @else
            <div class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.82rem;">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Showing <strong>{{ $totalCount }}</strong> capabilities across <strong>{{ count($allModules) }}</strong> modules</span>
            </div>
        @endif
    </div>
</div>

<!-- PERMISSIONS TABLE CARD (Reference Design) -->
<div class="perm-table-card">
    <div class="table-responsive">
        <table class="perm-table">
            <thead>
                <tr>
                    <th style="min-width: 240px; width: 25%;">CAPABILITY TITLE</th>
                    <th style="min-width: 170px; width: 18%;">PERMISSION KEY</th>
                    <th style="min-width: 150px; width: 14%;">MODULE</th>
                    <th style="min-width: 240px; width: 21%;">DESCRIPTION</th>
                    <th style="min-width: 140px; width: 11%;">ROLES</th>
                    <th style="min-width: 100px; width: 8%;">STATUS</th>
                    <th style="min-width: 110px;" class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            @forelse($permissions as $p)
                @php
                    $keyLower = strtolower($p->name);
                    if (str_contains($keyLower, 'create') || str_contains($keyLower, 'store') || str_contains($keyLower, 'add')) {
                        $iconClass = 'create';
                        $iconName = 'bi-file-earmark-plus-fill';
                        $subAction = 'Create new ' . strtolower($p->module ?: 'records');
                    } elseif (str_contains($keyLower, 'delete') || str_contains($keyLower, 'destroy') || str_contains($keyLower, 'remove')) {
                        $iconClass = 'delete';
                        $iconName = 'bi-trash-fill';
                        $subAction = 'Delete existing ' . strtolower($p->module ?: 'records');
                    } elseif (str_contains($keyLower, 'edit') || str_contains($keyLower, 'update') || str_contains($keyLower, 'modify')) {
                        $iconClass = 'edit';
                        $iconName = 'bi-pencil-fill';
                        $subAction = 'Modify ' . strtolower($p->module ?: 'records');
                    } elseif (str_contains($keyLower, 'export') || str_contains($keyLower, 'download') || str_contains($keyLower, 'sync')) {
                        $iconClass = 'export';
                        $iconName = 'bi-box-arrow-up';
                        $subAction = 'Export ' . strtolower($p->module ?: 'data');
                    } elseif (str_contains($keyLower, 'view') || str_contains($keyLower, 'index') || str_contains($keyLower, 'show') || str_contains($keyLower, 'list')) {
                        $iconClass = 'view';
                        $iconName = 'bi-eye-fill';
                        $subAction = 'View ' . strtolower($p->module ?: 'records');
                    } else {
                        $iconClass = 'default';
                        $iconName = 'bi-key-fill';
                        $subAction = 'Access ' . strtolower($p->module ?: 'capabilities');
                    }
                @endphp
                <tr>
                    <!-- 1. CAPABILITY TITLE -->
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="perm-icon-box {{ $iconClass }}">
                                <i class="bi {{ $iconName }}"></i>
                            </div>
                            <div>
                                <div class="perm-title-text">{{ $p->title }}</div>
                                <div class="perm-sub-text">{{ $subAction }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- 2. PERMISSION KEY -->
                    <td>
                        <span class="perm-key-pill" title="Permission slug key">
                            <span>&lt;/&gt; {{ $p->name }}</span>
                            <i class="bi bi-clipboard" title="Copy slug" onclick="navigator.clipboard.writeText('{{ $p->name }}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Copied: {{ $p->name }}', timer:1500, showConfirmButton:false});"></i>
                        </span>
                    </td>

                    <!-- 3. MODULE -->
                    <td>
                        <span class="perm-mod-pill">
                            <i class="bi bi-folder2-open"></i>
                            <span>{{ $p->module ?: 'General' }}</span>
                        </span>
                    </td>

                    <!-- 4. DESCRIPTION -->
                    <td>
                        <div class="role-desc-text">
                            {{ $p->description ?: ('Grants ' . strtolower($p->title) . ' access on ' . ($p->module ?: 'General') . ' module') }}
                        </div>
                    </td>

                    <!-- 5. ROLES -->
                    <td>
                        @php
                            $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
                            $filteredRoles = $p->roles;
                            if (!$isSuperAdmin) {
                                $filteredRoles = $filteredRoles->reject(fn ($r) => $r->name === 'superadmin');
                            }
                        @endphp
                        <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 190px;">
                            @if($filteredRoles->isEmpty())
                                <span class="badge bg-light text-muted border py-1 px-2" style="font-size: 0.72rem;">Unassigned</span>
                            @else
                                @foreach($filteredRoles as $r)
                                    @php
                                        $rName = strtolower($r->name);
                                        $pillClass = $rName === 'admin' ? 'blue' : ($rName === 'coordinator' ? 'purple' : 'default');
                                    @endphp
                                    <span class="perm-role-pill {{ $pillClass }}">
                                        {{ $r->display_name ?: ucfirst($r->name) }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </td>

                    <!-- 6. STATUS -->
                    <td>
                        <span class="perm-status-active">
                            <span class="dot"></span> Active
                        </span>
                    </td>

                    <!-- 7. ACTIONS -->
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                            @php
                                $canManage = $isSuperAdmin || (auth()->user()?->isAdmin() ?? false);
                            @endphp
                            @if($canManage)
                                <button type="button" class="btn-perm-edit" title="{{ $isSuperAdmin ? 'Edit capability permission' : 'Manage Coordinator access' }}"
                                        data-bs-toggle="modal" data-bs-target="#editPermissionModal"
                                        data-id="{{ $p->id }}"
                                        data-name="{{ $p->name }}"
                                        data-display-name="{{ $p->display_name ?: $p->title }}"
                                        data-module="{{ $p->module }}"
                                        data-description="{{ $p->description }}"
                                        data-is-system="{{ $p->is_system ? '1' : '0' }}"
                                        data-coord-has="{{ $p->roles->contains('name', 'coordinator') ? '1' : '0' }}"
                                        onclick="openEditPermissionModal(this)">
                                    <i class="bi {{ $isSuperAdmin ? 'bi-pencil' : 'bi-shield-check' }}"></i>
                                    <span>{{ $isSuperAdmin ? 'Edit' : 'Manage' }}</span>
                                </button>

                                <div class="dropdown">
                                    <button type="button" class="btn-perm-more" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1 py-1" style="min-width: 175px; font-size: 0.82rem;">
                                        <li>
                                            <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#editPermissionModal"
                                                    data-id="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-display-name="{{ $p->display_name ?: $p->title }}"
                                                    data-module="{{ $p->module }}"
                                                    data-description="{{ $p->description }}"
                                                    data-is-system="{{ $p->is_system ? '1' : '0' }}"
                                                    data-coord-has="{{ $p->roles->contains('name', 'coordinator') ? '1' : '0' }}"
                                                    onclick="openEditPermissionModal(this)">
                                                <i class="bi {{ $isSuperAdmin ? 'bi-pencil-square text-primary' : 'bi-shield-check text-primary' }}"></i>
                                                <span>{{ $isSuperAdmin ? 'Edit Permission' : 'Manage Coordinator' }}</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2"
                                                    onclick="navigator.clipboard.writeText('{{ $p->name }}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Copied permission key', timer:1500, showConfirmButton:false});">
                                                <i class="bi bi-clipboard text-muted"></i>
                                                <span>Copy Slug Key</span>
                                            </button>
                                        </li>
                                        @if($isSuperAdmin && !$p->is_system)
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form method="POST" action="{{ route('permissions.destroy', $p) }}"
                                                      data-confirm="Are you sure you want to delete permission: {{ $p->title }} ({{ $p->name }})?">
                                                    @csrf @method('DELETE')
                                                    <button class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" type="submit">
                                                        <i class="bi bi-trash3"></i>
                                                        <span>Delete Permission</span>
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @else
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
                                <i class="bi bi-key text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No permissions found</h5>
                            <p class="text-muted small mb-3">No capability permissions match the selected criteria.</p>
                            @can('permissionmaster.edit')
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                                    <i class="bi bi-plus-circle me-1"></i> Add Permission
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 fs-xs text-muted">
        <div>
            Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} permissions @if($permissions->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $permissions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('permissionmaster.edit')
<!-- ADD PERMISSION MODAL -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('permissions.store') }}" novalidate>
                @csrf
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="role-icon-box blue p-2 rounded-3">
                            <i class="bi bi-key-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="addPermissionModalLabel">Add Capability Permission</h5>
                            <p class="text-muted small mb-0">Define granular system authorization and module mapping</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_perm_module">System Module</label>
                            <input type="text" name="module" id="add_perm_module" list="modulesDatalist" class="form-control form-control-modern" placeholder="e.g. Plant Master, Visitor Directory" required minlength="2" maxlength="100">
                            <datalist id="modulesDatalist">
                                @foreach($allModules as $mod)
                                    <option value="{{ $mod }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_perm_title">Capability Title</label>
                            <input type="text" name="display_name" id="add_perm_title" class="form-control form-control-modern" placeholder="e.g. View Plant Tour Records" required minlength="2" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_perm_slug">Permission Key (Slug)</label>
                            <input type="text" name="name" id="add_perm_slug" class="form-control form-control-modern font-monospace" placeholder="e.g. plantmaster.view" required minlength="3" maxlength="100" pattern="^[a-z0-9\.\-_]+$" title="Lowercase letters, numbers, dots, hyphens, and underscores">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Lowercase letters, numbers, dots, hyphens</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_perm_desc">Description / Scope</label>
                            <input type="text" name="description" id="add_perm_desc" class="form-control form-control-modern" placeholder="Grants authorization to inspect tour history" maxlength="150">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-key-fill me-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@php
    $canManagePermModal = auth()->user()?->isSuperAdmin() || (auth()->user()?->isAdmin() ?? false) || auth()->user()?->can('permissionmaster.edit');
@endphp

@if($canManagePermModal)
<!-- EDIT / MANAGE PERMISSION MODAL -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="editPermissionForm" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="role-icon-box {{ $isSuperAdmin ? 'green' : 'purple' }} p-2 rounded-3">
                            <i class="bi {{ $isSuperAdmin ? 'bi-pencil-square text-success' : 'bi-shield-check text-primary' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="editPermissionModalLabel">
                                {{ $isSuperAdmin ? 'Edit Capability Permission' : 'Manage Coordinator Permission' }}
                            </h5>
                            <p class="text-muted small mb-0">
                                {{ $isSuperAdmin ? 'Update capability display title, module category, or description' : 'Grant or revoke capability authorization for Coordinator role' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($isSuperAdmin)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-modern-label required" for="edit_perm_module">System Module</label>
                                <input type="text" name="module" id="edit_perm_module" list="modulesDatalist" class="form-control form-control-modern" required minlength="2" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-modern-label required" for="edit_perm_title">Capability Title</label>
                                <input type="text" name="display_name" id="edit_perm_title" class="form-control form-control-modern" required minlength="2" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-modern-label required" for="edit_perm_slug">Permission Key (Slug)</label>
                                <input type="text" name="name" id="edit_perm_slug" class="form-control form-control-modern font-monospace" required minlength="3" maxlength="100" pattern="^[a-z0-9\.\-_]+$" title="Lowercase letters, numbers, dots, hyphens, and underscores">
                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">Core permission keys are referenced in authorization gates</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-modern-label" for="edit_perm_desc">Description / Scope</label>
                                <input type="text" name="description" id="edit_perm_desc" class="form-control form-control-modern" maxlength="150">
                            </div>
                        </div>
                    @else
                        <!-- Admin View: Manage Coordinator Access Only -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-secondary-subtle text-secondary" id="coord_modal_module">Module</span>
                                <code class="font-monospace text-primary fs-xs" id="coord_modal_slug">key.slug</code>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" id="coord_modal_title">Capability Title</h6>
                            <p class="text-muted small mb-0" id="coord_modal_desc">Description</p>
                        </div>

                        <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: #fdf4ff; border-color: #fae8ff !important;">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-person-fill text-primary fs-5"></i>
                                    <span>Coordinator Role Authorization</span>
                                </div>
                                <div class="text-muted small mt-1">Allow users with the Coordinator role to execute this capability.</div>
                            </div>
                            <div class="form-check form-switch fs-4 m-0">
                                <input class="form-check-input cursor-pointer" type="checkbox" name="coordinator_access" id="edit_perm_coord_access" value="1">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> {{ $isSuperAdmin ? 'Update Permission' : 'Save Coordinator Access' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    function openEditPermissionModal(btn) {
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('editPermissionForm');
        form.action = `{{ url('admin/permissions') }}/${id}`;

        const isSuperAdmin = {{ (auth()->user()?->isSuperAdmin() ?? false) ? 'true' : 'false' }};
        if (isSuperAdmin) {
            const modInput = document.getElementById('edit_perm_module');
            if (modInput) modInput.value = btn.getAttribute('data-module') || '';
            const titleInput = document.getElementById('edit_perm_title');
            if (titleInput) titleInput.value = btn.getAttribute('data-display-name') || '';
            const slugInput = document.getElementById('edit_perm_slug');
            if (slugInput) {
                slugInput.value = btn.getAttribute('data-name') || '';
                const isSystem = btn.getAttribute('data-is-system') === '1';
                slugInput.readOnly = isSystem;
            }
            const descInput = document.getElementById('edit_perm_desc');
            if (descInput) descInput.value = btn.getAttribute('data-description') || '';
        } else {
            const titleEl = document.getElementById('coord_modal_title');
            if (titleEl) titleEl.textContent = btn.getAttribute('data-display-name') || btn.getAttribute('data-name') || '';
            const slugEl = document.getElementById('coord_modal_slug');
            if (slugEl) slugEl.textContent = btn.getAttribute('data-name') || '';
            const modEl = document.getElementById('coord_modal_module');
            if (modEl) modEl.textContent = btn.getAttribute('data-module') || '';
            const descEl = document.getElementById('coord_modal_desc');
            if (descEl) descEl.textContent = btn.getAttribute('data-description') || 'Standard system authorization';

            const coordSwitch = document.getElementById('edit_perm_coord_access');
            if (coordSwitch) {
                coordSwitch.checked = (btn.getAttribute('data-coord-has') === '1');
            }
        }
    }

    // Live search filter inside the Module dropdown
    document.addEventListener('DOMContentLoaded', function () {
        const moduleSearchInput = document.getElementById('moduleSearchInput');
        if (moduleSearchInput) {
            moduleSearchInput.addEventListener('input', function (e) {
                const val = e.target.value.toLowerCase().trim();
                const items = document.querySelectorAll('.module-dropdown-item');
                items.forEach(function (item) {
                    const name = item.getAttribute('data-module-name') || '';
                    if (!val || name.includes(val)) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                    }
                });
            });

            // Prevent dropdown from closing when clicking inside search input
            moduleSearchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
            moduleSearchInput.addEventListener('keydown', function (e) {
                e.stopPropagation();
            });
        }
    });
</script>
@endpush
