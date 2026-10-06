@extends('layouts.app')
@section('title', 'Company Profile & Master Brand Identity')
@section('page_title', 'Brand Identity & Master Profile')
@section('page_subtitle', 'Configure global company attributes, contact channels, and visual branding synced across web, mobile, and APIs')

@section('topbar_actions')
    <a href="{{ route('mobile.app') }}" target="_blank" class="btn-modern-primary btn-sm">
        <i class="bi bi-phone"></i>
        <span>Preview in App</span>
    </a>
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

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm border-0 mb-4" role="alert" style="background: #ecfdf5; border-left: 4px solid #10b981 !important;">
        <i class="bi bi-check-circle-fill text-success fs-4"></i>
        <div>
            <div class="fw-bold text-dark">Changes Saved Successfully</div>
            <div class="small text-secondary">{{ session('success') }}</div>
        </div>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger p-3 rounded-3 shadow-sm border-0 mb-4" style="background: #fef2f2; border-left: 4px solid #ef4444 !important;">
        <div class="fw-bold text-danger mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span>Please correct the errors below before saving:</span>
        </div>
        <ul class="mb-0 ps-3 small text-danger">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data" id="companyMasterForm">
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
                                   required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
                                   placeholder="https://www.shibaura-machine.co.in">
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
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="phone">Primary Phone</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-telephone"></i>
                                <input type="text" 
                                       class="form-control-modern @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $company->phone) }}" 
                                       placeholder="+91 44 2681 2000">
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-modern" for="alter_phone">Alternate Phone</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-phone"></i>
                                <input type="text" 
                                       class="form-control-modern @error('alter_phone') is-invalid @enderror" 
                                       id="alter_phone" 
                                       name="alter_phone" 
                                       value="{{ old('alter_phone', $company->alter_phone) }}" 
                                       placeholder="+91 44 2681 2001">
                            </div>
                            @error('alter_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
                                  placeholder="Plant Plot No, Chennai-Bangalore Highway, Industrial Estate, Landmark">{{ old('address', $company->address) }}</textarea>
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
                                       placeholder="Chennai">
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
                                       placeholder="Tamil Nadu">
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
                                       placeholder="600123">
                            </div>
                            @error('pincode')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
                              placeholder="Brief summary of company manufacturing capabilities, engineering heritage, and customer excellence mission...">{{ old('description', $company->description) }}</textarea>
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
                                <a href="{{ route('mobile.app') }}" target="_blank" class="text-primary fw-bold text-decoration-none" style="font-size: 0.76rem;">
                                    View <i class="bi bi-box-arrow-up-right"></i>
                                </a>
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
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const websiteInput = document.getElementById('website');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const pincodeInput = document.getElementById('pincode');
    const descInput = document.getElementById('description');

    function updateLocation() {
        const city = (cityInput ? cityInput.value.trim() : '') || 'Chennai';
        const state = (stateInput ? stateInput.value.trim() : '') || 'Tamil Nadu';
        const target = document.getElementById('liveCityStatePreview');
        if (target) target.textContent = `${city}, ${state}`;
    }

    if (nameInput) {
        nameInput.addEventListener('input', e => {
            const el = document.getElementById('liveNamePreview');
            if (el) el.textContent = e.target.value.trim() || 'Company Entity Name';
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', e => {
            const el = document.getElementById('liveEmailPreview');
            if (el) el.textContent = e.target.value.trim() || 'contact@company.com';
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', e => {
            const el = document.getElementById('livePhonePreview');
            if (el) el.textContent = e.target.value.trim() || '+91 000 000 0000';
        });
    }

    if (websiteInput) {
        websiteInput.addEventListener('input', e => {
            const el = document.getElementById('liveWebsitePreview');
            if (el) el.textContent = e.target.value.trim() || 'https://www.example.com';
        });
    }

    if (cityInput) cityInput.addEventListener('input', updateLocation);
    if (stateInput) stateInput.addEventListener('input', updateLocation);

    if (pincodeInput) {
        pincodeInput.addEventListener('input', e => {
            const el = document.getElementById('livePincodePreview');
            if (el) el.textContent = e.target.value.trim() || '—';
        });
    }

    if (descInput) {
        descInput.addEventListener('input', e => {
            const el = document.getElementById('liveDescPreview');
            if (el) {
                const val = e.target.value.trim();
                el.textContent = val ? `"${val}"` : `"Precision industrial manufacturing and customer excellence."`;
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
