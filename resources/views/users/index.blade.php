@extends('layouts.app')
@section('title', 'Users Management')
@section('page_title', 'User Directory & Roles')
@section('page_subtitle', 'Manage administrators, supervisors, and plant tour organizers')

@section('topbar_actions')
    @can('userdirectory.create')
        <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill"></i>
            <span>Add User</span>
        </button>
    @endcan
@endsection

@section('summary_cards')
<div class="row g-3">
    <!-- 1. Total Users -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-users.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalUsers) }}</div>
                        <div class="saas-stat-label">Total Coordinators</div>
                    </div>
                </div>
                <span class="saas-stat-badge slate">Team members</span>
            </div>
        </div>
    </div>

    <!-- 2. Super Admins -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/system-roles.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($roleCounts['superadmin'] ?? 0) }}</div>
                        <div class="saas-stat-label">Super admins</div>
                    </div>
                </div>
                <span class="saas-stat-badge indigo">Root access</span>
            </div>
        </div>
    </div>

    <!-- 3. Administrators -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/administrators.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($roleCounts['admin'] ?? 0) }}</div>
                        <div class="saas-stat-label">Administrators</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Branch ops</span>
            </div>
        </div>
    </div>

    <!-- 4. Organizers & Staff -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/tour-staff.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format(($roleCounts['organizer'] ?? 0) + ($roleCounts['supervisor'] ?? 0) + ($roleCounts['staff'] ?? 0)) }}</div>
                        <div class="saas-stat-label">Tour staff</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Tour guide</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@php
    $roleBadges = [
        'superadmin' => 'badge-indigo',
        'admin' => 'badge-amber',
        'organizer' => 'badge-emerald'
    ];
@endphp

