@extends('layouts.app')
@section('title', 'Access Roles & Permissions')
@section('page_title', 'Access Roles & Permissions')
@section('page_subtitle', 'Configure system roles, tour guide responsibilities, and operational access.')

@section('topbar_actions')
    @can('rolemaster.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-xs">
            <i class="bi bi-shield-plus"></i>
            <span>Add Role</span>
        </a>
    @else
        @cannot('rolemaster.edit')
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
                <i class="bi bi-eye"></i> View Only
            </span>
        @endcannot
    @endcan
    @can('permissionmaster.view')
        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
            <i class="bi bi-key-fill text-primary"></i>
            <span>Permission Master</span>
        </a>
    @endcan
@endsection

@section('content')
@php
    $totalRoles = $tabCounts['all'] ?? $tabCounts['total'] ?? 0;
    $activeRoles = $tabCounts['active'] ?? 0;
    $inactiveRoles = $tabCounts['inactive'] ?? 0;
    $totalPermissions = $tabCounts['permissions'] ?? 0;
@endphp

<!-- SUMMARY METRIC CARDS ROW (4-Column Reference Design) -->
<div class="role-metrics-grid">
    <!-- 1. Total Roles -->
    <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}" class="role-metric-card">
        <div class="role-metric-card-inner">
            <div class="role-metric-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="role-metric-info">
                <div class="role-metric-val">{{ number_format($totalRoles) }}</div>
                <div class="role-metric-lbl">Total Roles</div>
            </div>
            <div class="role-metric-arrow">
                <i class="bi bi-chevron-right"></i>
            </div>
        </div>
    </a>

    <!-- 2. Active Roles -->
    <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}" class="role-metric-card">
        <div class="role-metric-card-inner">
            <div class="role-metric-icon purple">
                <i class="bi bi-shield-shaded"></i>
            </div>
            <div class="role-metric-info">
                <div class="role-metric-val">{{ number_format($activeRoles) }}</div>
                <div class="role-metric-lbl">Active Roles</div>
            </div>
            <span class="role-metric-badge-top green">Operational</span>
            <div class="role-metric-arrow">
                <i class="bi bi-chevron-right"></i>
            </div>
        </div>
    </a>

    <!-- 3. Inactive Roles -->
    <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'inactive'])) }}" class="role-metric-card">
        <div class="role-metric-card-inner">
            <div class="role-metric-icon green">
                <i class="bi bi-power"></i>
            </div>
            <div class="role-metric-info">
                <div class="role-metric-val">{{ number_format($inactiveRoles) }}</div>
                <div class="role-metric-lbl">Inactive Roles</div>
            </div>
            <span class="role-metric-badge-top amber">Standby</span>
            <div class="role-metric-arrow">
                <i class="bi bi-chevron-right"></i>
            </div>
        </div>
    </a>

    <!-- 4. Permissions Matrix -->
    @can('permissionmaster.view')
        <a href="{{ route('permissions.index') }}" class="role-metric-card">
    @else
        <div class="role-metric-card">
    @endcan
        <div class="role-metric-card-inner">
            <div class="role-metric-icon amber">
                <i class="bi bi-key-fill"></i>
            </div>
            <div class="role-metric-info">
                <div class="role-metric-val">Granted</div>
                <div class="role-metric-lbl">Permissions</div>
            </div>
            <span class="role-metric-badge-top purple">Matrix access</span>
            <div class="role-metric-arrow">
                <i class="bi bi-chevron-right"></i>
            </div>
        </div>
    @can('permissionmaster.view')
        </a>
    @else
        </div>
    @endcan
</div>

<!-- FILTER PILLS & SEARCH TOOLBAR (Reference Design) -->
<div class="role-toolbar-row">
    <!-- Left: Filter Pills -->
    <div class="role-filter-pills role-toolbar-tabs">
        <a href="{{ route('roles.index', array_merge(request()->except(['tab', 'page']))) }}"
           class="role-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i>
            <span>All roles</span>
            <span class="role-pill-counter">{{ $totalRoles }}</span>
        </a>

        <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
           class="role-pill-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
            <span class="role-pill-dot green"></span>
            <span>Active</span>
            <span class="role-pill-counter">{{ $activeRoles }}</span>
        </a>

        <a href="{{ route('roles.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
           class="role-pill-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
            <span class="role-pill-dot gray"></span>
            <span>Inactive</span>
            <span class="role-pill-counter">{{ $inactiveRoles }}</span>
        </a>
    </div>

    <!-- Right: Search Box & Filter Button -->
    <div class="role-toolbar-search d-flex align-items-center gap-2 justify-content-end">
        <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center gap-2 m-0 w-100 flex-wrap flex-sm-nowrap" id="roleSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="visit-search-box flex-grow-1">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       class="visit-search-input" 
                       placeholder="Search role title, slug, description..."
                       autocomplete="off">
                @if(request('q'))
                    <a href="{{ route('roles.index', request()->except('q')) }}" class="search-clear-btn" title="Clear search">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-visit-toolbar" style="height: 38px;">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('roles.index') }}" class="btn-visit-toolbar text-danger" style="height: 38px;" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- ROLES TABLE CARD (Reference Design) -->
