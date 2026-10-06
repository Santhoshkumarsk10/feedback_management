@extends('layouts.app')
@section('title', $user->exists ? 'Edit User: '.$user->name : 'Add New User')
@section('page_title', $user->exists ? 'Edit User Profile' : 'Create Team Member')
@section('page_subtitle', $user->exists ? 'Update role, department, or login access' : 'Register a new administrator or plant tour organizer')

@section('content')
<div class="mb-3">
    <a href="{{ route('users.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to User List
    </a>
</div>

<div class="row g-4">
    <!-- Main User Form Column (Col-lg-8) -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span>{{ $user->exists ? 'Update Account Information' : 'New User Details' }}</span>
                </div>
                @if($user->exists)
                    <span class="badge-modern badge-shibaura">ID: #{{ $user->id }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" id="userForm" novalidate>
                    @csrf
                    @if($user->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_name">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input name="name" 
                                       id="user_name"
                                       class="form-control form-control-modern border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" 
                                       placeholder="e.g. Ramesh Kumar" 
                                       required
                                       minlength="2"
                                       maxlength="70">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_role">Access Role <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <select name="role_id" 
                                        id="user_role"
                                        class="form-select form-select-modern border-start-0 @error('role_id') is-invalid @enderror" 
                                        @disabled($user->id === auth()->id()) 
                                        required>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id || (empty($user->role_id) && $user->role === $r->slug))>
                                             {{ $r->name }} ({{ $r->slug }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                                <div class="small text-muted mt-1">You cannot modify your own role.</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_plant">Assigned Manufacturing Plant</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-buildings"></i></span>
                                <select name="plant_id" 
                                        id="user_plant"
                                        class="form-select form-select-modern border-start-0 @error('plant_id') is-invalid @enderror">
                                    <option value="">— HQ / Corporate (All Plants) —</option>
                                    @foreach($plants as $p)
                                        <option value="{{ $p->id }}" @selected(old('plant_id', $user->plant_id) == $p->id)>
                                            {{ $p->code }} — {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('plant_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-1">Select the factory unit this user belongs to</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_email">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       name="email" 
                                       id="user_email"
                                       class="form-control form-control-modern border-start-0 @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="name@plant.test"
                                       maxlength="100">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_email"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_mobile">Mobile Number (10 Digits)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input type="tel" 
                                       name="mobile" 
                                       id="user_mobile"
                                       class="form-control form-control-modern border-start-0 @error('mobile') is-invalid @enderror" 
                                       value="{{ old('mobile', $user->mobile) }}" 
                                       placeholder="e.g. 9844268120"
                                       minlength="10"
                                       maxlength="10"
                                       pattern="^[6-9][0-9]{9}$"
                                       title="10-digit mobile number starting with 6, 7, 8, or 9">
                            </div>
                            @error('mobile')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_mobile"></div>
                            <div class="small text-muted mt-1">Starting with 6, 7, 8, or 9</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_department">Department / Area</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input name="department" 
                                       id="user_department"
                                       class="form-control form-control-modern border-start-0 @error('department') is-invalid @enderror" 
                                       value="{{ old('department', $user->department) }}" 
                                       placeholder="e.g. Production, Quality, Technical Centre"
                                       minlength="2"
                                       maxlength="60">
                            </div>
                            @error('department')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_department"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_password">
                                Password 
                                @if($user->exists)
                                    <small class="text-muted fw-normal">(Leave blank to keep current)</small>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       name="password" 
                                       id="user_password"
                                       class="form-control form-control-modern border-start-0 @error('password') is-invalid @enderror" 
                                       placeholder="••••••••" 
                                       minlength="6"
                                       maxlength="60"
                                       {{ $user->exists ? '' : 'required' }}>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_password"></div>
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold text-dark">Account Status</div>
                                <div class="small text-muted">Active accounts can log into the mobile organizer app or web portal.</div>
                            </div>
                            <div class="form-check form-switch fs-5 m-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $user->is_active ?? true))>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $user->exists ? 'Save Changes' : 'Create User' }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Role Privileges & Security Reference (Col-lg-4) -->
    <div class="col-lg-4">
        <div class="card-modern mb-4">
            <div class="card-header bg-white">
                <div class="card-title text-primary">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Role Access Privileges</span>
                </div>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-3" style="font-size: 0.82rem;">
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <div class="fw-bold text-dark">Super Admin</div>
                        <div class="small text-muted">Full administrative privileges across user management, questionnaire configuration, and reporting.</div>
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-info">
                        <div class="fw-bold text-dark">Admin</div>
                        <div class="small text-muted">Technical Centre management, review audit, export logs, and visitor management.</div>
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-success">
                        <div class="fw-bold text-dark">Organizer / Guide</div>
                        <div class="small text-muted">Access to mobile organizer application, conducting customer tours and reviewing received feedback.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-modern p-3 bg-light border">
            <div class="small text-muted">
                <i class="bi bi-info-circle-fill text-primary me-1"></i> Either email or mobile is required for user login authentication.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm') || document.querySelector('form[action*="users"]');
    if (!form) return;

    const userConfigs = {
        user_name: {
            label: 'Full Name',
            disallowedRegex: /[^a-zA-Z\s\.\-']/g,
            min: 2,
            max: 70,
            required: true,
            msg: 'Only letters, spaces, hyphens, and dots are allowed in Name.'
        },
        user_mobile: {
            label: 'Mobile Number',
            disallowedRegex: /[^0-9]/g,
            min: 10,
            max: 10,
            phonePattern: /^[6-9][0-9]{9}$/,
            msg: 'Mobile must be a 10-digit number starting with 6, 7, 8, or 9.'
        },
        user_department: {
            label: 'Department',
            disallowedRegex: /[<>{}\[\]$^*~=\\\|]/g,
            min: 2,
            max: 60,
            msg: 'Special symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
        },
        user_email: {
            label: 'Email Address',
            disallowedRegex: /[^a-zA-Z0-9@._+\-]/g,
            emailPattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            max: 100,
            msg: 'Spaces and symbols other than @, ., _, +, - are not allowed in email.'
        }
    };

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

    Object.keys(userConfigs).forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        const cfg = userConfigs[id];

        // 1. Prevent typing disallowed characters on keystroke
        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (cfg.disallowedRegex && cfg.disallowedRegex.test(e.key)) {
                    e.preventDefault();
                    showLiveErr(id, cfg.msg);
                }
            }
        });

        // 2. Prevent disallowed input on virtual keyboards / mobile
        input.addEventListener('beforeinput', function(e) {
            if (e.data && cfg.disallowedRegex) {
                for (let i = 0; i < e.data.length; i++) {
                    if (cfg.disallowedRegex.test(e.data[i])) {
                        e.preventDefault();
                        showLiveErr(id, cfg.msg);
                        return;
                    }
                }
            }
        });

        // 3. Clean paste
        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && cfg.disallowedRegex && cfg.disallowedRegex.test(text)) {
                e.preventDefault();
                const cleaned = text.replace(cfg.disallowedRegex, '');
                document.execCommand('insertText', false, cleaned);
                showLiveErr(id, 'Unwanted characters removed from pasted text.');
            }
        });

        // 4. Input fallback & clear
        input.addEventListener('input', function() {
            clearLiveErr(id);
            if (cfg.disallowedRegex && cfg.disallowedRegex.test(this.value)) {
                this.value = this.value.replace(cfg.disallowedRegex, '');
                showLiveErr(id, cfg.msg);
            }
            if (cfg.max && this.value.length > cfg.max) {
                this.value = this.value.substring(0, cfg.max);
            }
        });

        // 5. Blur validation
        input.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val.length > 0 && cfg.min && val.length < cfg.min) {
                showLiveErr(id, `${cfg.label} must be at least ${cfg.min} characters.`);
            } else if (val.length > 0 && cfg.phonePattern && !cfg.phonePattern.test(val)) {
                showLiveErr(id, 'Must be a 10-digit number starting with 6, 7, 8, or 9.');
            } else if (val.length > 0 && cfg.emailPattern && !cfg.emailPattern.test(val)) {
                showLiveErr(id, 'Please enter a valid official email address with domain (e.g. name@company.com).');
            }
        });
    });

    const pwdInput = document.getElementById('user_password');
    pwdInput?.addEventListener('input', function() {
        if (this.value.trim().length >= 6) {
            clearLiveErr('user_password');
        }
    });

    form.addEventListener('submit', function(e) {
        let hasError = false;
        let firstInvalid = null;

        Object.keys(userConfigs).forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            const cfg = userConfigs[id];
            const val = input.value.trim();

            if (cfg.required && val.length === 0) {
                hasError = true;
                showLiveErr(id, `${cfg.label} is required.`);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.min && val.length < cfg.min) {
                hasError = true;
                showLiveErr(id, `${cfg.label} must be at least ${cfg.min} characters.`);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.phonePattern && !cfg.phonePattern.test(val)) {
                hasError = true;
                showLiveErr(id, 'Must be a 10-digit number starting with 6, 7, 8, or 9.');
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.emailPattern && !cfg.emailPattern.test(val)) {
                hasError = true;
                showLiveErr(id, 'Please enter a valid official email address with domain (e.g. name@company.com).');
                if (!firstInvalid) firstInvalid = input;
            }
        });

        // Email or Mobile check
        const emailInput = document.getElementById('user_email');
        const mobileInput = document.getElementById('user_mobile');
        const emailVal = (emailInput?.value || '').trim();
        const mobileVal = (mobileInput?.value || '').trim();
        if (!emailVal && !mobileVal) {
            hasError = true;
            showLiveErr('user_email', 'Either Email Address or Mobile Number is required.');
            showLiveErr('user_mobile', 'Either Email Address or Mobile Number is required.');
            if (!firstInvalid) firstInvalid = emailInput || mobileInput;
        }

        // Password check (required on create)
        const isCreate = !form.querySelector('input[name="_method"][value="PUT"]');
        if (pwdInput && isCreate) {
            const pwdVal = pwdInput.value.trim();
            if (!pwdVal) {
                hasError = true;
                showLiveErr('user_password', 'Password is required (minimum 6 characters).');
                if (!firstInvalid) firstInvalid = pwdInput;
            } else if (pwdVal.length < 6) {
                hasError = true;
                showLiveErr('user_password', 'Password must be at least 6 characters.');
                if (!firstInvalid) firstInvalid = pwdInput;
            }
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