<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <!-- Left: Filter Pills -->
    <div class="saas-filter-pills">
        <a href="{{ route('users.index', array_merge(request()->except(['role', 'page']))) }}"
           class="saas-pill-btn {{ !request('role') ? 'active' : '' }}">
            <span>All users</span>
            <span class="pill-counter">{{ $totalUsers }}</span>
        </a>
        @foreach($roles as $r)
            <a href="{{ route('users.index', array_merge(request()->except(['page']), ['role' => $r])) }}"
               class="saas-pill-btn {{ request('role') === $r ? 'active' : '' }}">
                <span>{{ ucfirst($r) }}s</span>
                <span class="pill-counter">{{ $roleCounts[$r] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <!-- Right: Search & Filter -->
    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center gap-2 m-0" id="userSearchForm">
            @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif

            <div class="saas-search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" class="saas-search-input" placeholder="Search by name, email, department...">
            </div>

            <button type="submit" class="saas-btn-filter">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['role', 'q', 'status']))
                <a href="{{ route('users.index') }}" class="saas-btn-filter text-decoration-none" title="Clear filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="saas-table-card">


    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 28%;">User</th>
                    <th style="width: 18%;">Role</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 20%;">Contact</th>
                    <th style="width: 8%;">Status</th>
                    <th class="text-end" style="width: 8%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-chip">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </span>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.92rem;">{{ $u->name }}</div>
                                <div class="small text-muted">ID: #{{ $u->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-modern {{ $roleBadges[$u->role] ?? 'badge-slate' }}">
                            <i class="bi bi-shield-shaded"></i> {{ $u->roleModel?->name ?? ucfirst($u->role) }}
                        </span>
                    </td>
                    <td>
                        @if($u->department)
                            <span class="badge-modern badge-slate">{{ $u->department }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="small text-dark mb-1">
                            <i class="bi bi-envelope text-muted me-1"></i> {{ $u->email ?? '—' }}
                        </div>
                        @if($u->mobile)
                            <div class="small text-muted">
                                <i class="bi bi-telephone text-muted me-1"></i> {{ $u->mobile }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <label class="saas-status-switch m-0" title="Click to toggle status">
                                    <input type="checkbox" class="saas-switch-input" {{ $u->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="saas-switch-slider"></span>
                                    <span class="saas-switch-label {{ $u->is_active ? '' : 'inactive' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </form>
                        @else
                            <span class="saas-switch-label"><i class="bi bi-person-check-fill"></i> Current User</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-2 align-items-center">
                            @can('userdirectory.edit')
                                <button type="button" class="btn-action-edit-pill" title="Edit user"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                                        data-id="{{ $u->id }}"
                                        data-name="{{ $u->name }}"
                                        data-email="{{ $u->email }}"
                                        data-role-id="{{ $u->role_id ?? ($roleModels->firstWhere('slug', $u->role)?->id ?? '') }}"
                                        data-mobile="{{ $u->mobile }}"
                                        data-department="{{ $u->department }}"
                                        data-is-active="{{ $u->is_active ? '1' : '0' }}"
                                        data-is-self="{{ $u->id === auth()->id() ? '1' : '0' }}"
                                        onclick="openEditUserModal(this)">
                                    <i class="bi bi-pencil-fill"></i>
                                    <span>Edit</span>
                                </button>
                            @endcan

                            @can('userdirectory.delete')
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                                          data-confirm="Are you sure you want to delete user '{{ $u->name }}'?">
                                        @csrf @method('DELETE')
                                        <button class="btn-action-delete-pill" type="submit" title="Delete user">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-modern py-4">
                            <div class="empty-icon-bubble mb-3">
                                <i class="bi bi-people text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No users found</h5>
                            <p class="text-muted small mb-3">No user accounts match your search criteria.</p>
                            @can('userdirectory.create')
                                <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Add User</span>
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
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
        </div>
        <div class="saas-pagination">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- ADD USER MODAL (Without Scroll) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble cyan p-2 rounded-3">
                            <i class="bi bi-person-plus-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="addUserModalLabel">Add New User</h5>
                            <p class="text-muted small mb-0">Register an administrator or team member account</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_user_name">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="add_user_name" class="form-control form-control-modern border-start-0" placeholder="e.g. Ramesh Kumar" required maxlength="70">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_user_role">Access Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <select name="role_id" id="add_user_role" class="form-select form-control-modern border-start-0" required>
                                    <option value="" disabled selected>Select Role...</option>
                                    @foreach($roleModels as $rm)
                                        <option value="{{ $rm->id }}">{{ $rm->display_name ?: $rm->name }} ({{ $rm->slug }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_user_email">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="add_user_email" class="form-control form-control-modern border-start-0" placeholder="name@shibaura.test" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_user_mobile">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="mobile" id="add_user_mobile" class="form-control form-control-modern border-start-0" placeholder="e.g. 9844268120" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_user_department">Department / Division</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input type="text" name="department" id="add_user_department" class="form-control form-control-modern border-start-0" placeholder="e.g. Operations, Quality" maxlength="60">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="add_user_password">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" id="add_user_password" class="form-control form-control-modern border-start-0" placeholder="••••••••" required minlength="6">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold text-dark small d-block">Active Login Status</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">Enable or disable immediate sign-in authorization</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add_user_is_active" checked style="width: 2.2em; height: 1.2em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT USER MODAL (Without Scroll) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="editUserForm" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble emerald p-2 rounded-3">
                            <i class="bi bi-person-gear text-emerald-600 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="editUserModalLabel">Edit User Account</h5>
                            <p class="text-muted small mb-0">Modify profile parameters and access permissions</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small d-none" id="edit_user_self_notice">
                        <i class="bi bi-info-circle me-1"></i> You are editing your own profile. Your role and active status are locked to prevent accidental lockout.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="edit_user_name">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="edit_user_name" class="form-control form-control-modern border-start-0" required maxlength="70">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label required" for="edit_user_role_id">Access Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <select name="role_id" id="edit_user_role_id" class="form-select form-control-modern border-start-0" required>
                                    @foreach($roleModels as $rm)
                                        <option value="{{ $rm->id }}">{{ $rm->display_name ?: $rm->name }} ({{ $rm->slug }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_user_email">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="edit_user_email" class="form-control form-control-modern border-start-0" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_user_mobile">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="mobile" id="edit_user_mobile" class="form-control form-control-modern border-start-0" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_user_department">Department / Division</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input type="text" name="department" id="edit_user_department" class="form-control form-control-modern border-start-0" maxlength="60">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_user_password">New Password <small class="text-muted fw-normal">(Optional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" id="edit_user_password" class="form-control form-control-modern border-start-0" placeholder="Leave blank to keep unchanged" minlength="6">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold text-dark small d-block">Active Login Status</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">Enable or disable immediate sign-in authorization</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_user_is_active" style="width: 2.2em; height: 1.2em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditUserModal(btn) {
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('editUserForm');
        form.action = `/users/${id}`;
        document.getElementById('edit_user_name').value = btn.getAttribute('data-name') || '';
        document.getElementById('edit_user_email').value = btn.getAttribute('data-email') || '';
        document.getElementById('edit_user_role_id').value = btn.getAttribute('data-role-id') || '';
        document.getElementById('edit_user_mobile').value = btn.getAttribute('data-mobile') || '';
        document.getElementById('edit_user_department').value = btn.getAttribute('data-department') || '';
        document.getElementById('edit_user_is_active').checked = btn.getAttribute('data-is-active') === '1';
        document.getElementById('edit_user_password').value = '';

        const isSelf = btn.getAttribute('data-is-self') === '1';
        document.getElementById('edit_user_role_id').disabled = isSelf;
        document.getElementById('edit_user_is_active').disabled = isSelf;
        const selfNotice = document.getElementById('edit_user_self_notice');
        if (selfNotice) {
            selfNotice.classList.toggle('d-none', !isSelf);
        }
    }
</script>
@endpush

