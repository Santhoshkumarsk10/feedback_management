@extends('layouts.app')
@section('title', 'Change Password — Security Settings')
@section('page_title', 'Account Security & Password')
@section('page_subtitle', 'Manage your enterprise login credentials, update password, and enforce session security')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <!-- User Identity Banner -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, #06539d 0%, #38bdf8 100%); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; font-weight: 700; color: #ffffff; box-shadow: 0 4px 14px rgba(6, 83, 157, 0.4);">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-white">{{ auth()->user()->name }}</h5>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1 small text-uppercase">
                                    {{ auth()->user()->role }}
                                </span>
                                <span class="small text-slate-400" style="color: #94a3b8;">
                                    <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                                </span>
                                @if(auth()->user()->mobile)
                                    <span class="small text-slate-400" style="color: #94a3b8;">
                                        <i class="bi bi-phone me-1"></i>{{ auth()->user()->mobile }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-end d-none d-sm-block">
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i> Account Protected
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Change Card Form -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom border-light p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-key-fill text-primary"></i>
                            Update Password
                        </h5>
                        <p class="text-muted small mb-0">Enter your existing password and specify a strong new one</p>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-2 small">
                        <i class="bi bi-lock me-1"></i> End-to-End Encrypted
                    </span>
                </div>
            </div>

            <div class="card-body p-4 p-md-5">
                <form action="{{ route('password.change.update') }}" method="POST" id="changePasswordForm" novalidate>
                    @csrf

                    <!-- Current Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small" for="current_password">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" id="current_password" name="current_password" class="form-control border-start-0 border-end-0 @error('current_password') is-invalid @enderror" placeholder="Enter your current password" minlength="4" maxlength="64" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('current_password', 'eyeCurrent')">
                                <i class="bi bi-eye text-muted" id="eyeCurrent"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_current_password"></div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- New Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small" for="password">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="Enter new password (min. 8 characters)" minlength="8" maxlength="64" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('password', 'eyeNew')">
                                <i class="bi bi-eye text-muted" id="eyeNew"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_password"></div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small" for="password_confirmation">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control border-start-0 border-end-0" placeholder="Re-enter new password" minlength="8" maxlength="64" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('password_confirmation', 'eyeConfirm')">
                                <i class="bi bi-eye text-muted" id="eyeConfirm"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_password_confirmation"></div>
                    </div>

                    <!-- Password Policy Tips Box -->
                    <div class="p-3 rounded-3 mb-4 bg-light border">
                        <div class="fw-bold small text-dark mb-2">
                            <i class="bi bi-shield-check text-success me-1"></i> Password Security Requirements:
                        </div>
                        <div class="row g-2 small text-secondary">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-primary"></i> Minimum 8 characters in length
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-primary"></i> Must be different from current password
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-primary"></i> Max length limit 64 characters
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-primary"></i> Active sessions remain protected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold" style="background: linear-gradient(135deg, #06539d 0%, #033a72 100%); border: none;">
                            <i class="bi bi-check-circle-fill me-1"></i> Save New Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

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
        if (input) input.classList.add('is-invalid');
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
        if (input) input.classList.remove('is-invalid');
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('changePasswordForm');
        const currPass = document.getElementById('current_password');
        const newPass = document.getElementById('password');
        const confirmPass = document.getElementById('password_confirmation');

        if (currPass) {
            currPass.addEventListener('input', function() {
                if (this.value.trim().length >= 4) {
                    clearPassLiveErr('current_password');
                }
            });
        }

        if (newPass) {
            newPass.addEventListener('input', function() {
                if (this.value.trim().length >= 8) {
                    clearPassLiveErr('password');
                }
                if (confirmPass && confirmPass.value.trim() && this.value === confirmPass.value) {
                    clearPassLiveErr('password_confirmation');
                }
            });
        }

        if (confirmPass) {
            confirmPass.addEventListener('input', function() {
                if (this.value === newPass.value) {
                    clearPassLiveErr('password_confirmation');
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
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