@php
    $isSuperAdmin = auth()->user()?->isSuperAdmin();
    $canRoleEdit = auth()->user()?->can('rolemaster.edit');
    $canRoleDelete = $isSuperAdmin && auth()->user()?->can('rolemaster.delete');
    $roleMeta = [];

    foreach ($roles as $roleItem) {
        $roleLower = strtolower($roleItem->name);
        if ($roleLower === 'admin') {
            $iconBoxClass = 'blue';
            $iconClass = 'bi-shield-shaded';
        } elseif ($roleLower === 'coordinator') {
            $iconBoxClass = 'purple';
            $iconClass = 'bi-person-fill';
        } elseif ($roleLower === 'superadmin') {
            $iconBoxClass = 'green';
            $iconClass = 'bi-shield-check';
        } else {
            $iconBoxClass = 'amber';
            $iconClass = 'bi-shield';
        }

        $roleMeta[$roleItem->id] = [
            'iconBoxClass' => $iconBoxClass,
            'icon' => $iconClass,
            'memberCount' => $roleItem->assigned_users_count ?? $roleItem->users_count ?? 0,
            'desc' => $roleItem->description ?: ($roleItem->is_system ? 'System core role managing plant facilities, visitor operations, and reporting.' : 'Custom operational role with configurable permissions.'),
            'modalDesc' => $roleItem->description ?: 'Core system role managing facilities, visits, and operational workflows.',
            'canEdit' => $canRoleEdit && ($isSuperAdmin || $roleItem->name !== 'admin'),
            'canDelete' => $canRoleDelete && !$roleItem->is_system,
        ];
    }
@endphp

