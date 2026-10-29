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
        <div class="card-modern overflow-visible" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span>{{ $user->exists ? 'Update Account Information' : 'New User Details' }}</span>
                </div>
                @if($user->exists)
                    <span class="badge-modern badge-shibaura">ID: {{ $user->id }}</span>
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
                                       maxlength="100"
                                       pattern="^[\p{L}\s]+$"
                                       title="Full Name may only contain letters and spaces. Special characters are not allowed.">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_user_name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="user_role">Access Role <span class="text-danger">*</span></label>
                            <x-custom-select 
                                name="role_id"
                                id="user_role"
                                :options="$roles->map(fn($r) => [
                                    'value' => (string)$r->id,
                                    'label' => ($r->display_name ?? $r->name) . ' (' . $r->slug . ')',
                                ])"
                                :value="(string) old('role_id', $user->role_id ?? ($roles->firstWhere('slug', $user->role)?->id ?? ''))"
                                placeholder="Select Access Role"
                                search-placeholder="Search role name or slug..."
                                icon="bi-shield-lock"
                                :auto-submit="false"
                                :allow-empty="false"
                                min-width="100%"
                                :disabled="$user->id === auth()->id()"
                            />
                            @error('role_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($user->id === auth()->id())
                                <div class="small text-muted mt-1"><i class="bi bi-lock-fill"></i> You cannot modify your own role.</div>
                            @endif
                        </div>


                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-modern-label mb-0" for="user_email">Email Address</label>
                                <span class="validation-counter text-muted" style="font-size: 0.72rem;">Optional</span>
                            </div>
                            <div class="input-icon-group">
                                <span class="input-icon-badge" id="badge_user_email"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" 
                                       name="email" 
                                       id="user_email" 
                                       class="form-control form-control-modern @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="name@shibaura-machine.co.in" 
                                       maxlength="100" pattern="^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$" title="Please provide a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).">
                            </div>
                            <div id="feedback_user_email" class="validation-feedback-box error {{ $errors->has('email') ? '' : 'd-none' }}">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="feedback_user_email_text">{{ $errors->first('email') }}</span>
                            </div>
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
                                       maxlength="100"
                                       pattern="^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$"
                                       title="Department may only contain letters, numbers, spaces, and allowed symbols.">
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
            disallowedRegex: /[^a-zA-Z\s]/g,
            min: 2,
            max: 100,
            required: true,
            msg: 'Only letters and spaces are allowed in Name. Special characters are not allowed.'
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
            max: 100,
            msg: 'Special symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
        },
        user_email: {
            label: 'Email Address',
            disallowedRegex: /[^a-zA-Z0-9@._\-]/g,
            emailPattern: /^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/,
            max: 100,
            msg: 'Spaces and symbols other than @, ., _, - are not allowed in email.'
        }
    };

    function setFieldState(inputEl, feedbackEl, textEl, isValid, message = '', isEmpty = false) {
        if (!inputEl) return;
        if (isEmpty) {
            inputEl.classList.remove('is-valid', 'is-invalid');
            if (feedbackEl) feedbackEl.classList.add('d-none');
            if (textEl) textEl.textContent = '';
            return;
        }
        if (isValid) {
            inputEl.classList.remove('is-invalid');
            inputEl.classList.add('is-valid');
            if (feedbackEl) feedbackEl.classList.add('d-none');
            if (textEl) textEl.textContent = '';
        } else {
            inputEl.classList.remove('is-valid');
            inputEl.classList.add('is-invalid');
            if (feedbackEl) {
                feedbackEl.classList.remove('d-none');
                feedbackEl.classList.add('error');
            }
            if (textEl) textEl.textContent = message;
        }
    }

    function triggerShake(element) {
        if (!element) return;
        element.classList.remove('field-shake');
        void element.offsetWidth;
        element.classList.add('field-shake');
    }

    function validateUserEmailField() {
        const input = document.getElementById('user_email');
        if (!input) return true;
        const feedbackEl = document.getElementById('feedback_user_email');
        const textEl = document.getElementById('feedback_user_email_text');
        const val = input.value.trim();

        if (!val) {
            setFieldState(input, feedbackEl, textEl, true, '', true);
            return true;
        }

        const emailRegex = /^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(val)) {
            setFieldState(input, feedbackEl, textEl, false, 'Please enter a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).');
            return false;
        }

        setFieldState(input, feedbackEl, textEl, true);
        return true;
    }

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
                    if (id === 'user_email') {
                        setFieldState(input, document.getElementById('feedback_user_email'), document.getElementById('feedback_user_email_text'), false, cfg.msg);
                    } else {
                        showLiveErr(id, cfg.msg);
                    }
                }
            }
        });

        // 2. Prevent disallowed input on virtual keyboards / mobile
        input.addEventListener('beforeinput', function(e) {
            if (e.data && cfg.disallowedRegex) {
                for (let i = 0; i < e.data.length; i++) {
                    if (cfg.disallowedRegex.test(e.data[i])) {
                        e.preventDefault();
                        if (id === 'user_email') {
                            setFieldState(input, document.getElementById('feedback_user_email'), document.getElementById('feedback_user_email_text'), false, cfg.msg);
                        } else {
                            showLiveErr(id, cfg.msg);
                        }
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
                if (id === 'user_email') {
                    validateUserEmailField();
                } else {
                    showLiveErr(id, 'Unwanted characters removed from pasted text.');
                }
            }
        });

        // 4. Input fallback & clear
        input.addEventListener('input', function() {
            if (id === 'user_email') {
                if (cfg.disallowedRegex && cfg.disallowedRegex.test(this.value)) {
                    this.value = this.value.replace(cfg.disallowedRegex, '');
                }
                validateUserEmailField();
                return;
            }
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
            if (id === 'user_email') {
                validateUserEmailField();
                return;
            }
            const val = this.value.trim();
            if (val.length > 0 && cfg.min && val.length < cfg.min) {
                showLiveErr(id, `${cfg.label} must be at least ${cfg.min} characters.`);
            } else if (val.length > 0 && cfg.phonePattern && !cfg.phonePattern.test(val)) {
                showLiveErr(id, 'Must be a 10-digit number starting with 6, 7, 8, or 9.');
            }
        });
    });

    // Initial validation if email already has value
    if (document.getElementById('user_email')?.value.trim()) {
        validateUserEmailField();
    }

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

            if (id === 'user_email') {
                if (val.length > 0 && !validateUserEmailField()) {
                    hasError = true;
                    if (!firstInvalid) firstInvalid = input;
                }
                return;
            }

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
            }
        });

        // Email or Mobile check
        const emailInput = document.getElementById('user_email');
        const mobileInput = document.getElementById('user_mobile');
        const emailVal = (emailInput?.value || '').trim();
        const mobileVal = (mobileInput?.value || '').trim();
        if (!emailVal && !mobileVal) {
            hasError = true;
            setFieldState(emailInput, document.getElementById('feedback_user_email'), document.getElementById('feedback_user_email_text'), false, 'Either Email Address or Mobile Number is required.');
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
                triggerShake(firstInvalid);
            }
            return false;
        }
    });
});
</script>
@endpush