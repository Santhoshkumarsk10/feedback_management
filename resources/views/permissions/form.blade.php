@extends('layouts.app')
@section('title', $permission->exists ? 'Edit Permission: '.$permission->title : 'Register New Permission')
@section('page_title', $permission->exists ? 'Edit Capability Permission' : 'Register System Permission')
@section('page_subtitle', $permission->exists ? 'Modify permission metadata, descriptive scope, and role assignments' : 'Define a new capability permission and assign it to system roles')

@section('content')
<div class="mb-3">
    <a href="{{ route('permissions.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Permissions Directory
    </a>
</div>

<form method="POST" action="{{ $permission->exists ? route('permissions.update', $permission) : route('permissions.store') }}" id="permissionForm" novalidate>
    @csrf
    @if($permission->exists)
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card-modern mb-4" style="border-left: 4px solid var(--shibaura-blue);">
                <div class="card-header bg-white">
                    <div class="card-title">
                        <i class="bi bi-key-fill text-primary"></i>
                        <span>{{ $permission->exists ? 'Modify Permission Metadata' : 'New Permission Definition' }}</span>
                    </div>
                    @if($permission->exists)
                        <code class="badge-modern badge-indigo">{{ $permission->name }}</code>
                    @endif
                </div>

                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <!-- Permission Display Title -->
                        <div class="col-md-6">
                            <label class="form-modern-label" for="perm_display_name">Display Title <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-bookmark-fill"></i></span>
                                <input name="display_name" 
                                       id="perm_display_name"
                                       class="form-control form-control-modern border-start-0 @error('display_name') is-invalid @enderror" 
                                       value="{{ old('display_name', $permission->display_name ?: $permission->title) }}" 
                                       placeholder="e.g. Manage Shift Schedules" 
                                       required 
                                       minlength="2"
                                       maxlength="80">
                            </div>
                            <div class="form-text text-muted small mt-1">Human-friendly capability label</div>
                            @error('display_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Permission Key (Slug) -->
                        <div class="col-md-6">
                            <label class="form-modern-label" for="perm_name">Permission Slug Key <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-hash"></i></span>
                                <input name="name" 
                                       id="perm_name"
                                       class="form-control form-control-modern border-start-0 @error('name') is-invalid @enderror font-monospace" 
                                       value="{{ old('name', $permission->name) }}" 
                                       placeholder="e.g. manage-shifts" 
                                       required 
                                       minlength="3"
                                       maxlength="60"
                                       {{ $permission->exists ? 'readonly' : '' }}>
                            </div>
                            <div class="form-text text-muted small mt-1">Machine-readable key used in @can & middleware (e.g. manage-shifts)</div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Module Group -->
                        <div class="col-md-12">
                            <label class="form-modern-label" for="perm_module">Functional Module Group <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-folder2-open"></i></span>
                                <input list="moduleOptions" 
                                       name="module" 
                                       id="perm_module"
                                       class="form-control form-control-modern border-start-0 @error('module') is-invalid @enderror" 
                                       value="{{ old('module', $permission->module ?: 'System Masters') }}" 
                                       placeholder="e.g. System Masters, Operations & Visits, Analytics & Insights" 
                                       required>
                                <datalist id="moduleOptions">
                                    @foreach($existingModules as $m)
                                        <option value="{{ $m }}">
                                    @endforeach
                                    <option value="Platform Access">
                                    <option value="System Masters">
                                    <option value="Operations & Visits">
                                    <option value="Analytics & Insights">
                                    <option value="Brand & Content">
                                    <option value="Security & Audits">
                                </datalist>
                            </div>
                            <div class="form-text text-muted small mt-1">Select an existing module or type a new one to group capabilities</div>
                            @error('module')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label class="form-modern-label" for="perm_description">Capability Description</label>
                            <textarea name="description" 
                                      id="perm_description"
                                      rows="3" 
                                      class="form-control form-control-modern @error('description') is-invalid @enderror" 
                                      placeholder="e.g. Grants access to configure factory shift timings, create presets, and toggle active shift status" 
                                      maxlength="255">{{ old('description', $permission->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Assignment Box -->
            <div class="card-modern mb-4">
                <div class="card-header bg-white">
                    <div class="card-title">
                        <i class="bi bi-shield-lock text-primary"></i>
                        <span>Assign to Roles</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <p class="small text-muted mb-3">
                        Choose which user roles should inherently possess this capability:
                    </p>

                    <div class="row g-3">
                        @foreach($roles as $r)
                            @php
                                $isSuper = $r->name === 'superadmin';
                                $isAssigned = $isSuper || in_array($r->id, old('roles', $assignedRoleIds ?? []));
                            @endphp
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $r->display_name ?: $r->name }}</div>
                                        <code class="fs-xs text-muted">{{ $r->name }}</code>
                                    </div>
                                    <div class="form-check form-switch fs-5 m-0">
                                        @if($isSuper)
                                            <input class="form-check-input" type="checkbox" checked disabled title="SuperAdmin inherently possesses all permissions">
                                        @else
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="roles[]" 
                                                   value="{{ $r->id }}" 
                                                   id="role_toggle_{{ $r->id }}"
                                                   @checked($isAssigned)>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn-modern-primary">
                    <i class="bi bi-check-lg"></i> {{ $permission->exists ? 'Save Changes' : 'Register Permission' }}
                </button>
                <a href="{{ route('permissions.index') }}" class="btn-modern-secondary">
                    Cancel
                </a>
            </div>
        </div>

        <!-- Help Column -->
        <div class="col-lg-4">
            <div class="card-modern mb-4">
                <div class="card-header bg-white">
                    <div class="card-title">
                        <i class="bi bi-info-circle-fill text-info"></i>
                        <span>Permission Standards</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold fs-6 mb-2">Naming Conventions</h6>
                    <ul class="small text-muted ps-3 mb-3">
                        <li class="mb-1">Use kebab-case: <code>verb-resource</code> (e.g. <code>view-visits</code>, <code>export-reports</code>).</li>
                        <li class="mb-1">Keep keys unique across all guards.</li>
                        <li class="mb-1">Group permissions into intuitive functional modules.</li>
                    </ul>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        Once created, permissions can be checked in Blade using <code>@@can('permission-key')</code> or in routes using <code>->middleware('permission:key')</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