<div class="role-table-card">
    @if($roles->isEmpty())
        <div class="text-center py-5 px-3">
            <div class="empty-state-modern py-4">
                <div class="empty-icon-bubble mb-3">
                    <i class="bi bi-shield text-muted fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No roles found</h5>
                <p class="text-muted small mb-3">No access roles match the selected filter.</p>
                @can('rolemaster.create')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-shield-plus"></i> Add Role
                    </a>
                @endcan
            </div>
        </div>
    @else
        <!-- Desktop & Laptop Table View (>= 992px) -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="role-table">
                    <thead>
                        <tr>
                            <th>ROLE TITLE</th>
                            <th>IDENTIFIER</th>
                            <th>PERMISSIONS</th>
                            <th class="d-none d-xl-table-cell">SCOPE &amp; DESCRIPTION</th>
                            <th>MEMBERS</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $r)
                        @php($meta = $roleMeta[$r->id])
                        <tr>
                            <!-- 1. ROLE TITLE -->
                            <td>
                                <div class="d-flex align-items-center gap-2 gap-xl-3 min-w-0">
                                    <div class="role-icon-box flex-shrink-0 {{ $meta['iconBoxClass'] }}">
                                        <i class="bi {{ $meta['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="role-title-row">
                                            <span class="role-name">{{ $r->display_name ?: $r->name }}</span>
                                            @if($r->is_system)
                                                <span class="role-system-badge">System</span>
                                            @endif
                                        </div>
                                        <div class="role-desc-text role-table-desc-clamp d-xl-none mt-1" title="{{ $meta['desc'] }}">
                                            {{ $meta['desc'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. IDENTIFIER -->
                            <td>
                                <span class="role-identifier-pill" title="Role identifier slug: {{ $r->name }}">
                                    <span class="role-identifier-slug">{{ $r->name }}</span>
                                    <i class="bi bi-clipboard" title="Copy slug" onclick="navigator.clipboard.writeText('{{ $r->name }}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Copied: {{ $r->name }}', timer:1500, showConfirmButton:false});"></i>
                                </span>
                            </td>

                            <!-- 3. PERMISSIONS -->
                            <td>
                                @if($r->name === 'superadmin')
                                    <span class="role-permission-pill green" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                        <i class="bi bi-check2"></i>
                                        <span>All System Permissions</span>
                                        <i class="bi bi-chevron-right fs-xs"></i>
                                    </span>
                                @else
                                    <span class="role-permission-pill purple" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                        <i class="bi bi-key-fill"></i>
                                        <span>{{ $r->permissions_count }} Permissions</span>
                                        <i class="bi bi-chevron-right fs-xs"></i>
                                    </span>
                                @endif
                            </td>

                            <!-- 4. SCOPE & DESCRIPTION -->
                            <td class="d-none d-xl-table-cell">
                                <div class="role-desc-text role-table-desc-clamp" title="{{ $meta['desc'] }}">
                                    {{ $meta['desc'] }}
                                </div>
                            </td>

                            <!-- 5. MEMBERS -->
                            <td>
                                <a href="{{ route('users.index', ['role' => $r->name]) }}" class="role-member-pill" title="Filter users with this role">
                                    <i class="bi bi-people member-icon"></i>
                                    <span>{{ $meta['memberCount'] }} {{ Str::plural('Member', $meta['memberCount']) }}</span>
                                    <i class="bi bi-chevron-right arrow-icon"></i>
                                </a>
                            </td>

                            <!-- 6. STATUS -->
                            <td>
                                @if($r->is_system)
                                    <span class="role-status-core">
                                        <span class="dot"></span> Core
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('roles.toggle', $r) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <label class="saas-status-switch m-0" title="Click to toggle status">
                                            <input type="checkbox" class="saas-switch-input" {{ $r->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="saas-switch-slider"></span>
                                            <span class="saas-switch-label {{ $r->is_active ? '' : 'inactive' }}">{{ $r->is_active ? 'Active' : 'Inactive' }}</span>
                                        </label>
                                    </form>
                                @endif
                            </td>

                            <!-- 7. ACTIONS -->
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                    @if($meta['canEdit'])
                                        <a href="{{ route('roles.edit', $r) }}" class="btn-role-edit" title="Edit role & permissions">
                                            <i class="bi bi-pencil"></i>
                                            <span>Edit</span>
                                        </a>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
                                            <i class="bi bi-lock-fill"></i> Read Only
                                        </span>
                                    @endif

                                    <div class="dropdown">
                                        <button type="button" class="btn-role-more" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1 py-1" style="min-width: 175px; font-size: 0.82rem;">
                                            <li>
                                                <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                                    <i class="bi bi-key text-muted"></i>
                                                    <span>View Permissions</span>
                                                </button>
                                            </li>
                                            @if($meta['canEdit'])
                                                <li>
                                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-primary" href="{{ route('roles.edit', $r) }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                        <span>Edit Role</span>
                                                    </a>
                                                </li>
                                            @endif
                                            @if($meta['canDelete'])
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('roles.destroy', $r) }}"
                                                          data-confirm="Are you sure you want to delete role '{{ $r->display_name ?: $r->name }}'?">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" type="submit" @disabled($meta['memberCount'] > 0)>
                                                            <i class="bi bi-trash3"></i>
                                                            <span>Delete Role</span>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tablet & Mobile Card View (< 992px) -->
        <div class="d-lg-none role-mobile-list">
            @foreach($roles as $r)
                @php($meta = $roleMeta[$r->id])
                <div class="role-mobile-card">
                    <div class="role-mobile-card-head">
                        <div class="role-mobile-card-identity">
                            <div class="role-icon-box flex-shrink-0 {{ $meta['iconBoxClass'] }}">
                                <i class="bi {{ $meta['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="role-title-row">
                                    <span class="role-name">{{ $r->display_name ?: $r->name }}</span>
                                    @if($r->is_system)
                                        <span class="role-system-badge">System</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <span class="role-identifier-pill" title="Role identifier slug: {{ $r->name }}">
                                        <span class="role-identifier-slug">{{ $r->name }}</span>
                                        <i class="bi bi-clipboard" title="Copy slug" onclick="navigator.clipboard.writeText('{{ $r->name }}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Copied: {{ $r->name }}', timer:1500, showConfirmButton:false});"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            @if($r->is_system)
                                <span class="role-status-core">
                                    <span class="dot"></span> Core
                                </span>
                            @else
                                <form method="POST" action="{{ route('roles.toggle', $r) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <label class="saas-status-switch m-0" title="Click to toggle status">
                                        <input type="checkbox" class="saas-switch-input" {{ $r->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="saas-switch-slider"></span>
                                        <span class="saas-switch-label {{ $r->is_active ? '' : 'inactive' }}">{{ $r->is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="role-desc-text mw-100">
                        {{ $meta['desc'] }}
                    </div>

                    <div class="role-mobile-card-chips">
                        @if($r->name === 'superadmin')
                            <span class="role-permission-pill green" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                <i class="bi bi-check2"></i>
                                <span>All System Permissions</span>
                                <i class="bi bi-chevron-right fs-xs"></i>
                            </span>
                        @else
                            <span class="role-permission-pill purple" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                <i class="bi bi-key-fill"></i>
                                <span>{{ $r->permissions_count }} Permissions</span>
                                <i class="bi bi-chevron-right fs-xs"></i>
                            </span>
                        @endif

                        <a href="{{ route('users.index', ['role' => $r->name]) }}" class="role-member-pill" title="Filter users with this role">
                            <i class="bi bi-people member-icon"></i>
                            <span>{{ $meta['memberCount'] }} {{ Str::plural('Member', $meta['memberCount']) }}</span>
                            <i class="bi bi-chevron-right arrow-icon"></i>
                        </a>
                    </div>

                    <div class="role-mobile-card-actions">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($meta['canEdit'])
                                <a href="{{ route('roles.edit', $r) }}" class="btn-role-edit" title="Edit role & permissions">
                                    <i class="bi bi-pencil"></i>
                                    <span>Edit Role</span>
                                </a>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-lock-fill"></i> Read Only
                                </span>
                            @endif

                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fs-xs" data-bs-toggle="modal" data-bs-target="#rolePermModal_{{ $r->id }}">
                                <i class="bi bi-key me-1"></i>View Permissions
                            </button>
                        </div>

                        @if($meta['canDelete'])
                            <form method="POST" action="{{ route('roles.destroy', $r) }}"
                                  data-confirm="Are you sure you want to delete role '{{ $r->display_name ?: $r->name }}'?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fs-xs" type="submit" @disabled($meta['memberCount'] > 0)>
                                    <i class="bi bi-trash3 me-1"></i>Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Table Footer with Pagination -->
    <div class="p-3 border-top role-pagination-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 fs-xs text-muted">
        <div class="text-center text-sm-start">
            Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} roles @if($roles->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div>
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Role Permissions Inspection Modals (rendered outside table/card structure for valid DOM) -->
@foreach($roles as $r)
    @php($meta = $roleMeta[$r->id])
    <div class="modal fade" id="rolePermModal_{{ $r->id }}" tabindex="-1" aria-labelledby="rolePermModalLabel_{{ $r->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-light align-items-start align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="role-icon-box flex-shrink-0 {{ $meta['iconBoxClass'] }}">
                            <i class="bi {{ $meta['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="modal-title fw-bold text-dark mb-0 text-truncate" id="rolePermModalLabel_{{ $r->id }}">{{ $r->display_name ?: $r->name }}</h5>
                            <div class="fs-xs text-muted">
                                Identifier: <code class="bg-white px-1 border rounded">{{ $r->name }}</code> &bull;
                                {{ $meta['memberCount'] }} active users
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-sm-4">
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Role Description</div>
                        <div class="small text-dark">{{ $meta['modalDesc'] }}</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <h6 class="fw-bold text-dark mb-0 fs-6">Granted System Permissions</h6>
                        <span class="badge bg-primary text-white rounded-pill px-2 py-1">
                            {{ $r->name === 'superadmin' ? 'All System Access' : $r->permissions->count() . ' Permissions' }}
                        </span>
                    </div>

                    @if($r->name === 'superadmin')
                        <div class="p-3 rounded-3 bg-success-subtle text-success border border-success-subtle text-center">
                            <i class="bi bi-shield-check fs-2 d-block mb-1"></i>
                            <div class="fw-bold">Superadmin Unrestricted Access</div>
                            <div class="fs-xs text-muted">This core system persona automatically possesses all permissions across all modules.</div>
                        </div>
                    @else
                        <div class="row g-2">
                            @forelse($r->permissions as $p)
                                <div class="col-12 col-md-6">
                                    <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between gap-2">
                                        <div class="role-perm-item-label">
                                            <div class="fw-semibold text-dark small">{{ $p->display_name ?: $p->name }}</div>
                                            <div class="fs-xs text-muted">{{ $p->module ?: 'General' }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0"></i>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted">
                                    No specific permissions attached to this role.
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-2 px-3 px-sm-4 bg-light d-flex flex-wrap justify-content-between gap-2">
                    @if($meta['canEdit'])
                        <a href="{{ route('roles.edit', $r) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="bi bi-pencil-square me-1"></i> Configure Permissions
                        </a>
                    @else
                        <span></span>
                    @endif
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
