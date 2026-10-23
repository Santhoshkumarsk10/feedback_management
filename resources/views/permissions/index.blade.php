@extends('layouts.app')
@section('title', 'Permission Master Management')
@section('page_title', 'Capability Permissions Directory')
@section('page_subtitle', 'Manage granular system authorizations, module capabilities, and role mappings')

@section('topbar_actions')
    @can('permissionmaster.edit')
        <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
            <i class="bi bi-key-fill"></i>
            <span>Add Permission</span>
        </button>
    @endcan
    @can('rolemaster.view')
        <a href="{{ route('roles.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-shield-lock-fill text-primary"></i>
            <span>Role Master</span>
        </a>
    @endcan
@endsection

@section('summary_cards')
@php
    $totalPerms = $totalCount ?? 0;
    $totalMods = is_countable($allModules) ? count($allModules) : 0;
    $currentMod = request('module');
@endphp
<div class="row g-3">
    <!-- 1. Total Permissions -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-permissions.svg" width="46" height="46" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalPerms) }}</div>
                        <div class="saas-stat-label">Total permissions</div>
                    </div>
                </div>
                <span class="saas-stat-badge slate">Policies</span>
            </div>
        </div>
    </div>

    <!-- 2. Security Modules -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/security-modules.svg" width="46" height="46" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalMods) }}</div>
                        <div class="saas-stat-label">Security modules</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Core domain</span>
            </div>
        </div>
    </div>

    <!-- 3. Active Scope -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/filtered-scope.svg" width="46" height="46" alt="">
                    <div>
                        <div class="saas-stat-value fs-5 text-truncate" style="max-width: 140px;">{{ $currentMod ? ucfirst($currentMod) : 'All' }}</div>
                        <div class="saas-stat-label">Filtered scope</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Module filter</span>
            </div>
        </div>
    </div>

    <!-- 4. Auth Protocol -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/rbac.svg" width="46" height="46" alt="">
                    <div>
                        <div class="saas-stat-value fs-5">RBAC</div>
                        <div class="saas-stat-label">Gate policy</div>
                    </div>
                </div>
                <span class="saas-stat-badge indigo">Protected</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <!-- Left: Filter Pills (Top 4 Modules) -->
    <div class="saas-filter-pills">
        <a href="{{ route('permissions.index', array_merge(request()->except(['module', 'page']))) }}"
           class="saas-pill-btn {{ !request('module') ? 'active' : '' }}">
            <span>All modules</span>
            <span class="pill-counter">{{ $totalCount }}</span>
        </a>
        @foreach(collect($allModules)->take(4) as $m)
            <a href="{{ route('permissions.index', array_merge(request()->except(['page']), ['module' => $m])) }}"
               class="saas-pill-btn {{ request('module') === $m ? 'active' : '' }}">
                <span>{{ $m }}</span>
                <span class="pill-counter">{{ $moduleCounts[$m] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <!-- Right: Search & Filter -->
    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('permissions.index') }}" class="d-flex align-items-center gap-2 m-0" id="permissionSearchForm">
            @if(request('module'))
                <input type="hidden" name="module" value="{{ request('module') }}">
            @endif

            <div class="saas-search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" class="saas-search-input" placeholder="Search capability, slug key...">
            </div>

            <button type="submit" class="saas-btn-filter">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            @if(request()->filled('q') || request()->filled('module'))
                <a href="{{ route('permissions.index') }}" class="saas-btn-filter text-decoration-none" title="Clear filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Permissions Table Card -->
