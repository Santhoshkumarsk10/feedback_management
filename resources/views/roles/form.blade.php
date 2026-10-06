@extends('layouts.app')
@section('title', $role->exists ? 'Edit Role: '.$role->name : 'Create New Role')
@section('page_title', $role->exists ? 'Edit Access Role' : 'Create Access Role')
@section('page_subtitle', $role->exists ? 'Modify role metadata, descriptive responsibilities, and access status' : 'Define a new organizational role for plant tour guides or supervisors')

@section('content')
<div class="mb-3">
    <a href="{{ route('roles.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Role List
    </a>
</div>

<div class="row g-4">
    <!-- Main Role Form Column -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-shield-lock-fill text-primary"></i>
                    <span>{{ $role->exists ? 'Role Specification' : 'New Role Configuration' }}</span>
                </div>
                @if($role->exists)
                    <span class="badge-modern {{ $role->is_system ? 'badge-shibaura' : 'badge-slate' }}">
                        {{ $role->slug }}
                    </span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}">
                    @csrf
                    @if($role->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-modern-label">Role Title / Display Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-badge"></i></span>
                                <input name="name" class="form-control form-control-modern border-start-0" value="{{ old('name', $role->name) }}" placeholder="e.g. Quality Supervisor" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">
                                Role Slug / Identifier 
                                @if($role->is_system)
                                    <small class="text-muted fw-normal">(Fixed for core system)</small>
                                @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-hash"></i></span>
                                <input name="slug" class="form-control form-control-modern border-start-0" value="{{ old('slug', $role->slug) }}" placeholder="e.g. quality_supervisor" {{ $role->is_system ? 'readonly' : '' }}>
                            </div>
                            <div class="small text-muted mt-1">Machine-readable key (auto-generated if left blank)</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-modern-label">Role Description & Responsibilities</label>
                            <textarea name="description" rows="3" class="form-control form-control-modern" placeholder="e.g. Responsible for escorting customers during injection molding trials and gathering evaluations">{{ old('description', $role->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    @if($role->slug !== 'superadmin')
                        <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Active Assignment Status</div>
                                    <div class="small text-muted">Active roles can be assigned to team members and administrators.</div>
                                </div>
                                <div class="form-check form-switch fs-5 m-0">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="role_act" @checked(old('is_active', $role->is_active ?? true))>
                                </div>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="is_active" value="1">
                    @endif

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $role->exists ? 'Save Changes' : 'Create Role' }}
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Info Sidebar -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-shield-check text-primary"></i>
                    <span>Access Control</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon-bubble shibaura" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">User Assignment</h6>
                        <p class="small text-muted mb-0">Once saved, this role will appear in the User registration and profile management dropdowns.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="stat-icon-bubble amber" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">System Roles Protection</h6>
                        <p class="small text-muted mb-0">Core system roles (Super Admin, Plant Admin, Tour Organizer) are protected from accidental removal.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
