@extends('layouts.app')
@section('title', 'My Profile')
@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your personal profile details, contact information, and security settings')

@section('content')
<div class="container-fluid px-0">

    @if(session('profile_success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('profile_success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Full-Width User Identity Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #091325 0%, #13223f 50%, #0f1c32 100%);">
        <div class="card-body p-4 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 58px; height: 58px; min-width: 58px; border-radius: 16px; background: linear-gradient(135deg, #0284c7 0%, #06539d 100%); display: flex; align-items: center; justify-content: center; font-size: 1.45rem; font-weight: 700; color: #ffffff; box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35); border: 2px solid rgba(255, 255, 255, 0.15);">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-white">{{ auth()->user()->name }}</h4>
                            <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-30 px-2 py-1 small text-uppercase fw-semibold">
                                <i class="bi bi-person-fill-gear me-1"></i>{{ auth()->user()->role }}
                            </span>
                            <span class="badge bg-success text-white border border-success px-2 py-1 small fw-semibold" style="color: #ffffff !important;">
                                <i class="bi bi-check-circle-fill me-1 text-white"></i> Active
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap mt-2 small text-slate-300" style="color: #cbd5e1;">
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="bi bi-envelope text-info"></i> {{ auth()->user()->email ?? 'No email set' }}
                            </span>
                            @if(auth()->user()->mobile)
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-telephone text-info"></i> {{ auth()->user()->mobile }}
                                </span>
                            @endif
                            @if(auth()->user()->department)
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-building text-info"></i> {{ auth()->user()->department }}
                                </span>
                            @endif
                            @if(auth()->user()->plant)
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-geo-alt text-info"></i> {{ auth()->user()->plant->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-sm-end d-flex flex-column align-items-sm-end gap-1">
                    <span class="badge bg-success text-white border border-success px-3 py-2 fw-semibold" style="color: #ffffff !important;">
                        <i class="bi bi-shield-fill-check text-white me-1"></i> Protected Session
                    </span>
                    <span class="small" style="color: #94a3b8; font-size: 0.8rem;">
                        <i class="bi bi-clock-history me-1"></i> Member since {{ auth()->user()->created_at ? auth()->user()->created_at->format('M Y') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Full-Page Two-Column Responsive Layout -->
    <div class="row g-4 align-items-stretch">
        
        <!-- Column 1: Profile Details Update -->
        <div class="col-xl-6 col-lg-6 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom border-light p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-person-bounding-box text-primary"></i>
                                Profile Details
                            </h5>
                            <p class="text-muted small mb-0">Update your personal account information and contact records</p>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 small">
                            <i class="bi bi-person-check me-1 text-primary"></i> Account Profile
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-4 d-flex flex-column flex-grow-1">
                    <form action="{{ route('profile.update') }}" method="POST" id="profileUpdateForm" class="d-flex flex-column flex-grow-1" novalidate>
                        @csrf

                        <div class="flex-grow-1">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="profile_name">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <div class="custom-input-group" id="group_profile_name">
                                    <span class="input-icon"><i class="bi bi-person"></i></span>
                                    <input type="text" id="profile_name" name="name" 
                                           class="form-control" 
                                           value="{{ old('name', auth()->user()->name) }}" 
                                           placeholder="Enter your full name" 
                                           minlength="2" maxlength="70" required autocomplete="name">
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_profile_name"></div>
                            </div>

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="profile_email">
                                    Official Email Address
                                </label>
                                <div class="custom-input-group" id="group_profile_email">
                                    <span class="input-icon"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="profile_email" name="email" 
                                           class="form-control" 
                                           value="{{ old('email', auth()->user()->email) }}" 
                                           placeholder="e.g. name@company.com" 
                                           maxlength="100" autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_profile_email"></div>
                            </div>

                            <!-- Mobile Number -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="profile_mobile">
                                    Mobile Number
                                </label>
                                <div class="custom-input-group" id="group_profile_mobile">
                                    <span class="input-icon"><i class="bi bi-phone"></i></span>
                                    <input type="tel" id="profile_mobile" name="mobile" 
                                           class="form-control" 
                                           value="{{ old('mobile', auth()->user()->mobile) }}" 
                                           placeholder="10-digit mobile number" 
                                           maxlength="10" autocomplete="tel">
                                </div>
                                <div class="form-text text-muted small mt-1" style="font-size: 0.78rem;">
                                    10-digit mobile number starting with 6, 7, 8, or 9.
                                </div>
                                @error('mobile')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_profile_mobile"></div>
                            </div>

                            <!-- Department -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small" for="profile_department">
                                    Department / Section
                                </label>
                                <div class="custom-input-group" id="group_profile_department">
                                    <span class="input-icon"><i class="bi bi-building"></i></span>
                                    <input type="text" id="profile_department" name="department" 
                                           class="form-control" 
                                           value="{{ old('department', auth()->user()->department) }}" 
                                           placeholder="e.g. Operations, IT, Quality" 
                                           maxlength="60">
                                </div>
                                @error('department')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Read-Only Enterprise Context Box -->
                            <div class="p-3 rounded-3 mb-4 bg-light border">
                                <div class="fw-bold small text-dark mb-2 d-flex align-items-center gap-1">
                                    <i class="bi bi-info-circle-fill text-primary"></i> System Access Scope (Admin Controlled)
                                </div>
                                <div class="row g-2 small text-secondary">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border">
                                            <span class="text-muted">Assigned Role:</span>
                                            <span class="badge bg-primary-subtle text-primary fw-semibold">{{ strtoupper(auth()->user()->role) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border">
                                            <span class="text-muted">Location / Plant:</span>
                                            <span class="fw-semibold text-dark">{{ auth()->user()->plant?->name ?? 'Global (All)' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Profile Button (Aligned with Save New Password) -->
                        <div class="mt-auto pt-4 border-top d-flex align-items-center justify-content-between">
                            <span class="small text-muted" style="font-size: 0.8rem;">
                                <i class="bi bi-shield-check text-success me-1"></i> Verified Enterprise Profile
                            </span>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                                <i class="bi bi-check2-circle me-1"></i> Save Profile Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Column 2: Account Security & Password -->
        <div class="col-xl-6 col-lg-6 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom border-light p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-key-fill text-primary"></i>
                                Update Password
                            </h5>
                            <p class="text-muted small mb-0">Enter your existing password and specify a compliant strong new one</p>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 small">
                            <i class="bi bi-lock me-1 text-primary"></i> End-to-End Encrypted
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-4 d-flex flex-column flex-grow-1">
                    <form action="{{ route('password.change.update') }}" method="POST" id="changePasswordForm" class="d-flex flex-column flex-grow-1" novalidate>
                        @csrf

                        <div class="flex-grow-1">
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="current_password">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="custom-input-group" id="group_current_password">
                                    <span class="input-icon"><i class="bi bi-lock"></i></span>
                                    <input type="password" id="current_password" name="current_password" 
                                           class="form-control" 
                                           placeholder="Enter your current password" 
                                           minlength="4" maxlength="64" required autocomplete="current-password">
                                    <button class="btn-password-toggle" type="button" onclick="toggleField('current_password', 'eyeCurrent')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="eyeCurrent"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_current_password"></div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="password">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="custom-input-group" id="group_password">
                                    <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" id="password" name="password" 
                                           class="form-control" 
                                           placeholder="Enter new password (8-16 characters)" 
                                           minlength="8" maxlength="16" required autocomplete="new-password">
                                    <button class="btn-password-toggle" type="button" onclick="toggleField('password', 'eyeNew')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="eyeNew"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_password"></div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small" for="password_confirmation">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="custom-input-group" id="group_password_confirmation">
                                    <span class="input-icon"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" id="password_confirmation" name="password_confirmation" 
                                           class="form-control" 
                                           placeholder="Re-enter new password" 
                                           minlength="8" maxlength="16" required autocomplete="new-password">
                                    <button class="btn-password-toggle" type="button" onclick="toggleField('password_confirmation', 'eyeConfirm')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="eyeConfirm"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-none custom-live-err" id="live_err_password_confirmation"></div>
                            </div>

                            <!-- Password Policy Tips Box -->
                            <div class="p-3 rounded-3 mb-4 bg-light border" id="passwordRequirementsBox">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-bold small text-dark">
                                        <i class="bi bi-shield-lock-fill text-primary me-1"></i> Password Security Requirements:
                                    </div>
                                    <span id="passwordStrengthBadge" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 small">
                                        Strength: Empty
                                    </span>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-length">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-length"></i>
                                            <span>8 to 16 characters in length</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-different">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-different"></i>
                                            <span>Must be different from current password</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-caps">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-caps"></i>
                                            <span>At least 1 uppercase letter (Caps)</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-special">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-special"></i>
                                            <span>At least 1 special character (!@#$...)</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-digits">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-digits"></i>
                                            <span>At least 3 digits compulsory (0-9)</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 text-danger req-item" id="req-match">
                                            <i class="bi bi-x-circle text-danger req-icon" id="icon-match"></i>
                                            <span>New password & confirmation match</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-2 text-muted req-item pt-2 border-top small" id="req-session">
                                            <i class="bi bi-shield-check text-success req-icon"></i>
                                            <span>Active sessions remain protected</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Password Button (Aligned with Save Profile Details, Back to Dashboard removed) -->
                        <div class="mt-auto pt-4 border-top d-flex align-items-center justify-content-between">
                            <span class="small text-muted" style="font-size: 0.8rem;">
                                <i class="bi bi-lock-fill text-primary me-1"></i> End-to-End Encrypted
                            </span>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm" style="background: linear-gradient(135deg, #06539d 0%, #033a72 100%); border: none;">
                                <i class="bi bi-check-circle-fill me-1"></i> Save New Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Uniform, crisp input group styling with matching outerline */
    .custom-input-group {
        display: flex;
        align-items: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background-color: #ffffff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
        width: 100%;
    }
    .custom-input-group:hover {
        border-color: #94a3b8;
    }
    .custom-input-group:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    .custom-input-group.is-invalid-group {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
    }
    .custom-input-group .input-icon {
        padding: 0.65rem 0.85rem;
        color: #64748b;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .custom-input-group .form-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0.65rem 0.5rem 0.65rem 0;
        font-size: 0.9rem;
        color: #1e293b;
        flex-grow: 1;
        min-width: 0;
    }
    .custom-input-group .form-control:focus {
        outline: none;
        box-shadow: none !important;
    }
    .custom-input-group .btn-password-toggle {
        border: none !important;
        background: transparent !important;
        padding: 0.65rem 0.95rem;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.15s ease, background 0.15s ease;
        flex-shrink: 0;
    }
    .custom-input-group .btn-password-toggle:hover {
        color: #0284c7;
        background: rgba(2, 132, 199, 0.08) !important;
    }
    .custom-input-group .btn-password-toggle:focus {
        outline: none;
    }

    .req-item {
        transition: color 0.2s ease, opacity 0.2s ease;
        font-weight: 500;
    }
    .req-icon {
        font-size: 0.95rem;
        transition: transform 0.2s ease, color 0.2s ease;
    }
</style>

<script>
    function toggleField(inputId, iconId) {
        const inp = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function showPassLiveErr(id, msg, persistent = false) {
        const errEl = document.getElementById('live_err_' + id);
        const input = document.getElementById(id);
        const group = document.getElementById('group_' + id) || (input ? input.closest('.custom-input-group') : null);
        if (input) input.classList.add('is-invalid');
        if (group) group.classList.add('is-invalid-group');
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.remove('d-none');
            errEl.classList.add('d-block');
            clearTimeout(errEl._timer);
            if (!persistent) {
                errEl._timer = setTimeout(() => {
                    errEl.classList.remove('d-block');
                    errEl.classList.add('d-none');
                }, 2500);
            }
        }
    }

    function clearPassLiveErr(id) {
        const errEl = document.getElementById('live_err_' + id);
        const input = document.getElementById(id);
        const group = document.getElementById('group_' + id) || (input ? input.closest('.custom-input-group') : null);
        if (input) input.classList.remove('is-invalid');
        if (group) group.classList.remove('is-invalid-group');
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
    }

    function updateRequirement(elId, iconId, isValid) {
        const el = document.getElementById(elId);
        const icon = document.getElementById(iconId);
        if (!el || !icon) return;

        if (isValid) {
            el.classList.remove('text-danger');
            el.classList.add('text-success');
            icon.classList.remove('bi-x-circle', 'text-danger');
            icon.classList.add('bi-check-circle-fill', 'text-success');
        } else {
            el.classList.remove('text-success');
            el.classList.add('text-danger');
            icon.classList.remove('bi-check-circle-fill', 'text-success');
            icon.classList.add('bi-x-circle', 'text-danger');
        }
    }

    function validatePasswordRequirements() {
        const currPass = document.getElementById('current_password');
        const newPass = document.getElementById('password');
        const confirmPass = document.getElementById('password_confirmation');

        const currVal = currPass ? currPass.value : '';
        const newVal = newPass ? newPass.value : '';
        const confVal = confirmPass ? confirmPass.value : '';

        // 1. Length: 8 to 16 characters
        const hasValidLength = newVal.length >= 8 && newVal.length <= 16;
        updateRequirement('req-length', 'icon-length', hasValidLength);

        // 2. At least 1 uppercase letter (Caps)
        const hasCaps = /[A-Z]/.test(newVal);
        updateRequirement('req-caps', 'icon-caps', hasCaps);

        // 3. At least 1 special character
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(newVal);
        updateRequirement('req-special', 'icon-special', hasSpecial);

        // 4. At least 3 digits compulsory
        const digitCount = (newVal.match(/\d/g) || []).length;
        const hasThreeDigits = digitCount >= 3;
        updateRequirement('req-digits', 'icon-digits', hasThreeDigits);

        // 5. Must be different from current password
        const isDifferent = newVal.length > 0 && (currVal.length === 0 || newVal !== currVal);
        updateRequirement('req-different', 'icon-different', isDifferent);

        // 6. New password and confirmation match
        const isMatch = newVal.length > 0 && confVal.length > 0 && newVal === confVal;
        updateRequirement('req-match', 'icon-match', isMatch);

        // Strength badge
        const badge = document.getElementById('passwordStrengthBadge');
        if (badge) {
            const passedCount = [hasValidLength, hasCaps, hasSpecial, hasThreeDigits, isDifferent, isMatch].filter(Boolean).length;
            if (!newVal) {
                badge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 small';
                badge.textContent = 'Strength: Empty';
            } else if (passedCount <= 2) {
                badge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 small';
                badge.textContent = 'Strength: Weak';
            } else if (passedCount <= 4) {
                badge.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 small';
                badge.textContent = 'Strength: Medium';
            } else if (passedCount === 5) {
                badge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small';
                badge.textContent = 'Strength: Strong';
            } else {
                badge.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small';
                badge.textContent = 'Strength: Excellent';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const passForm = document.getElementById('changePasswordForm');
        const currPass = document.getElementById('current_password');
        const newPass = document.getElementById('password');
        const confirmPass = document.getElementById('password_confirmation');

        // Profile Form Handlers
        const profileForm = document.getElementById('profileUpdateForm');
        const profileName = document.getElementById('profile_name');
        const profileEmail = document.getElementById('profile_email');
        const profileMobile = document.getElementById('profile_mobile');

        if (profileMobile) {
            profileMobile.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
                if (this.value.length === 10) {
                    clearPassLiveErr('profile_mobile');
                }
            });
        }

        if (profileName) {
            profileName.addEventListener('input', function() {
                if (this.value.trim().length >= 2) {
                    clearPassLiveErr('profile_name');
                }
            });
        }

        if (profileEmail) {
            profileEmail.addEventListener('input', function() {
                if (/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(this.value.trim())) {
                    clearPassLiveErr('profile_email');
                }
            });
        }

        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                let hasErr = false;
                let firstFocus = null;

                const nameVal = profileName ? profileName.value.trim() : '';
                const emailVal = profileEmail ? profileEmail.value.trim() : '';
                const mobileVal = profileMobile ? profileMobile.value.trim() : '';

                if (!nameVal || nameVal.length < 2) {
                    hasErr = true;
                    showPassLiveErr('profile_name', 'Full Name must be at least 2 characters.', true);
                    if (!firstFocus) firstFocus = profileName;
                }

                if (!emailVal && !mobileVal) {
                    hasErr = true;
                    showPassLiveErr('profile_email', 'Either Email Address or Mobile Number is required.', true);
                    if (!firstFocus) firstFocus = profileEmail;
                } else {
                    if (emailVal && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailVal)) {
                        hasErr = true;
                        showPassLiveErr('profile_email', 'Please provide a valid official email address with domain.', true);
                        if (!firstFocus) firstFocus = profileEmail;
                    }
                    if (mobileVal && !/^[6-9][0-9]{9}$/.test(mobileVal)) {
                        hasErr = true;
                        showPassLiveErr('profile_mobile', 'Mobile number must be 10 digits starting with 6, 7, 8, or 9.', true);
                        if (!firstFocus) firstFocus = profileMobile;
                    }
                }

                if (hasErr) {
                    e.preventDefault();
                    if (firstFocus) {
                        firstFocus.focus();
                        firstFocus.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }

        // Password Form Handlers
        if (currPass) {
            currPass.addEventListener('input', function() {
                if (this.value.trim().length >= 4) {
                    clearPassLiveErr('current_password');
                }
                validatePasswordRequirements();
            });
        }

        if (newPass) {
            newPass.addEventListener('input', function() {
                const val = this.value;
                if (val.length >= 8 && val.length <= 16 && /[A-Z]/.test(val) && /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(val) && (val.match(/\d/g) || []).length >= 3) {
                    clearPassLiveErr('password');
                }
                if (confirmPass && confirmPass.value.trim() && val === confirmPass.value) {
                    clearPassLiveErr('password_confirmation');
                }
                validatePasswordRequirements();
            });
        }

        if (confirmPass) {
            confirmPass.addEventListener('input', function() {
                if (this.value === newPass.value) {
                    clearPassLiveErr('password_confirmation');
                }
                validatePasswordRequirements();
            });
        }

        // Run validation on load to ensure initial state is red
        validatePasswordRequirements();

        if (passForm) {
            passForm.addEventListener('submit', function(e) {
                let hasError = false;
                let firstInvalid = null;

                const currVal = currPass ? currPass.value : '';
                const newVal = newPass ? newPass.value : '';
                const confVal = confirmPass ? confirmPass.value : '';

                if (!currVal) {
                    hasError = true;
                    showPassLiveErr('current_password', 'Current password is required.', true);
                    if (!firstInvalid) firstInvalid = currPass;
                } else if (currVal.length < 4) {
                    hasError = true;
                    showPassLiveErr('current_password', 'Current password must be at least 4 characters.', true);
                    if (!firstInvalid) firstInvalid = currPass;
                }

                if (!newVal) {
                    hasError = true;
                    showPassLiveErr('password', 'New password is required.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if (newVal.length < 8) {
                    hasError = true;
                    showPassLiveErr('password', 'New password must be at least 8 characters.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if (newVal.length > 16) {
                    hasError = true;
                    showPassLiveErr('password', 'New password must not exceed 16 characters.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if (!/[A-Z]/.test(newVal)) {
                    hasError = true;
                    showPassLiveErr('password', 'New password must contain at least 1 uppercase letter (Caps).', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(newVal)) {
                    hasError = true;
                    showPassLiveErr('password', 'New password must contain at least 1 special character.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if ((newVal.match(/\d/g) || []).length < 3) {
                    hasError = true;
                    showPassLiveErr('password', 'New password must contain at least 3 digits compulsory.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                } else if (currVal && newVal === currVal) {
                    hasError = true;
                    showPassLiveErr('password', 'The new password must be different from your current password.', true);
                    if (!firstInvalid) firstInvalid = newPass;
                }

                if (!confVal) {
                    hasError = true;
                    showPassLiveErr('password_confirmation', 'Please confirm your new password.', true);
                    if (!firstInvalid) firstInvalid = confirmPass;
                } else if (confVal !== newVal) {
                    hasError = true;
                    showPassLiveErr('password_confirmation', 'Confirmation password does not match.', true);
                    if (!firstInvalid) firstInvalid = confirmPass;
                }

                if (hasError) {
                    e.preventDefault();
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
    });
</script>
@endsection
