@extends('layouts.app')
@section('title', 'Company Profile & Master Brand Identity')
@section('page_title', 'Brand Identity & Master Profile')
@section('page_subtitle', 'Configure global company attributes, contact channels, and visual branding synced across web, mobile, and APIs')

@section('topbar_actions')
@endsection

@section('content')
<!-- CMS Unified Navigation Tabs -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div class="cms-hub-nav">
        <a href="{{ route('company.edit') }}" class="cms-hub-tab active">
            <i class="bi bi-building-gear"></i>
            <span>Company Identity</span>
        </a>
        <a href="{{ route('banners.index') }}" class="cms-hub-tab">
            <i class="bi bi-images"></i>
            <span>Promotional Banners</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="/api/cms/company" target="_blank" class="badge-modern badge-slate text-decoration-none py-2 px-3" title="Inspect Public REST API Endpoint">
            <i class="bi bi-code-slash text-primary"></i> <span>REST API: /api/cms/company</span>
        </a>
    </div>
</div>

<form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data" id="companyMasterForm" novalidate>
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column: Master Information Form -->
        <div class="col-12 col-xl-7">
            
            <!-- Card 1: Brand & Digital Presence -->
            <div class="cms-form-card">
                <div class="cms-form-card-header">
                    <div class="cms-icon-badge">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div>
                        <h4 class="cms-form-card-title">Corporate Identity & Visual Branding</h4>
                        <p class="cms-form-card-subtitle">Official entity trade name, official website, and corporate logo asset</p>
                    </div>
                </div>

                <div class="cms-form-card-body">
                    <!-- Company Name -->
                    <div class="mb-4">
                        <label class="form-label-modern required" for="name">Official Company / Corporate Entity Name</label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-building"></i>
                            <input type="text" 
                                   class="form-control-modern @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $company->name) }}" 
                                   placeholder="e.g. Shibaura Machine India Private Limited" 
                                   minlength="2"
                                   maxlength="150"
                                   required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_name"></div>
                    </div>

                    <!-- Website -->
                    <div class="mb-4">
                        <label class="form-label-modern" for="website">Official Corporate Website URL</label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-globe2"></i>
                            <input type="url" 
                                   class="form-control-modern @error('website') is-invalid @enderror" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website', $company->website) }}" 
                                   placeholder="https://www.shibaura-machine.co.in"
                                   maxlength="200">
                        </div>
                        @error('website')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Logo Upload Dropzone -->
                    <div>
                        <label class="form-label-modern" for="logo">Corporate Logo Graphic</label>
                        <div class="modern-upload-dropzone" id="logoDropzone">
                            <input type="file" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   onchange="handleLogoSelect(this)">
                            <div class="upload-dropzone-content">
                                <div class="upload-logo-current-preview">
                                    <img id="formLogoThumbnail" 
                                         src="{{ $company->logo_url }}" 
                                         alt="Logo Thumbnail"
                                         onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="upload-instructions-title d-flex align-items-center gap-2">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-5"></i>
                                        <span id="logoFileNameText">Upload New Logo or Drag & Drop</span>
                                    </div>
                                    <p class="upload-instructions-desc">
                                        PNG, JPG, WebP, SVG (Transparent background recommended • Max 3MB)
                                    </p>
                                </div>
                                <button type="button" class="btn-modern-secondary btn-sm px-3" style="pointer-events: none;">
                                    Browse
                                </button>
                            </div>
                        </div>
                        @error('logo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card 2: Contact Communication Channels -->
            <div class="cms-form-card">
                <div class="cms-form-card-header">
                    <div class="cms-icon-badge" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                        <i class="bi bi-telephone-inbound"></i>
                    </div>
                    <div>
                        <h4 class="cms-form-card-title">Official Communication Channels</h4>
                        <p class="cms-form-card-subtitle">Primary email and telephone lines displayed on visitor tablet app & invoices</p>
                    </div>
                </div>

                <div class="cms-form-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="email">Official Email</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-envelope"></i>
                                <input type="email" 
                                       class="form-control-modern @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $company->email) }}" 
                                       placeholder="contact@company.com">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_email"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="phone">Primary Phone (10 Digits)</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-telephone"></i>
                                <input type="tel" 
                                       class="form-control-modern @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $company->phone) }}" 
                                       placeholder="e.g. 9844268200"
                                       maxlength="10"
                                       minlength="10"
                                       pattern="^[6-9][0-9]{9}$"
                                       title="10-digit mobile/phone number starting with 6, 7, 8, or 9">
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_phone"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="alter_phone">Alternate Phone (10 Digits)</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-phone"></i>
                                <input type="tel" 
                                       class="form-control-modern @error('alter_phone') is-invalid @enderror" 
                                       id="alter_phone" 
                                       name="alter_phone" 
                                       value="{{ old('alter_phone', $company->alter_phone) }}" 
                                       placeholder="e.g. 9844268201"
                                       maxlength="10"
                                       minlength="10"
                                       pattern="^[6-9][0-9]{9}$"
                                       title="10-digit mobile/phone number starting with 6, 7, 8, or 9">
                            </div>
                            @error('alter_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_alter_phone"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Manufacturing Facility Location & Postal Address -->
            <div class="cms-form-card">
                <div class="cms-form-card-header">
                    <div class="cms-icon-badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h4 class="cms-form-card-title">Headquarters & Manufacturing Address</h4>
                        <p class="cms-form-card-subtitle">Complete physical address and regional territory attributes</p>
                    </div>
                </div>

                <div class="cms-form-card-body">
                    <!-- Street Address: FULL WIDTH -->
                    <div class="mb-3">
                        <label class="form-label-modern" for="address">Street / Plant Address</label>
                        <textarea class="form-control-modern @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3" 
                                  placeholder="Plant Plot No, Chennai-Bangalore Highway, Industrial Estate, Landmark"
                                  minlength="3"
                                  maxlength="500">{{ old('address', $company->address) }}</textarea>
                        @error('address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- City, State, Pincode in 3 clean columns -->
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="city">City</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-pin-map"></i>
                                <input type="text" 
                                       class="form-control-modern @error('city') is-invalid @enderror" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city', $company->city) }}" 
                                       placeholder="Chennai"
                                       minlength="2"
                                       maxlength="50">
                            </div>
                            @error('city')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="state">State / Province</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-map"></i>
                                <input type="text" 
                                       class="form-control-modern @error('state') is-invalid @enderror" 
                                       id="state" 
                                       name="state" 
                                       value="{{ old('state', $company->state) }}" 
                                       placeholder="Tamil Nadu"
                                       minlength="2"
                                       maxlength="50">
                            </div>
                            @error('state')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="pincode">Pincode / Postal Code</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-mailbox"></i>
                                <input type="text" 
                                       class="form-control-modern @error('pincode') is-invalid @enderror" 
                                       id="pincode" 
                                       name="pincode" 
                                       value="{{ old('pincode', $company->pincode) }}" 
                                       placeholder="600123"
                                       minlength="6"
                                       maxlength="6"
                                       pattern="^[1-9][0-9]{5}$"
                                       title="6-digit postal PIN code">
                            </div>
                            @error('pincode')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_pincode"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Corporate Overview / Tagline -->
            <div class="cms-form-card">
                <div class="cms-form-card-header">
                    <div class="cms-icon-badge" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
                        <i class="bi bi-chat-left-quote"></i>
                    </div>
                    <div>
                        <h4 class="cms-form-card-title">Corporate Overview & Industrial Tagline</h4>
                        <p class="cms-form-card-subtitle">Summary statement displayed in customer feedback introductions</p>
                    </div>
                </div>

                <div class="cms-form-card-body">
                    <textarea class="form-control-modern @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Brief summary of company manufacturing capabilities, engineering heritage, and customer excellence mission..."
                              minlength="5"
                              maxlength="1000">{{ old('description', $company->description) }}</textarea>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Sticky Bottom Form Actions Bar -->
            <div class="cms-form-card p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    <span>Updates instantly apply across all client portals</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="reset" class="btn-modern-secondary px-3" onclick="resetLivePreview()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn-modern-primary px-4 shadow-sm" id="btnSaveCompany">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Save Company Profile</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Right Column: Executive Brand Passport Preview -->
        <div class="col-12 col-xl-5">
            <div class="sticky-top" style="top: 85px;">
                
                <!-- Section Header -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="fw-bold text-dark fs-6 mb-0">
                            <i class="bi bi-patch-check-fill text-primary me-1"></i>
                            <span>Executive Brand Identity Passport</span>
                        </h4>
                        <small class="text-muted">Real-time simulator of how your brand renders across the suite</small>
                    </div>
                    <span class="badge-modern badge-sky">Live Preview</span>
                </div>

                <!-- Executive Holographic Brand Card -->
                <div class="executive-brand-card mb-4" id="executiveCard">
                    <!-- Topbar with Seal -->
                    <div class="brand-card-topbar">
                        <div class="brand-card-chip">
                            <i class="bi bi-shield-fill-check text-info"></i>
                            <span>Enterprise Verified</span>
                        </div>
                        <div class="brand-card-seal">
                            <i class="bi bi-award-fill"></i> Master Profile
                        </div>
                    </div>

                    <!-- Logo Emblem & Name -->
                    <div class="brand-card-emblem-row">
                        <div class="brand-card-logo-frame">
                            <img id="liveLogoPreview" 
                                 src="{{ $company->logo_url }}" 
                                 alt="Brand Logo"
                                 onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                        </div>
                        <div class="brand-card-name-group">
                            <div class="brand-card-name" id="liveNamePreview">{{ $company->name }}</div>
                            <div class="brand-card-location-tag">
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                <span id="liveCityStatePreview">{{ $company->city ? $company->city . ', ' . $company->state : 'Chennai, Tamil Nadu' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Glass Info Panel -->
                    <div class="brand-card-info-glass">
                        <div class="brand-card-info-item">
                            <span class="brand-card-info-label">
                                <i class="bi bi-envelope"></i> Email
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="brand-card-info-value" id="liveEmailPreview">{{ $company->email ?: 'customercare@shibaura-machine.co.in' }}</span>
                                <button type="button" class="brand-card-copy-btn" onclick="copyBrandField('liveEmailPreview')" title="Copy email">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="brand-card-info-item">
                            <span class="brand-card-info-label">
                                <i class="bi bi-telephone"></i> Phone
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="brand-card-info-value" id="livePhonePreview">{{ $company->phone ?: '+91 44 2681 2000' }}</span>
                                <button type="button" class="brand-card-copy-btn" onclick="copyBrandField('livePhonePreview')" title="Copy phone">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="brand-card-info-item">
                            <span class="brand-card-info-label">
                                <i class="bi bi-globe"></i> Website
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="brand-card-info-value" id="liveWebsitePreview">{{ $company->website ?: 'https://www.shibaura-machine.co.in' }}</span>
                                <button type="button" class="brand-card-copy-btn" onclick="copyBrandField('liveWebsitePreview')" title="Copy website URL">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="brand-card-info-item">
                            <span class="brand-card-info-label">
                                <i class="bi bi-pin-map"></i> Pincode
                            </span>
                            <span class="brand-card-info-value" id="livePincodePreview">{{ $company->pincode ?: '600123' }}</span>
                        </div>
                    </div>

                    <!-- Description Box -->
                    <div class="brand-card-desc-box" id="liveDescPreview">
                        "{{ $company->description ?: 'Precision industrial machinery engineered with Japanese precision.' }}"
                    </div>
                </div>

                <!-- Integration Status Checklist Card -->
                <div class="cms-form-card">
                    <div class="cms-form-card-header py-3">
                        <i class="bi bi-cpu-fill text-primary fs-5"></i>
                        <div>
                            <h5 class="cms-form-card-title fs-7">Active Sync Channels</h5>
                            <p class="cms-form-card-subtitle">Endpoints & screens consuming this company profile</p>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                <span class="d-flex align-items-center gap-2 text-dark fw-medium">
                                    <span class="status-dot active"></span>
                                    <span>Visitor Mobile / Tablet App</span>
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                                    Live on Native App & APIs
                                </span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                <span class="d-flex align-items-center gap-2 text-dark fw-medium">
                                    <span class="status-dot active"></span>
                                    <span>Web Admin Brand Header</span>
                                </span>
                                <span class="badge-modern badge-success py-0" style="font-size: 0.7rem;">Live Synced</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                <span class="d-flex align-items-center gap-2 text-dark fw-medium">
                                    <span class="status-dot active"></span>
                                    <span>JSON REST API Endpoint</span>
                                </span>
                                <a href="/api/cms/company" target="_blank" class="text-primary fw-bold text-decoration-none" style="font-size: 0.76rem;">
                                    /api/cms/company
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

<style>
/* Input Icon Wrapper with sleek positioning */
.input-icon-wrapper {
    position: relative;
    width: 100%;
}
.input-icon-wrapper > i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 1rem;
    pointer-events: none;
    z-index: 2;
    transition: color 0.2s ease;
}
.input-icon-wrapper:focus-within > i {
    color: #06539d;
}
.input-icon-wrapper > .form-control-modern {
    padding-left: 2.75rem !important;
    width: 100% !important;
}

/* Copy Toast */
.copy-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #0f172a;
    color: #ffffff;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideUpToast 0.25s ease;
}
@keyframes slideUpToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
// Live file selection preview
function handleLogoSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        const nameTxt = document.getElementById('logoFileNameText');
        if (nameTxt) {
            nameTxt.innerHTML = `<span class="text-primary fw-bold">${file.name}</span> (${(file.size / 1024).toFixed(1)} KB)`;
        }

        reader.onload = function(e) {
            const liveImg = document.getElementById('liveLogoPreview');
            const formThumb = document.getElementById('formLogoThumbnail');
            if (liveImg) liveImg.src = e.target.result;
            if (formThumb) formThumb.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Copy to clipboard helper
function copyBrandField(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent.trim();
    if (!text) return;

    navigator.clipboard.writeText(text).then(() => {
        showCopyToast('Copied: ' + text);
    }).catch(() => {
        showCopyToast('Copied to clipboard');
    });
}

function showCopyToast(msg) {
    const existing = document.querySelector('.copy-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'copy-toast';
    toast.innerHTML = `<i class="bi bi-check2-circle text-success fs-5"></i> <span>${msg}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s ease';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 2200);
}

// Real-time reactive simulator updates
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('companyMasterForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const alterPhoneInput = document.getElementById('alter_phone');
    const websiteInput = document.getElementById('website');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const pincodeInput = document.getElementById('pincode');
    const descInput = document.getElementById('description');

    function showCompanyLiveErr(id, msg, persistent = false) {
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

    function clearCompanyLiveErr(id) {
        const errEl = document.getElementById('live_err_' + id);
        const input = document.getElementById(id);
        if (input) input.classList.remove('is-invalid');
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
    }

    const disallowedChars = /[<>{}\[\]$^*~=\\\|]/g;
    const phoneRegex = /^[6-9][0-9]{9}$/;
    const pinRegex = /^[1-9][0-9]{5}$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    function updateLocation() {
        const city = (cityInput ? cityInput.value.trim() : '') || 'Chennai';
        const state = (stateInput ? stateInput.value.trim() : '') || 'Tamil Nadu';
        const target = document.getElementById('liveCityStatePreview');
        if (target) target.textContent = `${city}, ${state}`;
    }

    if (nameInput) {
        nameInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && disallowedChars.test(e.key)) {
                e.preventDefault();
                showCompanyLiveErr('name', 'Special characters like < > { } [ ] $ ^ * = \\ | are not allowed.');
            }
        });
        nameInput.addEventListener('beforeinput', e => {
            if (e.data && disallowedChars.test(e.data)) {
                e.preventDefault();
                showCompanyLiveErr('name', 'Special characters like < > { } [ ] $ ^ * = \\ | are not allowed.');
            }
        });
        nameInput.addEventListener('input', e => {
            if (disallowedChars.test(e.target.value)) {
                e.target.value = e.target.value.replace(disallowedChars, '');
                showCompanyLiveErr('name', 'Special characters like < > { } [ ] $ ^ * = \\ | are not allowed.');
            } else if (e.target.value.trim().length >= 2) {
                clearCompanyLiveErr('name');
            }
            const el = document.getElementById('liveNamePreview');
            if (el) el.textContent = e.target.value.trim() || 'Company Entity Name';
        });
    }

    if (emailInput) {
        const emailDisallowed = /[^a-zA-Z0-9@._+\-]/;
        emailInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && emailDisallowed.test(e.key)) {
                e.preventDefault();
                showCompanyLiveErr('email', 'Spaces and symbols other than @, ., _, +, - are not allowed in email.');
            }
        });
        emailInput.addEventListener('beforeinput', e => {
            if (e.data && emailDisallowed.test(e.data)) {
                e.preventDefault();
                showCompanyLiveErr('email', 'Spaces and symbols other than @, ., _, +, - are not allowed in email.');
            }
        });
        emailInput.addEventListener('input', e => {
            if (emailDisallowed.test(e.target.value)) {
                e.target.value = e.target.value.replace(/[^a-zA-Z0-9@._+\-]/g, '');
            } else if (emailRegex.test(e.target.value.trim()) || !e.target.value.trim()) {
                clearCompanyLiveErr('email');
            }
            const el = document.getElementById('liveEmailPreview');
            if (el) el.textContent = e.target.value.trim() || 'contact@company.com';
        });
        emailInput.addEventListener('blur', e => {
            const val = e.target.value.trim();
            if (val && !emailRegex.test(val)) {
                showCompanyLiveErr('email', 'Please enter a valid company email address with domain (e.g. info@company.com).');
            }
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/[0-9]/.test(e.key)) {
                e.preventDefault();
                showCompanyLiveErr('phone', 'Only digits 0-9 are allowed in Phone.');
            }
        });
        phoneInput.addEventListener('beforeinput', e => {
            if (e.data && !/^[0-9]+$/.test(e.data)) {
                e.preventDefault();
                showCompanyLiveErr('phone', 'Only digits 0-9 are allowed in Phone.');
            }
        });
        phoneInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
            if (phoneRegex.test(e.target.value) || !e.target.value) {
                clearCompanyLiveErr('phone');
            }
            const el = document.getElementById('livePhonePreview');
            if (el) el.textContent = e.target.value.trim() || '+91 000 000 0000';
        });
        phoneInput.addEventListener('blur', e => {
            const val = e.target.value.trim();
            if (val && !phoneRegex.test(val)) {
                showCompanyLiveErr('phone', 'Phone must be a 10-digit number starting with 6, 7, 8, or 9.');
            }
        });
    }

    if (alterPhoneInput) {
        alterPhoneInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/[0-9]/.test(e.key)) {
                e.preventDefault();
                showCompanyLiveErr('alter_phone', 'Only digits 0-9 are allowed in Phone.');
            }
        });
        alterPhoneInput.addEventListener('beforeinput', e => {
            if (e.data && !/^[0-9]+$/.test(e.data)) {
                e.preventDefault();
                showCompanyLiveErr('alter_phone', 'Only digits 0-9 are allowed in Phone.');
            }
        });
        alterPhoneInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
            if (phoneRegex.test(e.target.value) || !e.target.value) {
                clearCompanyLiveErr('alter_phone');
            }
        });
        alterPhoneInput.addEventListener('blur', e => {
            const val = e.target.value.trim();
            if (val && !phoneRegex.test(val)) {
                showCompanyLiveErr('alter_phone', 'Alternate Phone must be a 10-digit number starting with 6, 7, 8, or 9.');
            }
        });
    }

    if (websiteInput) {
        websiteInput.addEventListener('input', e => {
            const el = document.getElementById('liveWebsitePreview');
            if (el) el.textContent = e.target.value.trim() || 'https://www.example.com';
        });
    }

    const addrInput = document.getElementById('address');
    if (addrInput) {
        addrInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && disallowedChars.test(e.key)) {
                e.preventDefault();
            }
        });
        addrInput.addEventListener('input', e => {
            if (disallowedChars.test(e.target.value)) {
                e.target.value = e.target.value.replace(disallowedChars, '');
            }
        });
    }

    if (cityInput) {
        cityInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/[a-zA-Z\s\.\-]/.test(e.key)) {
                e.preventDefault();
            }
        });
        cityInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^a-zA-Z\s\.\-]/g, '').slice(0, 50);
            updateLocation();
        });
    }

    if (stateInput) {
        stateInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/[a-zA-Z\s\.\-]/.test(e.key)) {
                e.preventDefault();
            }
        });
        stateInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^a-zA-Z\s\.\-]/g, '').slice(0, 50);
            updateLocation();
        });
    }

    if (pincodeInput) {
        pincodeInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/[0-9]/.test(e.key)) {
                e.preventDefault();
                showCompanyLiveErr('pincode', 'Only digits 0-9 are allowed.');
            }
        });
        pincodeInput.addEventListener('beforeinput', e => {
            if (e.data && !/^[0-9]+$/.test(e.data)) {
                e.preventDefault();
                showCompanyLiveErr('pincode', 'Only digits 0-9 are allowed.');
            }
        });
        pincodeInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (pinRegex.test(e.target.value) || !e.target.value) {
                clearCompanyLiveErr('pincode');
            }
            const el = document.getElementById('livePincodePreview');
            if (el) el.textContent = e.target.value.trim() || '—';
        });
        pincodeInput.addEventListener('blur', e => {
            const val = e.target.value.trim();
            if (val && !pinRegex.test(val)) {
                showCompanyLiveErr('pincode', 'Pincode must be exactly 6 digits.');
            }
        });
    }

    if (descInput) {
        descInput.addEventListener('keydown', e => {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && disallowedChars.test(e.key)) {
                e.preventDefault();
            }
        });
        descInput.addEventListener('input', e => {
            if (disallowedChars.test(e.target.value)) {
                e.target.value = e.target.value.replace(disallowedChars, '');
            }
            const el = document.getElementById('liveDescPreview');
            if (el) {
                const val = e.target.value.trim();
                el.textContent = val ? `"${val}"` : `"Precision industrial manufacturing and customer excellence."`;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            let firstInvalid = null;

            const nameVal = nameInput ? nameInput.value.trim() : '';
            if (!nameVal) {
                hasError = true;
                showCompanyLiveErr('name', 'Official Company Name is required.', true);
                if (!firstInvalid) firstInvalid = nameInput;
            } else if (nameVal.length < 2) {
                hasError = true;
                showCompanyLiveErr('name', 'Company Name must be at least 2 characters.', true);
                if (!firstInvalid) firstInvalid = nameInput;
            }

            if (emailInput && emailInput.value.trim()) {
                if (!emailRegex.test(emailInput.value.trim())) {
                    hasError = true;
                    showCompanyLiveErr('email', 'Please enter a valid company email address with domain (e.g. info@company.com).', true);
                    if (!firstInvalid) firstInvalid = emailInput;
                }
            }

            if (phoneInput && phoneInput.value.trim()) {
                if (!phoneRegex.test(phoneInput.value.trim())) {
                    hasError = true;
                    showCompanyLiveErr('phone', 'Primary Phone must be a 10-digit number starting with 6, 7, 8, or 9.', true);
                    if (!firstInvalid) firstInvalid = phoneInput;
                }
            }

            if (alterPhoneInput && alterPhoneInput.value.trim()) {
                if (!phoneRegex.test(alterPhoneInput.value.trim())) {
                    hasError = true;
                    showCompanyLiveErr('alter_phone', 'Alternate Phone must be a 10-digit number starting with 6, 7, 8, or 9.', true);
                    if (!firstInvalid) firstInvalid = alterPhoneInput;
                }
            }

            if (pincodeInput && pincodeInput.value.trim()) {
                if (!pinRegex.test(pincodeInput.value.trim())) {
                    hasError = true;
                    showCompanyLiveErr('pincode', 'Pincode must be exactly 6 digits.', true);
                    if (!firstInvalid) firstInvalid = pincodeInput;
                }
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

    // Drag and drop events for dropzone
    const dropzone = document.getElementById('logoDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(name => {
            dropzone.addEventListener(name, e => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(name => {
            dropzone.addEventListener(name, e => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });
    }
});

function resetLivePreview() {
    setTimeout(() => {
        const evt = new Event('input');
        ['name', 'email', 'phone', 'website', 'city', 'state', 'pincode', 'description'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.dispatchEvent(evt);
        });
    }, 50);
}
</script>
@endsection
