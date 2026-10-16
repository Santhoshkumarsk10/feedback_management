@extends('layouts.app')
@section('title', 'Permission Master Management')
@section('page_title', 'Capability Permissions Directory')
@section('page_subtitle', 'Manage granular system authorizations, module capabilities, and role mappings')

@section('topbar_actions')
    @can('permissionmaster.edit')
        <a href="{{ route('permissions.create') }}" class="btn-modern-primary btn-sm">
            <i class="bi bi-key-fill"></i>
            <span>Add Permission</span>
        </a>
    @endcan
    @can('rolemaster.view')
        <a href="{{ route('roles.index') }}" class="btn-modern-secondary btn-sm">
            <i class="bi bi-shield-lock-fill text-primary"></i>
            <span>Role Master</span>
        </a>
    @endcan
@endsection

@section('content')
<!-- Filter Controls Card -->
<div class="filter-card-wrapper">
    <div class="filter-controls-body rounded-3">
        <form method="GET" action="{{ route('permissions.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            <x-search-suggest 
                name="q" 
                placeholder="Search capability title, permission slug key, or description..." 
                :suggestions="$permissionSuggestions ?? []"
                header-title="Permission Directory Suggestions"
            />

            <x-custom-select 
                name="module" 
                :value="request('module')" 
                placeholder="All Modules" 
                search-placeholder="Filter by module..." 
                icon="bi-folder2-open" 
                min-width="230px" 
                :options="collect($allModules)->map(fn($m) => [
                    'value' => $m, 
                    'label' => $m,
                    'sub' => ($moduleCounts[$m] ?? 0) . ' perms',
                ])->all()" 
                auto-submit
            />

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->filled('q') || request()->filled('module'))
                <a href="{{ route('permissions.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif

            <div class="ms-auto d-none d-md-flex align-items-center gap-2">
                @if(request()->filled('module'))
                    <span class="badge-modern badge-shibaura">
                        <i class="bi bi-folder2-open me-1"></i> {{ request('module') }}
                    </span>
                @endif
                <span class="badge-modern badge-slate">
                    <i class="bi bi-key-fill text-primary me-1"></i> {{ $permissions->total() }} of {{ $totalCount }} Capabilities
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Permissions Table Card -->
<div class="card-modern">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-shield-check text-primary"></i>
            <span>System Capability Permissions</span>
            @if(request()->filled('module'))
                <span class="badge-modern badge-shibaura ms-2" style="font-size: 0.78rem;">
                    {{ request('module') }}
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">{{ $permissions->total() }} Permissions Listed</span>
            @can('permissionmaster.edit')
                <a href="{{ route('permissions.create') }}" class="btn-modern-primary btn-sm">
                    <i class="bi bi-key-fill"></i>
                    <span>Add Permission</span>
                </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="width: 220px;">Capability Title</th>
                    <th style="width: 200px;">Permission Key (Slug)</th>
                    <th>Module Group</th>
                    <th>Functional Description</th>
                    <th>Assigned Roles</th>
                    <th class="text-end">Actions</th>
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
                        <div class="small text-muted" style="max-width: 320px;">
                            {{ $p->description ?: 'No additional description provided.' }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 260px;">
                            @foreach($p->roles as $r)
                                <span class="badge-modern badge-indigo py-0 px-2 fs-xs" title="Role: {{ $r->display_name ?: $r->name }}">
                                    {{ $r->display_name ?: $r->name }}
                                </span>
                            @endforeach
                            @if($p->roles->isEmpty())
                                <span class="badge-modern badge-slate py-0 px-2 fs-xs text-muted">Unassigned</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-1 align-items-center">
                            @can('permissionmaster.edit')
                                <a href="{{ route('permissions.edit', $p) }}" class="btn-action-icon edit" title="Edit permission">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @if(!$p->is_system)
                                    <form method="POST" action="{{ route('permissions.destroy', $p) }}" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete permission: {{ $p->title }} ({{ $p->name }})?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-action-icon delete" type="submit" title="Delete permission">
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
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-key fs-2 d-block mb-2 text-slate-300"></i>
                        <p class="mb-3">No capability permissions match the selected criteria.</p>
                        @can('permissionmaster.edit')
                            <a href="{{ route('permissions.create') }}" class="btn-modern-primary btn-sm">
                                <i class="bi bi-key-fill"></i> Add Permission
                            </a>
                        @endcan
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
        Showing <span class="fw-bold text-dark">{{ $permissions->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $permissions->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $permissions->total() }}</span> permissions (15 per page)
    </div>
    <div>
        {{ $permissions->links() }}
    </div>
</div>
@endsection
