@extends('layouts.app')
@section('title', 'Users Management')
@section('page_title', 'User Directory & Roles')
@section('page_subtitle', 'Manage administrators, supervisors, and plant tour organizers')

@section('topbar_actions')
    <a href="{{ route('users.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-person-plus-fill"></i>
        <span>Add New User</span>
    </a>
@endsection

@section('content')
@php
    $roleBadges = [
        'superadmin' => 'badge-indigo',
        'admin' => 'badge-amber',
        'organizer' => 'badge-emerald'
    ];
@endphp

<!-- Filter Tabs & Search Controls -->
<div class="filter-card-wrapper">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('users.index', array_merge(request()->except(['role', 'page']))) }}"
               class="filter-tab-btn {{ !request('role') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>All Roles</span>
                <span class="filter-tab-badge">{{ $totalUsers }}</span>
            </a>
            @foreach($roles as $r)
                @php
                    $roleIcon = match($r) {
                        'superadmin' => 'bi-shield-shaded',
                        'admin' => 'bi-person-gear',
                        'organizer' => 'bi-person-badge',
                        default => 'bi-person'
                    };
                @endphp
                <a href="{{ route('users.index', array_merge(request()->except(['page']), ['role' => $r])) }}"
                   class="filter-tab-btn {{ request('role') === $r ? 'active' : '' }}">
                    <i class="bi {{ $roleIcon }}"></i>
                    <span>{{ ucfirst($r) }}s</span>
                    <span class="filter-tab-badge">{{ $roleCounts[$r] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">
                <i class="bi bi-list-check"></i> 10 per page
            </span>
        </div>
    </div>

    <!-- Filter Controls Bar -->
    <div class="filter-controls-body">
        <form method="GET" action="{{ route('users.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search by name, email, department, mobile..." 
                :suggestions="$userSuggestions ?? []"
                header-title="User Directory Suggestions"
            />

            <x-custom-select 
                name="plant_id" 
                :value="request('plant_id')" 
                placeholder="All Facilities / Plants" 
                search-placeholder="Search facilities & codes..." 
                icon="bi-buildings" 
                min-width="210px" 
                :options="$plants->map(fn($pl) => ['value' => $pl->id, 'label' => $pl->name, 'code' => $pl->code])" 
                auto-submit
            />

            <x-custom-select 
                name="status" 
                :value="request('status')" 
                placeholder="All Statuses" 
                search-placeholder="Search status..." 
                icon="bi-check-circle" 
                min-width="145px" 
                :options="[
                    ['value' => 'active', 'label' => 'Active Only', 'dot' => 'active'],
                    ['value' => 'inactive', 'label' => 'Inactive Only', 'dot' => 'inactive'],
                ]" 
                auto-submit
            />

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['role', 'plant_id', 'q', 'status']))
                <a href="{{ route('users.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-people-fill text-primary"></i>
            <span>Registered Team Members</span>
        </div>
        <span class="badge-modern badge-slate">{{ $users->total() }} Users Found</span>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Assigned Plant</th>
                    <th>Department</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="user-avatar-chip">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </span>
                            <div>
                                <div class="fw-bold text-dark">{{ $u->name }}</div>
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
                        @if($u->plant)
                            <span class="badge-modern badge-shibaura" title="{{ $u->plant->name }}">
                                <i class="bi bi-buildings"></i> {{ $u->plant->code }}
                            </span>
                            <div class="small text-muted mt-0 text-truncate" style="max-width: 180px; font-size: 0.72rem;">{{ $u->plant->name }}</div>
                        @else
                            <span class="badge-modern badge-slate">HQ / General</span>
                        @endif
                    </td>
                    <td>
                        @if($u->department)
                            <span class="badge-modern badge-slate">{{ $u->department }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="small text-dark">
                            <i class="bi bi-envelope text-muted me-1"></i> {{ $u->email ?? '—' }}
                        </div>
                        @if($u->mobile)
                            <div class="small text-muted mt-0">
                                <i class="bi bi-telephone text-muted me-1"></i> {{ $u->mobile }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($u->is_active)
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
                            <a href="{{ route('users.edit', $u) }}" class="btn-action-icon edit" title="Edit user">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn-action-icon" type="submit" title="{{ $u->is_active ? 'Deactivate user' : 'Activate user' }}">
                                        <i class="bi {{ $u->is_active ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }} fs-5"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-icon delete" type="submit" title="Delete user">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-search fs-2 d-block mb-2 text-slate-300"></i>
                        No users found matching your search.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-container">
    <div class="small text-muted">
        Showing <span class="fw-bold text-dark">{{ $users->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $users->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $users->total() }}</span> users (10 per page)
    </div>
    <div>
        {{ $users->links() }}
    </div>
</div>
@endsection
