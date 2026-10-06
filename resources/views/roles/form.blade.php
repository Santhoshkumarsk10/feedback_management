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
                <form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" id="roleForm" novalidate>
                    @csrf
                    @if($role->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-modern-label" for="role_name">Role Title / Display Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-badge"></i></span>
                                <input name="name" id="role_name" class="form-control form-control-modern @error('name') is-invalid @enderror border-start-0" value="{{ old('name', $role->name) }}" placeholder="e.g. Quality Supervisor" minlength="2" maxlength="70" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_role_name"></div>
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
                                <input name="slug" id="role_slug" class="form-control form-control-modern @error('slug') is-invalid @enderror border-start-0" value="{{ old('slug', $role->slug) }}" placeholder="e.g. quality_supervisor" maxlength="50" {{ $role->is_system ? 'readonly' : '' }}>
                            </div>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_role_slug"></div>
                            <div class="small text-muted mt-1">Machine-readable key (auto-generated if left blank)</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-modern-label" for="role_description">Role Description & Responsibilities</label>
                            <textarea name="description" id="role_description" rows="3" class="form-control form-control-modern @error('description') is-invalid @enderror" placeholder="e.g. Responsible for escorting customers during injection molding trials and gathering evaluations" maxlength="500">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_role_description"></div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('roleForm');
    if (!form) return;

    const nameInput = document.getElementById('role_name');
    const slugInput = document.getElementById('role_slug');
    const descInput = document.getElementById('role_description');

    function showLiveErr(id, msg) {
        const errEl = document.getElementById('live_err_' + id);
        const input = document.getElementById(id);
        if (errEl) {
            errEl.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${msg}`;
            errEl.classList.remove('d-none');
            errEl.classList.add('d-block');
        }
        if (input) input.classList.add('is-invalid');
    }

    function clearLiveErr(id) {
        const errEl = document.getElementById('live_err_' + id);
        const input = document.getElementById(id);
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
        if (input) input.classList.remove('is-invalid');
    }

    function bindFieldInterceptors(input, id, { allowedCharRegex = null, forbiddenCharRegex = null, stripRegex = null, maxLen = null, errMsg = '' }) {
        if (!input) return;

        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (allowedCharRegex && !allowedCharRegex.test(e.key)) {
                    e.preventDefault();
                    showLiveErr(id, errMsg);
                    return;
                }
                if (forbiddenCharRegex && forbiddenCharRegex.test(e.key)) {
                    e.preventDefault();
                    showLiveErr(id, errMsg);
                    return;
                }
                if (maxLen && this.value.length >= maxLen && this.selectionStart === this.selectionEnd) {
                    e.preventDefault();
                    return;
                }
            }
        });

        input.addEventListener('beforeinput', function(e) {
            if (e.data) {
                for (let i = 0; i < e.data.length; i++) {
                    const ch = e.data[i];
                    if (allowedCharRegex && !allowedCharRegex.test(ch)) {
                        e.preventDefault();
                        showLiveErr(id, errMsg);
                        return;
                    }
                    if (forbiddenCharRegex && forbiddenCharRegex.test(ch)) {
                        e.preventDefault();
                        showLiveErr(id, errMsg);
                        return;
                    }
                }
            }
        });

        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && stripRegex) {
                e.preventDefault();
                let cleaned = text.replace(stripRegex, '');
                if (maxLen) {
                    const avail = maxLen - (this.value.length - (this.selectionEnd - this.selectionStart));
                    if (avail > 0) cleaned = cleaned.slice(0, avail);
                    else cleaned = '';
                }
                document.execCommand('insertText', false, cleaned);
            }
        });

        input.addEventListener('input', function() {
            if (stripRegex && stripRegex.test(this.value)) {
                this.value = this.value.replace(stripRegex, '');
                showLiveErr(id, errMsg);
            } else if (this.value.trim().length >= 2 || (id === 'role_description' && this.value.trim().length === 0)) {
                clearLiveErr(id);
            }
            if (maxLen && this.value.length > maxLen) {
                this.value = this.value.slice(0, maxLen);
            }
        });
    }

    bindFieldInterceptors(nameInput, 'role_name', {
        allowedCharRegex: /^[\p{L}\p{N}\s\-–—_&/,\.()'’]$/u,
        stripRegex: /[^\p{L}\p{N}\s\-–—_&/,\.()'’]/gu,
        maxLen: 70,
        errMsg: 'Special characters like < > { } [ ] $ ^ * = \\ | are not allowed.'
    });

    bindFieldInterceptors(slugInput, 'role_slug', {
        allowedCharRegex: /^[a-zA-Z0-9_\-]$/,
        stripRegex: /[^a-zA-Z0-9_\-]/g,
        maxLen: 50,
        errMsg: 'Only letters, numbers, hyphens, and underscores are allowed.'
    });

    bindFieldInterceptors(descInput, 'role_description', {
        forbiddenCharRegex: /[<>{}\[\]$^*~=\\\|]/,
        stripRegex: /[<>{}\[\]$^*~=\\\|]/g,
        maxLen: 500,
        errMsg: 'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
    });

    form.addEventListener('submit', function(e) {
        let hasError = false;
        let firstInvalid = null;

        const nameVal = nameInput ? nameInput.value.trim() : '';
        if (nameVal.length === 0) {
            hasError = true;
            showLiveErr('role_name', 'Role Title / Display Name is required.');
            if (!firstInvalid) firstInvalid = nameInput;
        } else if (nameVal.length < 2) {
            hasError = true;
            showLiveErr('role_name', 'Role Title must be at least 2 characters.');
            if (!firstInvalid) firstInvalid = nameInput;
        } else if (nameVal.length > 70) {
            hasError = true;
            showLiveErr('role_name', 'Role Title cannot exceed 70 characters.');
            if (!firstInvalid) firstInvalid = nameInput;
        }

        const slugVal = slugInput ? slugInput.value.trim() : '';
        if (slugVal && (slugVal.length < 2 || slugVal.length > 50)) {
            hasError = true;
            showLiveErr('role_slug', 'Role Slug must be between 2 and 50 characters.');
            if (!firstInvalid) firstInvalid = slugInput;
        } else if (slugVal && !/^[a-zA-Z0-9_\-]+$/.test(slugVal)) {
            hasError = true;
            showLiveErr('role_slug', 'Role Slug may only contain letters, numbers, hyphens, and underscores.');
            if (!firstInvalid) firstInvalid = slugInput;
        }

        const descVal = descInput ? descInput.value.trim() : '';
        if (descVal && descVal.length > 500) {
            hasError = true;
            showLiveErr('role_description', 'Role Description cannot exceed 500 characters.');
            if (!firstInvalid) firstInvalid = descInput;
        }

        if (hasError) {
            e.preventDefault();
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
    });
});
</script>
@endpush
