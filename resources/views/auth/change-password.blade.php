@extends('layouts.app')
@section('title', 'Change Password — Security Settings')
@section('page_title', 'Account Security & Password')
@section('page_subtitle', 'Manage your enterprise login credentials, update password, and enforce session security')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8 col-lg-10">

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm border-0 mb-4" role="alert" style="background: #ecfdf5; border-left: 4px solid #10b981 !important;">
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                <div>
                    <div class="fw-bold text-dark">Password Updated Successfully</div>
                    <div class="small text-secondary">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger p-3 rounded-3 shadow-sm border-0 mb-4" style="background: #fef2f2; border-left: 4px solid #ef4444 !important;">
                <div class="fw-bold text-danger mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span>Please correct the issues below:</span>
                </div>
                <ul class="mb-0 ps-3 small text-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                <form action="{{ route('password.change.update') }}" method="POST">
                    @csrf

                    <!-- Current Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" id="current_password" name="current_password" class="form-control border-start-0 border-end-0 @error('current_password') is-invalid @enderror" placeholder="Enter your current password" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('current_password', 'eyeCurrent')">
                                <i class="bi bi-eye text-muted" id="eyeCurrent"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- New Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="Enter new password (min. 8 characters)" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('password', 'eyeNew')">
                                <i class="bi bi-eye text-muted" id="eyeNew"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control border-start-0 border-end-0" placeholder="Re-enter new password" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="toggleField('password_confirmation', 'eyeConfirm')">
                                <i class="bi bi-eye text-muted" id="eyeConfirm"></i>
                            </button>
                        </div>
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
                                    <i class="bi bi-check-circle text-primary"></i> Uses mixed case and numbers
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
</script>
@endsection
