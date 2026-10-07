@extends('layouts.app')
@section('title', $role->exists ? 'Edit Role: '.($role->display_name ?: $role->name) : 'Create New Role')
@section('page_title', $role->exists ? 'Edit Access Role & Permissions' : 'Create Access Role')
@section('page_subtitle', $role->exists ? 'Modify role metadata, descriptive responsibilities, and capability permissions' : 'Define a new organizational role and assign fine-grained capability permissions')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('roles.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Role List
    </a>
    <a href="{{ route('permissions.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-key-fill text-primary"></i> View All Permissions
    </a>
</div>

<form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" id="roleForm" novalidate>
    @csrf
    @if($role->exists)
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Main Role Form Column -->
        <div class="col-lg-8">
            <div class="card-modern mb-4" style="border-left: 4px solid var(--shibaura-blue);">
                <div class="card-header bg-white">
                    <div class="card-title">
                        <i class="bi bi-shield-lock-fill text-primary"></i>
                        <span>{{ $role->exists ? 'Role Specification' : 'New Role Configuration' }}</span>
                    </div>
                    @if($role->exists)
                        <span class="badge-modern {{ $role->is_system ? 'badge-shibaura' : 'badge-slate' }}">
                            {{ $role->name }}
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-modern-label" for="role_name">Role Title / Display Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-badge"></i></span>
                                <input name="name" id="role_name" 
                                       class="form-control form-control-modern @error('name') is-invalid @enderror border-start-0" 
                                       value="{{ old('name', $role->display_name ?: $role->name) }}" 
                                       placeholder="e.g. Operations Supervisor" minlength="2" maxlength="70" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-1">Human-readable job or responsibility title</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="role_slug">
                                Role Slug / Identifier 
                                @if($role->is_system)
                                    <small class="text-muted fw-normal">(Fixed for core system)</small>
                                @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-hash"></i></span>
                                <input name="slug" id="role_slug" 
                                       class="form-control form-control-modern @error('slug') is-invalid @enderror border-start-0" 
                                       value="{{ old('slug', $role->name) }}" 
                                       placeholder="e.g. operations_supervisor" maxlength="50" 
                                       {{ $role->is_system ? 'readonly' : '' }}>
                            </div>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-1">Unique machine-readable identifier (auto-generated if empty)</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-modern-label" for="role_description">Role Description & Responsibilities</label>
                            <textarea name="description" id="role_description" rows="2" 
                                      class="form-control form-control-modern @error('description') is-invalid @enderror" 
                                      placeholder="e.g. Responsible for escorting customers during injection molding trials and gathering evaluations" 
                                      maxlength="500">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    @if($role->name !== 'superadmin')
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
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
                </div>
            </div>

            <!-- Permission Allocation Matrix -->
            <div class="card-modern">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <i class="bi bi-key-fill text-primary"></i>
                        <span>Capability Permissions Matrix</span>
                    </div>
                    @if($role->name !== 'superadmin')
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3" id="btnSelectAllPerms">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" id="btnDeselectAllPerms">
                                <i class="bi bi-x"></i> Deselect All
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if($role->name === 'superadmin')
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-0">
                            <i class="bi bi-shield-check fs-2 text-primary"></i>
                            <div>
                                <div class="fw-bold">Full Administrative Privileges</div>
                                <div class="small text-muted">The Super Administrator role inherently possesses all current and future capabilities across the platform. Permissions are permanently enabled.</div>
                            </div>
                        </div>
                    @else
                        <p class="small text-muted mb-4">
                            Select the operational privileges and capabilities granted to users assigned this role:
                        </p>

                        <div class="d-flex flex-column gap-4">
                            @foreach($groupedPermissions as $module => $perms)
                                <div class="border rounded-3 p-3 bg-white shadow-xs module-perm-block">
                                    <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-folder2-open text-primary fs-5"></i>
                                            <span class="fw-bold text-dark fs-6">{{ $module }}</span>
                                            <span class="badge-modern badge-slate py-0 px-2 fs-xs">{{ count($perms) }} Capabilities</span>
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none module-toggle-btn" style="font-size: 0.8rem;">
                                            Toggle All in Group
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        @foreach($perms as $p)
                                            @php
                                                $isChecked = in_array($p->name, old('permissions', $rolePermissions ?? []));
                                            @endphp
                                            <div class="col-md-6">
                                                <div class="p-2 rounded-2 border bg-light-subtle h-100 perm-card d-flex gap-2 align-items-start">
                                                    <div class="form-check mt-1">
                                                        <input class="form-check-input perm-checkbox" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $p->name }}" 
                                                               id="perm_{{ $p->id }}"
                                                               @checked($isChecked)>
                                                    </div>
                                                    <label class="form-check-label flex-grow-1 cursor-pointer" for="perm_{{ $p->id }}">
                                                        <div class="fw-semibold text-dark small">{{ $p->title }}</div>
                                                        <code class="text-primary fs-xs d-block">{{ $p->name }}</code>
                                                        @if($p->description)
                                                            <div class="text-muted fs-xs mt-1" style="line-height: 1.3;">
                                                                {{ $p->description }}
                                                            </div>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2 mt-4">
                <button type="submit" class="btn-modern-primary">
                    <i class="bi bi-check-lg"></i> {{ $role->exists ? 'Save Changes' : 'Create Role' }}
                </button>
                <a href="{{ route('roles.index') }}" class="btn-modern-secondary">
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
                        <span>Role Design Guidelines</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold fs-6 mb-2">Role Best Practices</h6>
                    <ul class="small text-muted ps-3 mb-3">
                        <li class="mb-1"><strong>Staff:</strong> Restrict to viewing/managing visitors, gathering reviews, and tablet mobile app.</li>
                        <li class="mb-1"><strong>Supervisor:</strong> Grant analytics reports, export, and visitor history. Avoid granting system master controls.</li>
                        <li class="mb-1"><strong>Admin:</strong> Responsible for plant operations, user provisioning, shifts, and questionnaires.</li>
                    </ul>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        Permissions take effect immediately for all active users assigned this role.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All / Deselect All
    const selectAllBtn = document.getElementById('btnSelectAllPerms');
    const deselectAllBtn = document.getElementById('btnDeselectAllPerms');
    const allCheckboxes = document.querySelectorAll('.perm-checkbox');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => cb.checked = true);
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => cb.checked = false);
        });
    }

    // Toggle Group
    document.querySelectorAll('.module-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const block = this.closest('.module-perm-block');
            const checkboxes = block.querySelectorAll('.perm-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        });
    });
});
</script>
@endpush
