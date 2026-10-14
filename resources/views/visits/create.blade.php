@extends('layouts.app')
@section('title', 'Register Plant Visitor')
@section('page_title', 'Register Plant Visitor')
@section('page_subtitle', 'Record new visitor arrival, escort assignment & duty shift')

@section('hide_top_errors', true)

@section('topbar_actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shift_queue') }}" class="btn btn-outline-primary btn-sm fw-semibold shadow-xs">
            <i class="bi bi-clock-history me-1"></i> Live Shift Queue
        </a>
        <a href="{{ route('dashboard') }}" class="btn-modern-secondary btn-sm" title="Back to Dashboard">
            <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- Top Navigation Bar & Breadcrumb -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2 text-muted small">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
            <i class="bi bi-chevron-right fs-8"></i>
            <a href="{{ route('visits.index') }}" class="text-decoration-none text-muted">Plant Visitors</a>
            <i class="bi bi-chevron-right fs-8"></i>
            <span class="text-dark fw-bold">New Registration</span>
        </div>
        <div>
            <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Visitors List
            </a>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9">
            <div class="card-modern shadow-card border-0">
                <!-- Card Header -->
                <div class="card-header p-3 p-md-4 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-bubble cyan flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-person-plus-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0 fs-6">Visitor Registration & Shift Assignment</h5>
                            <div class="small text-muted">Complete guest credentials to issue immediate evaluation pass</div>
                        </div>
                    </div>
                    @if($currentShift)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                            <span class="status-pulse-dot success"></span>
                            <span>Active Duty: <strong>{{ $currentShift->name }}</strong> ({{ $currentShift->formatted_24h_range }})</span>
                        </span>
                    @endif
                </div>

                <!-- Card Body Form -->
                <div class="card-body p-3 p-md-4">
                    <form action="{{ route('visits.store') }}" method="POST" id="registerVisitorForm" novalidate>
                        @csrf

                        <!-- SECTION 1: OPERATIONAL SHIFT SELECTION -->
                        <div class="mb-4">
                            <div class="form-section-title">
                                <i class="bi bi-clock-history text-primary"></i> 1. Operational Duty Shift <span class="text-danger">*</span>
                            </div>
                            <div id="shiftSelectionContainer" class="shift-selection-grid">
                                <div class="row g-2">
                                    @foreach($shifts as $s)
                                        @php
                                            $isCurrent = $currentShift && $currentShift->id == $s->id;
                                            $isSelected = old('shift_id', $currentShift?->id) == $s->id;
                                        @endphp
                                        <div class="col-12 col-md-4">
                                            <label class="shift-radio-card {{ $isSelected ? 'active' : '' }}" onclick="selectShift(this)">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <input type="radio" name="shift_id" value="{{ $s->id }}" class="form-check-input mt-0 shift-radio-input" {{ $isSelected ? 'checked' : '' }} required>
                                                        <strong class="text-dark">{{ $s->name }}</strong>
                                                    </div>
                                                    @if($isCurrent)
                                                        <span class="badge bg-success text-white" style="font-size: 0.65rem;">
                                                            <i class="bi bi-lightning-fill"></i> Current
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.78rem;">
                                                    <span><i class="bi bi-clock me-1"></i> {{ $s->formatted_24h_range }}</span>
                                                    <span class="small fw-semibold text-secondary">({{ $s->formatted_12h_range }})</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div id="feedback_shift_id" class="validation-feedback-box error {{ $errors->has('shift_id') ? '' : 'd-none' }}">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="feedback_shift_id_text">{{ $errors->first('shift_id') ?: 'Please select an operational duty shift.' }}</span>
                            </div>
                        </div>

                        <!-- SECTION 2: VISITOR & COMPANY IDENTITY -->
                        <div class="mb-4">
                            <div class="form-section-title">
                                <i class="bi bi-person-vcard text-primary"></i> 2. Guest Identity & Organization
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitorNameInput" class="form-label fw-bold text-dark small mb-0">
                                            Visitor Full Name <span class="text-danger">*</span>
                                        </label>
                                        <span id="visitorNameCount" class="validation-counter text-muted">0/100</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge" id="badge_visitor_name"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="visitor_name" id="visitorNameInput" 
                                               class="form-control @error('visitor_name') is-invalid @enderror" 
                                               value="{{ old('visitor_name') }}" placeholder="e.g. Rajesh Kumar" 
                                               required maxlength="100" autocomplete="name" autofocus>
                                    </div>
                                    <div id="feedback_visitor_name" class="validation-feedback-box error {{ $errors->has('visitor_name') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visitor_name_text">{{ $errors->first('visitor_name') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitorCompanyInput" class="form-label fw-bold text-dark small mb-0">
                                            Company / Organization <span class="text-danger">*</span>
                                        </label>
                                        <span id="visitorCompanyCount" class="validation-counter text-muted">0/150</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge" id="badge_visitor_company"><i class="bi bi-building-fill"></i></span>
                                        <input type="text" name="visitor_company" id="visitorCompanyInput" 
                                               class="form-control @error('visitor_company') is-invalid @enderror" 
                                               value="{{ old('visitor_company') }}" placeholder="e.g. TVS Motor Company Ltd" 
                                               required maxlength="150" autocomplete="organization">
                                    </div>
                                    <div id="feedback_visitor_company" class="validation-feedback-box error {{ $errors->has('visitor_company') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visitor_company_text">{{ $errors->first('visitor_company') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitorDesignationInput" class="form-label fw-bold text-dark small mb-0">Designation / Role</label>
                                        <span id="visitorDesignationCount" class="validation-counter text-muted">0/150</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge" id="badge_visitor_designation"><i class="bi bi-briefcase-fill"></i></span>
                                        <input type="text" name="visitor_designation" id="visitorDesignationInput" 
                                               class="form-control @error('visitor_designation') is-invalid @enderror" 
                                               value="{{ old('visitor_designation') }}" placeholder="e.g. Plant Head / Tooling Lead" maxlength="150">
                                    </div>
                                    <div id="feedback_visitor_designation" class="validation-feedback-box error {{ $errors->has('visitor_designation') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visitor_designation_text">{{ $errors->first('visitor_designation') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="organizerSelect" class="form-label fw-bold text-dark small mb-0">Tour Escort / SMI Engineer</label>
                                        <span class="text-muted small" style="font-size: 0.72rem;">Optional</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge"><i class="bi bi-person-badge-fill"></i></span>
                                        <select name="organizer_id" id="organizerSelect" class="form-select @error('organizer_id') is-invalid @enderror">
                                            <option value="">— Select Tour Escort / Organizer —</option>
                                            @foreach($organizers as $org)
                                                <option value="{{ $org->id }}" {{ old('organizer_id', auth()->id()) == $org->id ? 'selected' : '' }}>
                                                    {{ $org->name }} ({{ ucfirst($org->role ?? 'Staff') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('organizer_id')
                                        <div class="validation-feedback-box error"><i class="bi bi-exclamation-circle-fill"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: CONTACT & SCHEDULE -->
                        <div class="mb-4">
                            <div class="form-section-title">
                                <i class="bi bi-telephone-inbound text-primary"></i> 3. Contact & Visit Schedule
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitorMobileInput" class="form-label fw-bold text-dark small mb-0">Mobile Contact Number</label>
                                        <span id="visitorMobileCounterBadge" class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Optional</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge font-monospace fw-bold" id="badge_visitor_mobile" style="font-size: 0.8rem;">+91</span>
                                        <input type="tel" name="visitor_mobile" id="visitorMobileInput" 
                                               class="form-control @error('visitor_mobile') is-invalid @enderror" 
                                               value="{{ old('visitor_mobile') }}" placeholder="10-digit mobile" 
                                               maxlength="10" autocomplete="tel">
                                    </div>
                                    <div id="feedback_visitor_mobile" class="validation-feedback-box error {{ $errors->has('visitor_mobile') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visitor_mobile_text">{{ $errors->first('visitor_mobile') }}</span>
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">10 digits starting with 6, 7, 8, or 9 (Leave blank if unavailable)</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitorEmailInput" class="form-label fw-bold text-dark small mb-0">Email Address</label>
                                        <span id="visitorEmailStatus" class="validation-counter text-muted">Optional</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge" id="badge_visitor_email"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="email" name="visitor_email" id="visitorEmailInput" 
                                               class="form-control @error('visitor_email') is-invalid @enderror" 
                                               value="{{ old('visitor_email') }}" placeholder="visitor@company.com" 
                                               maxlength="150" autocomplete="email">
                                    </div>
                                    <div id="feedback_visitor_email" class="validation-feedback-box error {{ $errors->has('visitor_email') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visitor_email_text">{{ $errors->first('visitor_email') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="visitDateInput" class="form-label fw-bold text-dark small mb-0">
                                            Visit Date <span class="text-danger">*</span>
                                        </label>
                                        <span class="text-muted small" style="font-size: 0.72rem;">YYYY-MM-DD</span>
                                    </div>
                                    <div class="input-icon-group">
                                        <span class="input-icon-badge" id="badge_visit_date"><i class="bi bi-calendar-event-fill"></i></span>
                                        <input type="date" name="visit_date" id="visitDateInput" 
                                               class="form-control @error('visit_date') is-invalid @enderror" 
                                               value="{{ old('visit_date', today()->toDateString()) }}" required>
                                    </div>
                                    <div id="feedback_visit_date" class="validation-feedback-box error {{ $errors->has('visit_date') ? '' : 'd-none' }}">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span id="feedback_visit_date_text">{{ $errors->first('visit_date') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: PURPOSE OF VISIT & QUICK PRESETS -->
                        <div class="mb-4">
                            <div class="form-section-title">
                                <i class="bi bi-compass text-primary"></i> 4. Visit Purpose & Plant Tour Context
                            </div>
                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="purposeInput" class="form-label fw-bold text-dark small mb-0">Purpose of Visit</label>
                                    <span id="purposeCount" class="validation-counter text-muted">0/255</span>
                                </div>
                                <div class="input-icon-group mb-2">
                                    <span class="input-icon-badge" id="badge_purpose"><i class="bi bi-journal-text"></i></span>
                                    <input type="text" name="purpose" id="purposeInput" 
                                           class="form-control @error('purpose') is-invalid @enderror" 
                                           value="{{ old('purpose', 'Plant Tour & Technical Demo') }}" 
                                           placeholder="e.g. Injection Molding Trial, Die Casting Witness" maxlength="255">
                                </div>
                                <div id="feedback_purpose" class="validation-feedback-box error {{ $errors->has('purpose') ? '' : 'd-none' }}">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span id="feedback_purpose_text">{{ $errors->first('purpose') }}</span>
                                </div>

                                <!-- Quick Presets -->
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-2">
                                    <span class="small text-muted me-1" style="font-size: 0.72rem;">Quick Fill:</span>
                                    <button type="button" class="preset-chip" onclick="setPurpose('Injection Molding Demo & Trial')">
                                        🎯 Injection Molding Trial
                                    </button>
                                    <button type="button" class="preset-chip" onclick="setPurpose('CNC & Machine Tools Inspection')">
                                        ⚙️ Machine Tools Inspection
                                    </button>
                                    <button type="button" class="preset-chip" onclick="setPurpose('Shibaura Technical Centre Tour & Demo')">
                                        🏭 Tech Centre Tour
                                    </button>
                                    <button type="button" class="preset-chip" onclick="setPurpose('High Pressure Die Casting Witness')">
                                        🔍 Die Casting Witness
                                    </button>
                                    <button type="button" class="preset-chip" onclick="setPurpose('Executive Customer Review & Partnership')">
                                        🤝 Client Meeting
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT ACTION BAR -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top mt-4">
                            <div class="text-muted small">
                                <i class="bi bi-shield-check text-success me-1"></i>
                                Logged entries are instantly synchronized to the duty shift queue.
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-3 py-2 fw-semibold">
                                    Cancel
                                </a>
                                <button type="submit" id="submitBtn" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Register Plant Visitor
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info & Guide Column -->
        <div class="col-12 col-xl-3">
            <!-- Shift Operation Tip Card -->
            <div class="card-modern p-3 mb-3 bg-white">
                <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold small">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Instant Queue Sync
                </div>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Newly registered visitors appear in the <strong>Live Shift Queue</strong> immediately. The on-duty tour organizer can trigger feedback collection on the plant floor.
                </p>
            </div>

            <!-- Feedback Window Notice -->
            <div class="card-modern p-3 mb-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold small">
                    <i class="bi bi-hourglass-split text-warning"></i> 30m Shift Deadline
                </div>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Feedback submissions must be completed before the shift closes. Automated alerts trigger 30 minutes before shift completion.
                </p>
            </div>

            <!-- Fast Keyboard Tip -->
            <div class="card-modern p-3 bg-light text-secondary">
                <div class="small fw-bold text-dark mb-1">
                    <i class="bi bi-keyboard me-1"></i> Form Pro Tip
                </div>
                <div class="small" style="font-size: 0.75rem; line-height: 1.4;">
                    Use the <strong>Quick Fill</strong> chips to populate visit purposes with one click. Escorts can be assigned to either the current user or another engineer.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Shift selection function
    function selectShift(labelEl) {
        document.querySelectorAll('.shift-radio-card').forEach(c => c.classList.remove('active'));
        labelEl.classList.add('active');
        const radio = labelEl.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            validateShift();
        }
    }

    // Preset purpose fill
    function setPurpose(text) {
        const input = document.getElementById('purposeInput');
        if (input) {
            input.value = text;
            validatePurpose();
            input.focus();
        }
    }

    // DOM Elements
    const form = document.getElementById('registerVisitorForm');
    const nameInput = document.getElementById('visitorNameInput');
    const companyInput = document.getElementById('visitorCompanyInput');
    const mobileInput = document.getElementById('visitorMobileInput');
    const emailInput = document.getElementById('visitorEmailInput');
    const dateInput = document.getElementById('visitDateInput');
    const designationInput = document.getElementById('visitorDesignationInput');
    const purposeInput = document.getElementById('purposeInput');
    const submitBtn = document.getElementById('submitBtn');

    // Counters & Badges
    const nameCount = document.getElementById('visitorNameCount');
    const companyCount = document.getElementById('visitorCompanyCount');
    const designationCount = document.getElementById('visitorDesignationCount');
    const purposeCount = document.getElementById('purposeCount');
    const mobileBadge = document.getElementById('visitorMobileCounterBadge');

    // Helper: Display field validation state
    function setFieldState(inputEl, feedbackEl, textEl, isValid, message = '', isOptionalEmpty = false) {
        if (!inputEl || !feedbackEl) return;

        if (isOptionalEmpty) {
            inputEl.classList.remove('is-invalid', 'is-valid');
            feedbackEl.classList.add('d-none');
            return;
        }

        if (isValid) {
            inputEl.classList.remove('is-invalid');
            inputEl.classList.add('is-valid');
            feedbackEl.classList.add('d-none');
            if (textEl) textEl.textContent = '';
        } else {
            inputEl.classList.remove('is-valid');
            inputEl.classList.add('is-invalid');
            feedbackEl.classList.remove('d-none');
            feedbackEl.classList.add('error');
            if (textEl) textEl.textContent = message;
        }
    }

    // Helper: Trigger subtle shake animation
    function triggerShake(element) {
        if (!element) return;
        element.classList.remove('field-shake');
        void element.offsetWidth; // re-flow
        element.classList.add('field-shake');
    }

    // 1. Shift Validator
    function validateShift() {
        const checked = document.querySelector('input[name="shift_id"]:checked');
        const container = document.getElementById('shiftSelectionContainer');
        const feedback = document.getElementById('feedback_shift_id');
        const feedbackText = document.getElementById('feedback_shift_id_text');

        if (!checked) {
            container.classList.add('is-invalid');
            feedback.classList.remove('d-none');
            feedbackText.textContent = 'Please select an operational duty shift.';
            return false;
        } else {
            container.classList.remove('is-invalid');
            feedback.classList.add('d-none');
            return true;
        }
    }

    // 2. Name Validator (Letters, spaces, hyphens, periods, apostrophes)
    const nameRegex = /^[\p{L}\s.\-']+$/u;
    function validateName() {
        const val = (nameInput.value || '').trim();
        const feedback = document.getElementById('feedback_visitor_name');
        const feedbackText = document.getElementById('feedback_visitor_name_text');
        if (nameCount) nameCount.textContent = `${nameInput.value.length}/100`;

        if (!val) {
            setFieldState(nameInput, feedback, feedbackText, false, 'Visitor full name is required.');
            return false;
        }
        if (val.length < 2) {
            setFieldState(nameInput, feedback, feedbackText, false, 'Visitor full name must be at least 2 characters.');
            return false;
        }
        if (val.length > 100) {
            setFieldState(nameInput, feedback, feedbackText, false, 'Visitor full name cannot exceed 100 characters.');
            return false;
        }
        if (!nameRegex.test(val)) {
            setFieldState(nameInput, feedback, feedbackText, false, 'Visitor name may only contain letters, spaces, hyphens, and dots.');
            return false;
        }

        setFieldState(nameInput, feedback, feedbackText, true);
        return true;
    }

    // 3. Company Validator (Alphanumeric, spaces, safe company punctuation)
    const companyRegex = /^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$/u;
    function validateCompany() {
        const val = (companyInput.value || '').trim();
        const feedback = document.getElementById('feedback_visitor_company');
        const feedbackText = document.getElementById('feedback_visitor_company_text');
        if (companyCount) companyCount.textContent = `${companyInput.value.length}/150`;

        if (!val) {
            setFieldState(companyInput, feedback, feedbackText, false, 'Company or organization name is required.');
            return false;
        }
        if (val.length < 2) {
            setFieldState(companyInput, feedback, feedbackText, false, 'Company name must be at least 2 characters.');
            return false;
        }
        if (val.length > 150) {
            setFieldState(companyInput, feedback, feedbackText, false, 'Company name cannot exceed 150 characters.');
            return false;
        }
        if (!companyRegex.test(val)) {
            setFieldState(companyInput, feedback, feedbackText, false, 'Company name contains disallowed characters.');
            return false;
        }

        setFieldState(companyInput, feedback, feedbackText, true);
        return true;
    }

    // 4. Mobile Validator (Optional, but if entered: exactly 10 digits starting with 6, 7, 8, or 9)
    function validateMobile() {
        // Strip non-digits in real-time
        let digits = mobileInput.value.replace(/\D/g, '').slice(0, 10);
        if (mobileInput.value !== digits) {
            mobileInput.value = digits;
        }

        const val = digits.trim();
        const feedback = document.getElementById('feedback_visitor_mobile');
        const feedbackText = document.getElementById('feedback_visitor_mobile_text');

        if (!val) {
            if (mobileBadge) {
                mobileBadge.className = 'badge bg-secondary-subtle text-secondary';
                mobileBadge.textContent = 'Optional';
            }
            setFieldState(mobileInput, feedback, feedbackText, true, '', true);
            return true;
        }

        // Live badge feedback
        if (val.length < 10) {
            if (mobileBadge) {
                mobileBadge.className = 'badge bg-warning-subtle text-warning-emphasis';
                mobileBadge.textContent = `${val.length}/10 digits`;
            }
        } else {
            if (/^[6-9][0-9]{9}$/.test(val)) {
                if (mobileBadge) {
                    mobileBadge.className = 'badge bg-success-subtle text-success';
                    mobileBadge.textContent = '10/10 (Valid)';
                }
            } else {
                if (mobileBadge) {
                    mobileBadge.className = 'badge bg-danger-subtle text-danger';
                    mobileBadge.textContent = 'Invalid prefix';
                }
            }
        }

        if (!/^[6-9]/.test(val)) {
            setFieldState(mobileInput, feedback, feedbackText, false, 'Mobile number must start with 6, 7, 8, or 9.');
            return false;
        }
        if (val.length !== 10) {
            setFieldState(mobileInput, feedback, feedbackText, false, `Mobile number must be exactly 10 digits (currently ${val.length}).`);
            return false;
        }

        setFieldState(mobileInput, feedback, feedbackText, true);
        return true;
    }

    // 5. Email Validator (Optional, but if entered: RFC-compliant domain format)
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    function validateEmail() {
        const val = (emailInput.value || '').trim();
        const feedback = document.getElementById('feedback_visitor_email');
        const feedbackText = document.getElementById('feedback_visitor_email_text');

        if (!val) {
            setFieldState(emailInput, feedback, feedbackText, true, '', true);
            return true;
        }
        if (val.length > 150) {
            setFieldState(emailInput, feedback, feedbackText, false, 'Email address cannot exceed 150 characters.');
            return false;
        }
        if (!emailRegex.test(val)) {
            setFieldState(emailInput, feedback, feedbackText, false, 'Please provide a valid email address with a domain (e.g. name@company.com).');
            return false;
        }

        setFieldState(emailInput, feedback, feedbackText, true);
        return true;
    }

    // 6. Date Validator
    function validateDate() {
        const val = (dateInput.value || '').trim();
        const feedback = document.getElementById('feedback_visit_date');
        const feedbackText = document.getElementById('feedback_visit_date_text');

        if (!val) {
            setFieldState(dateInput, feedback, feedbackText, false, 'Visit date is required.');
            return false;
        }

        setFieldState(dateInput, feedback, feedbackText, true);
        return true;
    }

    // 7. Designation Validator
    function validateDesignation() {
        const val = designationInput.value || '';
        if (designationCount) designationCount.textContent = `${val.length}/150`;
        const feedback = document.getElementById('feedback_visitor_designation');
        const feedbackText = document.getElementById('feedback_visitor_designation_text');

        if (val.length > 150) {
            setFieldState(designationInput, feedback, feedbackText, false, 'Designation cannot exceed 150 characters.');
            return false;
        }
        setFieldState(designationInput, feedback, feedbackText, true, '', val.trim().length === 0);
        return true;
    }

    // 8. Purpose Validator
    function validatePurpose() {
        const val = purposeInput.value || '';
        if (purposeCount) purposeCount.textContent = `${val.length}/255`;
        const feedback = document.getElementById('feedback_purpose');
        const feedbackText = document.getElementById('feedback_purpose_text');

        if (val.length > 255) {
            setFieldState(purposeInput, feedback, feedbackText, false, 'Purpose cannot exceed 255 characters.');
            return false;
        }
        setFieldState(purposeInput, feedback, feedbackText, true, '', val.trim().length === 0);
        return true;
    }

    // Attach real-time listeners
    if (nameInput) {
        nameInput.addEventListener('input', validateName);
        nameInput.addEventListener('blur', validateName);
    }
    if (companyInput) {
        companyInput.addEventListener('input', validateCompany);
        companyInput.addEventListener('blur', validateCompany);
    }
    if (mobileInput) {
        mobileInput.addEventListener('input', validateMobile);
        mobileInput.addEventListener('blur', validateMobile);
    }
    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
        emailInput.addEventListener('blur', validateEmail);
    }
    if (dateInput) {
        dateInput.addEventListener('change', validateDate);
        dateInput.addEventListener('blur', validateDate);
    }
    if (designationInput) {
        designationInput.addEventListener('input', validateDesignation);
    }
    if (purposeInput) {
        purposeInput.addEventListener('input', validatePurpose);
    }
    document.querySelectorAll('input[name="shift_id"]').forEach(radio => {
        radio.addEventListener('change', validateShift);
    });

    // Run initial validations and counter sync on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (nameInput && nameInput.value) {
            if (nameCount) nameCount.textContent = `${nameInput.value.length}/100`;
            validateName();
        }
        if (companyInput && companyInput.value) {
            if (companyCount) companyCount.textContent = `${companyInput.value.length}/150`;
            validateCompany();
        }
        if (mobileInput && mobileInput.value) {
            validateMobile();
        }
        if (emailInput && emailInput.value) {
            validateEmail();
        }
        if (designationInput && designationInput.value) {
            if (designationCount) designationCount.textContent = `${designationInput.value.length}/150`;
        }
        if (purposeInput && purposeInput.value) {
            if (purposeCount) purposeCount.textContent = `${purposeInput.value.length}/255`;
        }
    });

    // Form Submit Interceptor
    if (form) {
        form.addEventListener('submit', function(e) {
            const errors = [];
            let firstInvalidEl = null;

            if (!validateShift()) {
                errors.push({ msg: 'Please select an operational duty shift.', el: document.getElementById('shiftSelectionContainer') });
                if (!firstInvalidEl) firstInvalidEl = document.getElementById('shiftSelectionContainer');
            }

            if (!validateName()) {
                const msg = document.getElementById('feedback_visitor_name_text')?.textContent || 'Visitor full name is required.';
                errors.push({ msg: msg, el: nameInput });
                if (!firstInvalidEl) firstInvalidEl = nameInput;
            }

            if (!validateCompany()) {
                const msg = document.getElementById('feedback_visitor_company_text')?.textContent || 'Company or organization name is required.';
                errors.push({ msg: msg, el: companyInput });
                if (!firstInvalidEl) firstInvalidEl = companyInput;
            }

            if (!validateMobile()) {
                const msg = document.getElementById('feedback_visitor_mobile_text')?.textContent || 'Valid 10-digit mobile number starting with 6, 7, 8, or 9 is required.';
                errors.push({ msg: msg, el: mobileInput });
                if (!firstInvalidEl) firstInvalidEl = mobileInput;
            }

            if (!validateEmail()) {
                const msg = document.getElementById('feedback_visitor_email_text')?.textContent || 'Please provide a valid email address.';
                errors.push({ msg: msg, el: emailInput });
                if (!firstInvalidEl) firstInvalidEl = emailInput;
            }

            if (!validateDate()) {
                const msg = document.getElementById('feedback_visit_date_text')?.textContent || 'Visit date is required.';
                errors.push({ msg: msg, el: dateInput });
                if (!firstInvalidEl) firstInvalidEl = dateInput;
            }

            if (!validateDesignation()) {
                errors.push({ msg: 'Designation is too long (maximum 150 characters).', el: designationInput });
                if (!firstInvalidEl) firstInvalidEl = designationInput;
            }

            if (!validatePurpose()) {
                errors.push({ msg: 'Purpose is too long (maximum 255 characters).', el: purposeInput });
                if (!firstInvalidEl) firstInvalidEl = purposeInput;
            }

            if (errors.length > 0) {
                e.preventDefault();

                // Smooth scroll and focus on first invalid element
                if (firstInvalidEl) {
                    firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    triggerShake(firstInvalidEl);
                    if (typeof firstInvalidEl.focus === 'function') {
                        setTimeout(() => firstInvalidEl.focus(), 300);
                    }
                }
                return false;
            }

            // Valid submission: activate loading spinner
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Registering Visitor...';
            }
        });
    }
</script>
@endpush
