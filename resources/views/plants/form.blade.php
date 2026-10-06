@extends('layouts.app')
@section('title', $plant->exists ? 'Edit Plant: '.$plant->code : 'Register New Plant')
@section('page_title', $plant->exists ? 'Edit Plant Details' : 'Register Manufacturing Facility')
@section('page_subtitle', $plant->exists ? 'Update factory metadata, address, contact, and operation status' : 'Add a new Shibaura Machine manufacturing plant or technical experience facility')

@section('content')
<div class="mb-3">
    <a href="{{ route('plants.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Plant List
    </a>
</div>

<div class="row g-4">
    <!-- Main Plant Form Column -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-buildings-fill text-primary"></i>
                    <span>{{ $plant->exists ? 'Modify Plant Configuration' : 'New Plant Information' }}</span>
                </div>
                @if($plant->exists)
                    <span class="badge-modern badge-shibaura">{{ $plant->code }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $plant->exists ? route('plants.update', $plant) : route('plants.store') }}" id="plantForm" novalidate>
                    @csrf
                    @if($plant->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4 mb-4">
                        <!-- Plant Code -->
                        <div class="col-md-4">
                            <label class="form-modern-label" for="plant_code">Plant Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-qr-code"></i></span>
                                <input name="code" 
                                       id="plant_code"
                                       class="form-control form-control-modern border-start-0 @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $plant->code) }}" 
                                       placeholder="e.g. PLANT-01" 
                                       required 
                                       minlength="2"
                                       maxlength="20"
                                       style="text-transform: uppercase;">
                            </div>
                            <div class="form-text text-muted small mt-1">Unique facility identifier</div>
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_code"></div>
                        </div>

                        <!-- Facility / Division Name -->
                        <div class="col-md-8">
                            <label class="form-modern-label" for="plant_name">Facility / Division Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input name="name" 
                                       id="plant_name"
                                       class="form-control form-control-modern border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $plant->name) }}" 
                                       placeholder="e.g. Plant 1 — Machine Tools Division" 
                                       required
                                       minlength="2"
                                       maxlength="100">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_name"></div>
                        </div>

                        <!-- Factory Location / Address -->
                        <div class="col-md-12">
                            <label class="form-modern-label" for="plant_location">Factory Location / Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-geo-alt"></i></span>
                                <input name="location" 
                                       id="plant_location"
                                       class="form-control form-control-modern border-start-0 @error('location') is-invalid @enderror" 
                                       value="{{ old('location', $plant->location) }}" 
                                       placeholder="e.g. Chembarambakkam, Chennai, Tamil Nadu - 600123"
                                       minlength="3"
                                       maxlength="200">
                            </div>
                            @error('location')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_location"></div>
                        </div>

                        <!-- Operations Contact Email -->
                        <div class="col-md-6">
                            <label class="form-modern-label" for="plant_email">Operations Contact Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       name="contact_email" 
                                       id="plant_email"
                                       class="form-control form-control-modern border-start-0 @error('contact_email') is-invalid @enderror" 
                                       value="{{ old('contact_email', $plant->contact_email) }}" 
                                       placeholder="plant1.ops@shibaura-machine.in"
                                       minlength="5"
                                       maxlength="100">
                            </div>
                            @error('contact_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_email"></div>
                        </div>

                        <!-- Desk / Helpdesk Phone -->
                        <div class="col-md-6">
                            <label class="form-modern-label" for="plant_phone">Desk / Helpdesk Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input type="tel" 
                                       name="contact_phone" 
                                       id="plant_phone"
                                       class="form-control form-control-modern border-start-0 @error('contact_phone') is-invalid @enderror" 
                                       value="{{ old('contact_phone', $plant->contact_phone) }}" 
                                       placeholder="e.g. 9844268120"
                                       minlength="10"
                                       maxlength="10"
                                       pattern="^[6-9][0-9]{9}$">
                            </div>
                            @error('contact_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_phone"></div>
                        </div>

                        <!-- Facility Description & Specialization -->
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-modern-label mb-0" for="plant_description">Facility Description & Specialization</label>
                                <span class="small text-muted char-count" data-target="plant_description">0 / 1000</span>
                            </div>
                            <textarea name="description" 
                                      id="plant_description"
                                      rows="3" 
                                      class="form-control form-control-modern @error('description') is-invalid @enderror" 
                                      placeholder="e.g. Machining Centers, Horizontal Boring, Injection Molding Demo Cell, and Customer Experience Zone"
                                      minlength="5"
                                      maxlength="1000">{{ old('description', $plant->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_plant_description"></div>
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold text-dark">Operational Status</div>
                                <div class="small text-muted">Active plants appear in user assignment, visit logs, and feedback reporting.</div>
                            </div>
                            <div class="form-check form-switch fs-5 m-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="plant_act" @checked(old('is_active', $plant->is_active ?? true))>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary" id="btnSubmitPlant">
                            <i class="bi bi-check-lg"></i> {{ $plant->exists ? 'Save Changes' : 'Register Facility' }}
                        </button>
                        <a href="{{ route('plants.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Info Sidebar (Col-lg-4) -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>Plant Hierarchy</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon-bubble shibaura" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">User Assignment</h6>
                        <p class="small text-muted mb-0">Tour organizers and engineers can be mapped to specific plants. When visitors arrive, feedback will be tracked by facility.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="stat-icon-bubble emerald" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Multi-Plant Ready</h6>
                        <p class="small text-muted mb-0">You can create multiple manufacturing units, divisions, and experience labs across locations.</p>
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
    const form = document.getElementById('plantForm');
    if (!form) return;

    // Field configuration for real-time keystroke/paste sanitization
    const fieldConfigs = {
        plant_code: {
            label: 'Plant Code',
            disallowedRegex: /[^A-Za-z0-9\-_]/g,
            autoUppercase: true,
            min: 2,
            max: 20,
            required: true,
            msg: 'Only letters, numbers, hyphens (-) and underscores (_) are allowed (No spaces).'
        },
        plant_name: {
            label: 'Facility / Division Name',
            disallowedRegex: /[<>{}\[\]$^*~=\\\|]/g,
            min: 2,
            max: 100,
            required: true,
            msg: 'Special symbols like < > { } [ ] $ ^ * = \\ | are not permitted.'
        },
        plant_location: {
            label: 'Factory Location / Address',
            disallowedRegex: /[<>{}\[\]$^*~=\\\|]/g,
            min: 3,
            max: 200,
            required: false,
            msg: 'Special symbols like < > { } [ ] $ ^ * = \\ | are not permitted.'
        },
        plant_email: {
            label: 'Operations Contact Email',
            disallowedRegex: /[^a-zA-Z0-9@._+\-]/g,
            emailPattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            min: 5,
            max: 100,
            required: false,
            msg: 'Spaces and symbols other than @, ., _, +, - are not allowed in email.'
        },
        plant_phone: {
            label: 'Desk / Helpdesk Phone',
            disallowedRegex: /[^0-9]/g,
            min: 10,
            max: 10,
            required: false,
            phonePattern: /^[6-9][0-9]{9}$/,
            msg: 'Phone must be exactly 10 digits starting with 6, 7, 8, or 9.'
        },
        plant_description: {
            label: 'Facility Description',
            disallowedRegex: /[<>{}\[\]$^*~=\\\|]/g,
            min: 5,
            max: 1000,
            required: false,
            msg: 'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
        }
    };

    // Helper: Show discreet live error notice under input when invalid char is typed
    function showLiveErr(inputId, msg, persistent = false) {
        const errEl = document.getElementById('live_err_' + inputId);
        const input = document.getElementById(inputId);
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

    function clearLiveErr(inputId) {
        const errEl = document.getElementById('live_err_' + inputId);
        const input = document.getElementById(inputId);
        if (input) input.classList.remove('is-invalid');
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
    }

    // Helper: Update description counter
    const descInput = document.getElementById('plant_description');
    const descCounter = document.querySelector('.char-count[data-target="plant_description"]');
    function updateDescCounter() {
        if (descInput && descCounter) {
            descCounter.textContent = `${descInput.value.length} / 1000`;
        }
    }
    if (descInput) {
        updateDescCounter();
        descInput.addEventListener('input', updateDescCounter);
    }

    // Apply seamless keystroke & paste filtering across fields
    Object.keys(fieldConfigs).forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        const cfg = fieldConfigs[id];

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

        // 3. Typing / Input event fallback
        input.addEventListener('input', function() {
            if (cfg.autoUppercase) {
                this.value = this.value.toUpperCase();
            }

            if (cfg.disallowedRegex && cfg.disallowedRegex.test(this.value)) {
                this.value = this.value.replace(cfg.disallowedRegex, '');
                showLiveErr(id, cfg.msg);
            } else if (this.value.trim().length >= (cfg.min || 1)) {
                clearLiveErr(id);
            }

            if (this.value.length > cfg.max) {
                this.value = this.value.substring(0, cfg.max);
            }
        });

        // 4. Paste event
        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && cfg.disallowedRegex && cfg.disallowedRegex.test(text)) {
                e.preventDefault();
                let cleaned = text.replace(cfg.disallowedRegex, '');
                if (cfg.autoUppercase) cleaned = cleaned.toUpperCase();
                document.execCommand('insertText', false, cleaned);
                showLiveErr(id, 'Unwanted characters removed from pasted text.');
            }
        });

        // 5. Blur event (minlength check)
        input.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val.length > 0 && cfg.min && val.length < cfg.min) {
                showLiveErr(id, `Minimum ${cfg.min} characters required.`);
            } else if (val.length > 0 && cfg.phonePattern && !cfg.phonePattern.test(val)) {
                showLiveErr(id, 'Must be a 10-digit number starting with 6, 7, 8, or 9.');
            } else if (val.length > 0 && cfg.emailPattern && !cfg.emailPattern.test(val)) {
                showLiveErr(id, 'Please enter a valid official email address with domain (e.g. plant@shibaura-machine.co.in).');
            } else if (val.length > 0) {
                clearLiveErr(id);
            }
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let hasError = false;
        let firstInvalid = null;

        Object.keys(fieldConfigs).forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;

            const cfg = fieldConfigs[id];
            const val = input.value.trim();

            if (cfg.required && val.length === 0) {
                hasError = true;
                showLiveErr(id, `${cfg.label} is required.`, true);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.min && val.length < cfg.min) {
                hasError = true;
                showLiveErr(id, `${cfg.label} must be at least ${cfg.min} characters.`, true);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > cfg.max) {
                hasError = true;
                showLiveErr(id, `${cfg.label} cannot exceed ${cfg.max} characters.`, true);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.phonePattern && !cfg.phonePattern.test(val)) {
                hasError = true;
                showLiveErr(id, 'Must be a 10-digit number starting with 6, 7, 8, or 9.', true);
                if (!firstInvalid) firstInvalid = input;
            } else if (val.length > 0 && cfg.emailPattern && !cfg.emailPattern.test(val)) {
                hasError = true;
                showLiveErr(id, 'Please enter a valid official email address with domain (e.g. plant@shibaura-machine.co.in).', true);
                if (!firstInvalid) firstInvalid = input;
            }
        });

        if (hasError) {
            e.preventDefault();
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>
@endpush