<div class="saas-table-card">


    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Capability Title</th>
                    <th style="width: 20%;">Permission Key</th>
                    <th style="width: 15%;">Module</th>
                    <th style="width: 22%;">Description</th>
                    <th style="width: 10%;">Roles</th>
                    <th class="text-end" style="width: 8%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($permissions as $p)
                <tr>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ $p->title }}</div>
                    </td>
                    <td>
                        <code class="text-primary fw-semibold">{{ $p->name }}</code>
                    </td>
                    <td>
                        <span class="badge-modern badge-slate">
                            <i class="bi bi-folder2-open text-secondary"></i> {{ $p->module ?: 'General' }}
                        </span>
                    </td>
                    <td>
                        <div class="small text-muted line-clamp-1" style="max-width: 320px;">
                            {{ $p->description ?: 'No additional description provided.' }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 220px;">
                            @foreach($p->roles as $r)
                                <span class="badge-modern badge-indigo py-0 px-2" style="font-size: 0.72rem;">
                                    {{ $r->display_name ?: $r->name }}
                                </span>
                            @endforeach
                            @if($p->roles->isEmpty())
                                <span class="badge-modern badge-slate py-0 px-2 text-muted" style="font-size: 0.72rem;">Unassigned</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-2 align-items-center">
                            @can('permissionmaster.edit')
                                <button type="button" class="btn-action-edit-pill" title="Edit permission"
                                        data-bs-toggle="modal" data-bs-target="#editPermissionModal"
                                        data-id="{{ $p->id }}"
                                        data-name="{{ $p->name }}"
                                        data-display-name="{{ $p->display_name ?: $p->title }}"
                                        data-module="{{ $p->module }}"
                                        data-description="{{ $p->description }}"
                                        data-is-system="{{ $p->is_system ? '1' : '0' }}"
                                        onclick="openEditPermissionModal(this)">
                                    <i class="bi bi-pencil-fill"></i>
                                    <span>Edit</span>
                                </button>
                                @if(!$p->is_system)
                                    <form method="POST" action="{{ route('permissions.destroy', $p) }}" class="d-inline"
                                          data-confirm="Are you sure you want to delete permission: {{ $p->title }} ({{ $p->name }})?">
                                        @csrf @method('DELETE')
                                        <button class="btn-action-delete-pill" type="submit" title="Delete permission">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-eye"></i> View Only
                                </span>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-modern py-4">
                            <div class="empty-icon-bubble mb-3">
                                <i class="bi bi-key text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No permissions found</h5>
                            <p class="text-muted small mb-3">No capability permissions match the selected criteria.</p>
                            @can('permissionmaster.edit')
                                <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                                    <i class="bi bi-key-fill"></i> Add Permission
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
    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} permissions
        </div>
        <div class="saas-pagination">
            {{ $permissions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- ADD PERMISSION MODAL (Without Scroll) -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('permissions.store') }}" novalidate>
                @csrf
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble cyan p-2 rounded-3">
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
                            <input type="text" name="module" id="add_perm_module" list="modulesDatalist" class="form-control form-control-modern" placeholder="e.g. Plant Master, Visitor Directory" required maxlength="50">
                            <datalist id="modulesDatalist">
                                @foreach($allModules as $mod)
                                    <option value="{{ $mod }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_perm_title">Capability Title</label>
                            <input type="text" name="display_name" id="add_perm_title" class="form-control form-control-modern" placeholder="e.g. View Plant Tour Records" required maxlength="80">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_perm_slug">Permission Key (Slug)</label>
                            <input type="text" name="name" id="add_perm_slug" class="form-control form-control-modern font-monospace" placeholder="e.g. plantmaster.view" required maxlength="60">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Lowercase letters, numbers, dots, hyphens</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_perm_desc">Description / Scope</label>
                            <input type="text" name="description" id="add_perm_desc" class="form-control form-control-modern" placeholder="Grants authorization to inspect tour history" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-key-fill me-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT PERMISSION MODAL (Without Scroll) -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="editPermissionForm" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble emerald p-2 rounded-3">
                            <i class="bi bi-pencil-square text-emerald-600 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="editPermissionModalLabel">Edit Capability Permission</h5>
                            <p class="text-muted small mb-0">Update capability display title, module category, or description</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="edit_perm_module">System Module</label>
                            <input type="text" name="module" id="edit_perm_module" list="modulesDatalist" class="form-control form-control-modern" required maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="edit_perm_title">Capability Title</label>
                            <input type="text" name="display_name" id="edit_perm_title" class="form-control form-control-modern" required maxlength="80">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="edit_perm_slug">Permission Key (Slug)</label>
                            <input type="text" name="name" id="edit_perm_slug" class="form-control form-control-modern font-monospace" required maxlength="60">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Core permission keys are referenced in authorization gates</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_perm_desc">Description / Scope</label>
                            <input type="text" name="description" id="edit_perm_desc" class="form-control form-control-modern" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditPermissionModal(btn) {
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('editPermissionForm');
        form.action = `/permissions/${id}`;
        document.getElementById('edit_perm_module').value = btn.getAttribute('data-module') || '';
        document.getElementById('edit_perm_title').value = btn.getAttribute('data-display-name') || '';
        document.getElementById('edit_perm_slug').value = btn.getAttribute('data-name') || '';
        document.getElementById('edit_perm_desc').value = btn.getAttribute('data-description') || '';

        const isSystem = btn.getAttribute('data-is-system') === '1';
        document.getElementById('edit_perm_slug').readOnly = isSystem;
    }
</script>
@endpush
