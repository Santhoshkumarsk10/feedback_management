@extends('layouts.app')
@php
    $isEdit = $banner->exists;
    $title = $isEdit ? 'Edit Promotional Banner' : 'Upload New Banner';
@endphp
@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $isEdit ? "Update banner display settings and asset: #{$banner->id}" : 'Add a new graphical banner for Mobile, Tablet, and Web displays')

@section('topbar_actions')
    <a href="{{ route('banners.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Banners</span>
    </a>
@endsection

@section('content')
<div class="row g-4 align-items-start">
    <!-- Left Column: Form -->
    <div class="col-12 col-lg-7">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div>
                    <div class="card-title">
                        <i class="bi bi-image-fill text-primary"></i>
                        <span>{{ $isEdit ? 'Update Banner Details' : 'Banner Information & Creative Asset' }}</span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">Configure banner target audience, order priority, and image file</p>
                </div>
                @if($isEdit)
                    <span class="badge-modern badge-shibaura">ID: {{ $banner->id }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form action="{{ $isEdit ? route('banners.update', $banner) : route('banners.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      id="bannerForm"
                      novalidate>
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <!-- Headline & Subtitle -->
                    <div class="mb-3">
                        <label class="form-label-modern required" for="bannerTitleInput">Banner Title / Headline</label>
                        <input type="text" 
                               class="form-control-modern @error('title') is-invalid @enderror" 
                               id="bannerTitleInput" 
                               name="title" 
                               value="{{ old('title', $banner->title) }}" 
                               placeholder="e.g. Leading Precision Injection Moulding Solutions" 
                               minlength="2"
                               maxlength="100"
                               required
                               pattern="^[\p{L}\p{N}\s\-–—_&/,\.()\'’&quot;!\?:;%]+$"
                               title="The banner title contains invalid characters.">
                        <small class="text-muted d-block mt-1">Primary headline displayed over the banner</small>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_bannerTitleInput"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern" for="bannerSubtitleInput">Subtitle / Supporting Text</label>
                        <input type="text" 
                               class="form-control-modern @error('subtitle') is-invalid @enderror" 
                               id="bannerSubtitleInput" 
                               name="subtitle" 
                               value="{{ old('subtitle', $banner->subtitle) }}" 
                               placeholder="e.g. Empowering Indian Manufacturing with Japanese Engineering Excellence" 
                               minlength="2"
                               maxlength="150"
                               pattern="^[\p{L}\p{N}\s\-–—_&/,\.()\'’&quot;!\?:;%]+$"
                               title="The subtitle contains invalid characters.">
                        <small class="text-muted d-block mt-1">Secondary caption or tagline displayed below headline</small>
                        @error('subtitle')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_bannerSubtitleInput"></div>
                    </div>

                    <!-- Target Device & Sort Order -->
                    <div class="row g-3 mb-3 align-items-start">
                        <div class="col-12 col-md-6">
                            <label class="form-label-modern required" for="target">Target Display Device</label>
                            <div class="banner-custom-select-wrap">
                                <x-custom-select
                                    name="target"
                                    :options="[
                                        ['value' => 'all', 'label' => 'All Platforms (Mobile, Tablet, Web)'],
                                        ['value' => 'mobile', 'label' => 'Mobile App Only'],
                                        ['value' => 'tablet', 'label' => 'Tablet Kiosk App Only'],
                                        ['value' => 'web', 'label' => 'Web Management Only'],
                                    ]"
                                    :value="old('target', $banner->target ?? 'all')"
                                    placeholder="Select Target Device"
                                    icon="bi-display"
                                    :auto-submit="false"
                                    :allow-empty="false"
                                />
                            </div>
                            <small class="text-muted d-block mt-1">Platform where this banner will appear</small>
                            @error('target')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label-modern required" for="sort_order">Display Priority / Sort Order</label>
                            <input type="number" 
                                   class="form-control-modern @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', $banner->sort_order ?? 0) }}" 
                                   min="0" 
                                   max="9999"
                                   required>
                            <small class="text-muted d-block mt-1">Lower numbers appear first (e.g. 1, 2, 3)</small>
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback d-none custom-live-err" id="live_err_sort_order"></div>
                        </div>
                    </div>

                    <!-- Image File Upload Dropzone -->
                    <div class="mb-3">
                        <label class="form-label-modern {{ $isEdit ? '' : 'required' }}" for="bannerImageInput">Banner Graphic Asset</label>
                        <div class="modern-upload-dropzone" id="bannerDropzone">
                            <input type="file" 
                                   id="bannerImageInput" 
                                   name="image" 
                                   accept="image/*" 
                                   onchange="previewBannerFile(this)"
                                   {{ $isEdit ? '' : 'required' }}>
                            <div class="upload-dropzone-content d-flex align-items-center gap-3">
                                <div class="upload-logo-current-preview flex-shrink-0" style="width: 110px; height: 62px; border-radius: 8px; overflow: hidden; background: #0f172a; border: 1.5px solid #e2e8f0; display: flex; align-items: center; justify-content: center; padding: 4px;">
                                    <img id="formBannerThumbnail" 
                                         src="{{ $isEdit ? $banner->image_url : asset('images/shibaura-logo-cropped.webp') }}" 
                                         alt="Banner Thumbnail"
                                         style="object-fit: contain; width: 100%; height: 100%;"
                                         onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="upload-instructions-title d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-5"></i>
                                        <span class="fw-semibold text-dark text-truncate" id="bannerFileNameText">{{ $isEdit ? 'Upload Replacement Image or Drag & Drop' : 'Upload Banner Image or Drag & Drop' }}</span>
                                    </div>
                                    <p class="upload-instructions-desc mb-0 text-muted small">
                                        JPG, PNG, WebP, GIF • 16:9 Landscape recommended • Max 5MB
                                    </p>
                                </div>
                                <button type="button" class="btn-modern-secondary btn-sm px-3 flex-shrink-0" style="pointer-events: none;">
                                    Browse
                                </button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_bannerImageInput"></div>
                    </div>

                    <!-- Link URL -->
                    <div class="mb-3">
                        <label class="form-label-modern" for="link_url">Click Destination URL (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0" style="border: 1.5px solid #e2e8f0; border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-link-45deg fs-5"></i></span>
                            <input type="url" 
                                   class="form-control-modern @error('link_url') is-invalid @enderror" 
                                   style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                   id="link_url" 
                                   name="link_url" 
                                   value="{{ old('link_url', $banner->link_url) }}" 
                                   placeholder="https://www.shibaura-machine.co.in/machines"
                                   maxlength="500">
                        </div>
                        <small class="text-muted d-block mt-1">Optional destination URL opened when banner is tapped</small>
                        @error('link_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_link_url"></div>
                    </div>

                    <!-- Active Status Switch in Clean Container -->
                    <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-check-label fw-bold text-dark d-block cursor-pointer mb-1" for="is_active">
                                    Publish & Active Status
                                </label>
                                <small class="text-muted">Display immediately in visitor welcome carousel and mobile slider</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input ms-0 mt-0" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       style="width: 2.75rem; height: 1.45rem; cursor: pointer;"
                                       {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top">
                        <a href="{{ route('banners.index') }}" class="btn-modern-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn-modern-primary px-4" id="btnSubmitBanner">
                            <i class="bi {{ $isEdit ? 'bi-check2' : 'bi-cloud-arrow-up-fill' }}"></i>
                            <span>{{ $isEdit ? 'Update Banner' : 'Create & Publish Banner' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive App Simulator Preview -->
    <div class="col-12 col-lg-5">
        <div class="card-modern sticky-top" style="top: 85px; border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div>
                    <div class="card-title">
                        <i class="bi bi-phone-fill text-primary"></i>
                        <span>App Screen Simulation</span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">Live preview in visitor kiosk & mobile slider</p>
                </div>
                <span class="badge-modern badge-sky d-inline-flex align-items-center gap-1">
                    <span class="status-dot green" style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    Live Visual
                </span>
            </div>

            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <p class="text-muted small mb-0">
                        Multi-device preview of this promotional banner creative:
                    </p>
                    <button type="button" 
                            class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-pill d-inline-flex align-items-center gap-1" 
                            id="btnToggleSimFit" 
                            onclick="toggleSimulatorImageFit()" 
                            title="Toggle Fit / Cover image framing"
                            style="font-size: 0.72rem;">
                        <i class="bi bi-arrows-angle-expand" id="simFitIcon"></i>
                        <span id="simFitText">Fill Bleed</span>
                    </button>
                </div>

                <!-- Device Mockup Frame (Dynamically resizes via dots) -->
                <div class="mobile-mockup-wrapper mx-auto sim-mode-tablet" id="simDeviceWrapper">
                    
                    <!-- Mobile Chrome Bar (Visible on mobile mode) -->
                    <div class="sim-chrome-mobile d-none align-items-center justify-content-between px-2 mb-2 text-white-50" id="simChromeMobile" style="font-size: 0.7rem;">
                        <span class="fw-semibold">09:41</span>
                        <div class="mockup-speaker"></div>
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-wifi" style="font-size: 0.75rem;"></i>
                            <i class="bi bi-battery-full" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>

                    <!-- Tablet Kiosk Chrome Bar (Visible on tablet mode - Default) -->
                    <div class="sim-chrome-tablet d-flex align-items-center justify-content-between px-2 mb-2 text-white-50" id="simChromeTablet" style="font-size: 0.7rem;">
                        <span class="fw-semibold text-info d-flex align-items-center gap-1">
                            <i class="bi bi-display"></i> Reception Kiosk 10.1"
                        </span>
                        <div class="mockup-camera-dot"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span>10:30 AM</span>
                            <i class="bi bi-wifi" style="font-size: 0.75rem;"></i>
                            <i class="bi bi-lock-fill" style="font-size: 0.7rem;" title="Kiosk Lockdown"></i>
                        </div>
                    </div>

                    <!-- Web Desktop Chrome Bar (Visible on web mode) -->
                    <div class="sim-chrome-web d-none align-items-center justify-content-between px-2 mb-2" id="simChromeWeb" style="font-size: 0.7rem;">
                        <div class="d-flex align-items-center gap-1">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        </div>
                        <div class="px-2 py-0 bg-slate-800 text-slate-300 rounded text-center small font-monospace" style="font-size: 0.65rem; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <i class="bi bi-lock-fill text-success me-1"></i>shibaura.portal/visitor-welcome
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-dash"></i>
                            <i class="bi bi-square ms-1" style="font-size: 0.6rem;"></i>
                        </div>
                    </div>

                    <!-- Banner Slide Card -->
                    <div class="sim-banner-card" id="simBannerCard">
                        <div class="sim-banner-image-wrap">
                            <!-- Ambient blurred backdrop mirroring the image colors -->
                            <div class="sim-banner-backdrop" id="simBannerBackdrop" style="background-image: url('{{ $isEdit ? $banner->image_url : asset('images/shibaura-logo-cropped.webp') }}');"></div>
                            
                            <!-- Foreground Banner Graphic (Exist Image displayed cleanly) -->
                            <img id="simBannerImg" 
                                 src="{{ $isEdit ? $banner->image_url : asset('images/shibaura-logo-cropped.webp') }}" 
                                 alt="Banner Preview" 
                                 class="sim-banner-img sim-fit-contain"
                                 onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'; const bg=document.getElementById('simBannerBackdrop'); if(bg) bg.style.backgroundImage='url({{ asset('images/shibaura-logo-cropped.webp') }})';">
                            
                        </div>
                    </div>

                    <!-- Interactive Screen Size Dots -->
                    <div class="d-flex flex-column align-items-center mt-3">
                        <div class="sim-screen-dots-bar d-flex justify-content-center align-items-center gap-2" role="tablist" aria-label="Device Screen Simulator">
                            <button type="button" 
                                    class="sim-screen-dot" 
                                    data-screen="mobile" 
                                    title="Mobile Screen (Phone 360×640 px)" 
                                    aria-label="Switch to Mobile screen size"
                                    onclick="switchSimulatorScreen('mobile', this)">
                                <span class="dot-indicator"></span>
                            </button>
                            <button type="button" 
                                    class="sim-screen-dot active" 
                                    data-screen="tablet" 
                                    title="Tablet Kiosk Screen (16:9 Landscape)" 
                                    aria-label="Switch to Tablet Kiosk screen size"
                                    onclick="switchSimulatorScreen('tablet', this)">
                                <span class="dot-indicator"></span>
                            </button>
                            <button type="button" 
                                    class="sim-screen-dot" 
                                    data-screen="web" 
                                    title="Desktop Web Screen (1920×1080 px)" 
                                    aria-label="Switch to Desktop Web screen size"
                                    onclick="switchSimulatorScreen('web', this)">
                                <span class="dot-indicator"></span>
                            </button>
                        </div>
                        <div class="sim-screen-caption mt-2 text-center">
                            <span class="badge bg-slate-800 text-info border border-slate-700 px-2 py-1" id="simScreenBadge" style="font-size: 0.75rem;">
                                <i class="bi bi-tablet-landscape me-1"></i> Tablet Kiosk (16:9 Landscape)
                            </span>
                            <div class="text-white-50 mt-1" style="font-size: 0.68rem;">
                                Click dots above to view the same image on Mobile, Tablet & Desktop
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Quick Specs Strip -->
                <div class="row g-2 mt-3 text-center" id="simSpecsStrip">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border border-slate-200">
                            <div class="text-muted" style="font-size: 0.68rem; font-weight: 600;">ASPECT RATIO</div>
                            <div class="fw-bold text-dark small" id="simSpecAspectRatio">16:9</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border border-slate-200">
                            <div class="text-muted" style="font-size: 0.68rem; font-weight: 600;">SCREEN TYPE</div>
                            <div class="fw-bold text-dark small" id="simSpecScreenType">Tablet 10.1"</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border border-slate-200">
                            <div class="text-muted" style="font-size: 0.68rem; font-weight: 600;">DEVICE TARGET</div>
                            <div class="fw-bold text-primary small text-truncate" id="simTargetPill">
                                {{ strtoupper($banner->target ?? 'ALL') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optimization Box -->
                <div class="bg-light-subtle rounded-3 p-3 border mt-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span class="fw-bold small text-dark">Optimization Recommendation</span>
                    </div>
                    <ul class="mb-0 small text-secondary ps-3" style="line-height: 1.55;">
                        <li>Maintain clean contrast if placing text directly in the banner creative.</li>
                        <li>Recommended aspect ratio: <strong>16:9</strong> (Landscape) for tablet & kiosk displays.</li>
                        <li>Supported file formats: <strong>JPG, PNG, WebP, GIF, SVG</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.banner-custom-select-wrap .custom-select-wrapper {
    width: 100% !important;
    display: block !important;
}
.banner-custom-select-wrap .custom-select-trigger {
    min-height: 43px;
    height: 43px;
    padding: 0.65rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.88rem;
    color: var(--slate-800);
    background: #ffffff;
    box-sizing: border-box;
}
.banner-custom-select-wrap .custom-select-dropdown {
    width: 100% !important;
}

.mobile-mockup-wrapper {
    max-width: 400px;
    background: #0f172a;
    border-radius: 20px;
    padding: 14px 14px 16px;
    box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.4);
    border: 3px solid #1e293b;
    transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), border-radius 0.35s ease;
}

.mobile-mockup-wrapper.sim-mode-mobile {
    max-width: 310px;
    border-radius: 28px;
}
.mobile-mockup-wrapper.sim-mode-tablet {
    max-width: 400px;
    border-radius: 18px;
}
.mobile-mockup-wrapper.sim-mode-web {
    max-width: 470px;
    border-radius: 12px;
}

.mockup-speaker {
    width: 44px;
    height: 4px;
    border-radius: 4px;
    background: #334155;
}

.mockup-camera-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #0f172a;
    border: 1.5px solid #334155;
}

.sim-banner-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #0b1120;
    height: 215px;
    transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.sim-banner-image-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #0b1120;
}

.sim-banner-backdrop {
    position: absolute;
    inset: -20px;
    background-size: cover;
    background-position: center;
    filter: blur(20px) brightness(0.5);
    opacity: 0.65;
    transform: scale(1.15);
    transition: background-image 0.25s ease;
}

.sim-banner-img {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    transition: all 0.25s ease;
}

.sim-banner-img.sim-fit-contain {
    object-fit: contain;
    padding: 10px;
}

.sim-banner-img.sim-fit-cover {
    object-fit: cover;
    object-position: center;
    padding: 0;
}

.sim-banner-gradient-overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(15,23,42,0.05) 0%, rgba(15,23,42,0.25) 45%, rgba(15,23,42,0.88) 100%);
    pointer-events: none;
    z-index: 2;
}

.sim-banner-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 12px 14px;
    z-index: 3;
}

.sim-badge {
    background: rgba(2, 132, 199, 0.9);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 5px;
}

.sim-title {
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 700;
    margin-bottom: 3px;
    line-height: 1.25;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

.sim-sub {
    color: #cbd5e1;
    font-size: 0.72rem;
    margin-bottom: 0;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Screen Size Switcher Dots */
.sim-screen-dots-bar {
    padding: 4px 8px;
    background: rgba(30, 41, 59, 0.7);
    border-radius: 20px;
    border: 1px solid rgba(51, 65, 85, 0.6);
}

.sim-screen-dot {
    background: transparent;
    border: none;
    padding: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}

.sim-screen-dot .dot-indicator {
    display: block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #64748b;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.sim-screen-dot:hover .dot-indicator {
    background: #cbd5e1;
    transform: scale(1.15);
}

.sim-screen-dot.active .dot-indicator {
    width: 22px;
    border-radius: 6px;
    background: #38bdf8;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
}
</style>

<script>
function showBannerLiveErr(id, msg, persistent = false) {
    const errEl = document.getElementById('live_err_' + id);
    const input = document.getElementById(id);
    if (input) input.classList.add('is-invalid');
    if (id === 'bannerImageInput') {
        const dropzone = document.getElementById('bannerDropzone');
        if (dropzone) dropzone.classList.add('border-danger');
    }
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

function clearBannerLiveErr(id) {
    const errEl = document.getElementById('live_err_' + id);
    const input = document.getElementById(id);
    if (input) input.classList.remove('is-invalid');
    if (id === 'bannerImageInput') {
        const dropzone = document.getElementById('bannerDropzone');
        if (dropzone) dropzone.classList.remove('border-danger');
    }
    if (errEl) {
        errEl.classList.remove('d-block');
        errEl.classList.add('d-none');
    }
}

function previewBannerFile(input) {
    if (input.files && input.files[0]) {
        clearBannerLiveErr('bannerImageInput');
        const file = input.files[0];
        const reader = new FileReader();

        const nameTxt = document.getElementById('bannerFileNameText');
        if (nameTxt) {
            nameTxt.innerHTML = `<span class="text-primary fw-bold">${file.name}</span> (${(file.size / 1024).toFixed(1)} KB)`;
        }

        reader.onload = function(e) {
            const preview = document.getElementById('simBannerImg');
            const thumb = document.getElementById('formBannerThumbnail');
            const backdrop = document.getElementById('simBannerBackdrop');
            if (preview) preview.src = e.target.result;
            if (thumb) thumb.src = e.target.result;
            if (backdrop) backdrop.style.backgroundImage = `url("${e.target.result}")`;
        };
        reader.readAsDataURL(file);
    }
}

const SIMULATOR_SCREENS = {
    mobile: {
        id: 'mobile',
        name: 'Mobile App Screen',
        badgeHtml: '<i class="bi bi-phone me-1"></i> Mobile App (360×640 px)',
        aspectRatio: '9:16 / 4:3',
        screenType: 'Phone 360px',
        cardHeight: '185px',
        wrapperClass: 'sim-mode-mobile'
    },
    tablet: {
        id: 'tablet',
        name: 'Tablet Kiosk Screen',
        badgeHtml: '<i class="bi bi-tablet-landscape me-1"></i> Tablet Kiosk (16:9 Landscape)',
        aspectRatio: '16:9 (Landscape)',
        screenType: 'Tablet 10.1"',
        cardHeight: '215px',
        wrapperClass: 'sim-mode-tablet'
    },
    web: {
        id: 'web',
        name: 'Desktop Web Screen',
        badgeHtml: '<i class="bi bi-display me-1"></i> Desktop Web (1920×1080 px)',
        aspectRatio: '21:9 (Panoramic)',
        screenType: 'Desktop 1920px',
        cardHeight: '165px',
        wrapperClass: 'sim-mode-web'
    }
};

let currentSimScreen = 'tablet';

function switchSimulatorScreen(screenKey, btnEl) {
    const cfg = SIMULATOR_SCREENS[screenKey];
    if (!cfg) return;
    currentSimScreen = screenKey;

    // Update active dot
    document.querySelectorAll('.sim-screen-dot').forEach(dot => {
        dot.classList.toggle('active', dot === btnEl || dot.getAttribute('data-screen') === screenKey);
    });

    // Update wrapper mode
    const wrapper = document.getElementById('simDeviceWrapper');
    if (wrapper) {
        wrapper.classList.remove('sim-mode-mobile', 'sim-mode-tablet', 'sim-mode-web');
        wrapper.classList.add(cfg.wrapperClass);
    }

    // Toggle chrome bars
    const chromeMobile = document.getElementById('simChromeMobile');
    const chromeTablet = document.getElementById('simChromeTablet');
    const chromeWeb = document.getElementById('simChromeWeb');

    if (chromeMobile) {
        chromeMobile.classList.toggle('d-none', screenKey !== 'mobile');
        chromeMobile.classList.toggle('d-flex', screenKey === 'mobile');
    }
    if (chromeTablet) {
        chromeTablet.classList.toggle('d-none', screenKey !== 'tablet');
        chromeTablet.classList.toggle('d-flex', screenKey === 'tablet');
    }
    if (chromeWeb) {
        chromeWeb.classList.toggle('d-none', screenKey !== 'web');
        chromeWeb.classList.toggle('d-flex', screenKey === 'web');
    }

    // Update card height
    const card = document.getElementById('simBannerCard');
    if (card) {
        card.style.height = cfg.cardHeight;
    }

    // Update badge & label
    const badge = document.getElementById('simScreenBadge');
    if (badge) {
        badge.innerHTML = cfg.badgeHtml;
    }

    // Update specs strip
    const specAspect = document.getElementById('simSpecAspectRatio');
    const specScreen = document.getElementById('simSpecScreenType');
    if (specAspect) specAspect.textContent = cfg.aspectRatio;
    if (specScreen) specScreen.textContent = cfg.screenType;
}

function toggleSimulatorImageFit() {
    const img = document.getElementById('simBannerImg');
    const fitText = document.getElementById('simFitText');
    const fitIcon = document.getElementById('simFitIcon');
    if (!img) return;

    if (img.classList.contains('sim-fit-contain')) {
        img.classList.remove('sim-fit-contain');
        img.classList.add('sim-fit-cover');
        if (fitText) fitText.textContent = 'Fit Full';
        if (fitIcon) fitIcon.className = 'bi bi-arrows-angle-contract';
    } else {
        img.classList.remove('sim-fit-cover');
        img.classList.add('sim-fit-contain');
        if (fitText) fitText.textContent = 'Fill Bleed';
        if (fitIcon) fitIcon.className = 'bi bi-arrows-angle-expand';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bannerForm');
    const titleInp = document.getElementById('bannerTitleInput');
    const subInp = document.getElementById('bannerSubtitleInput');
    const sortInp = document.getElementById('sort_order');
    const imageInp = document.getElementById('bannerImageInput');
    const linkInp = document.getElementById('link_url');

    const forbiddenCharRegex = /[<>{}\[\]$^*~=\\\|]/;
    const disallowedChars = /[<>{}\[\]$^*~=\\\|]/g;

    function bindBannerInputInterceptors(input, id, previewId, maxLen, fallbackText, msg) {
        if (!input) return;

        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (forbiddenCharRegex.test(e.key)) {
                    e.preventDefault();
                    showBannerLiveErr(id, msg);
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
                    if (forbiddenCharRegex.test(e.data[i])) {
                        e.preventDefault();
                        showBannerLiveErr(id, msg);
                        return;
                    }
                }
            }
        });

        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && disallowedChars.test(text)) {
                e.preventDefault();
                let cleaned = text.replace(disallowedChars, '');
                if (maxLen) {
                    const avail = maxLen - (this.value.length - (this.selectionEnd - this.selectionStart));
                    if (avail > 0) cleaned = cleaned.slice(0, avail);
                    else cleaned = '';
                }
                document.execCommand('insertText', false, cleaned);
            }
        });

        input.addEventListener('input', function() {
            if (disallowedChars.test(this.value)) {
                this.value = this.value.replace(disallowedChars, '');
                showBannerLiveErr(id, msg);
            } else if (this.value.trim().length >= 2 || (id === 'bannerSubtitleInput' && this.value.trim().length === 0)) {
                clearBannerLiveErr(id);
            }
            if (maxLen && this.value.length > maxLen) {
                this.value = this.value.slice(0, maxLen);
            }
            const el = document.getElementById(previewId);
            if (el) el.textContent = this.value.trim() || fallbackText;
        });
    }

    bindBannerInputInterceptors(
        titleInp,
        'bannerTitleInput',
        'simBannerTitle',
        100,
        'Banner Title Headline',
        'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
    );

    bindBannerInputInterceptors(
        subInp,
        'bannerSubtitleInput',
        'simBannerSubtitle',
        200,
        'Supporting subtitle caption text',
        'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.'
    );

    if (sortInp) {
        sortInp.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                clearBannerLiveErr('sort_order');
            }
        });
    }

    if (linkInp) {
        linkInp.addEventListener('input', function() {
            if (!this.value.trim() || /^https?:\/\//i.test(this.value.trim())) {
                clearBannerLiveErr('link_url');
            }
        });
    }

    const targetInp = form ? form.querySelector('input[name="target"]') : null;
    const simBadge = document.getElementById('simTargetBadge');
    const simPill = document.getElementById('simTargetPill');
    if (targetInp && (simBadge || simPill)) {
        const targetLabels = {
            'all': 'ALL PLATFORMS',
            'mobile': 'MOBILE APP',
            'tablet': 'TABLET KIOSK',
            'web': 'WEB ONLY'
        };
        const shortLabels = {
            'all': 'ALL',
            'mobile': 'MOBILE',
            'tablet': 'TABLET',
            'web': 'WEB'
        };
        targetInp.addEventListener('change', function() {
            const v = this.value || 'all';
            if (simBadge) simBadge.textContent = targetLabels[v] || v.toUpperCase();
            if (simPill) simPill.textContent = shortLabels[v] || v.toUpperCase();
            if (v === 'mobile' || v === 'tablet' || v === 'web') {
                const targetDot = document.querySelector(`.sim-screen-dot[data-screen="${v}"]`);
                if (targetDot) switchSimulatorScreen(v, targetDot);
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            let firstInvalid = null;

            const titleVal = titleInp ? titleInp.value.trim() : '';
            if (!titleVal) {
                hasError = true;
                showBannerLiveErr('bannerTitleInput', 'Banner Title / Headline is required.', true);
                if (!firstInvalid) firstInvalid = titleInp;
            } else if (titleVal.length < 2) {
                hasError = true;
                showBannerLiveErr('bannerTitleInput', 'Banner Title must be at least 2 characters.', true);
                if (!firstInvalid) firstInvalid = titleInp;
            }

            const sortVal = sortInp ? sortInp.value.trim() : '';
            if (sortVal === '' || isNaN(sortVal)) {
                hasError = true;
                showBannerLiveErr('sort_order', 'Display priority / sort order is required.', true);
                if (!firstInvalid) firstInvalid = sortInp;
            }

            @if(!$isEdit)
            if (imageInp && (!imageInp.files || imageInp.files.length === 0)) {
                hasError = true;
                showBannerLiveErr('bannerImageInput', 'Banner Graphic Asset image is required.', true);
                if (!firstInvalid) firstInvalid = imageInp;
            }
            @endif

            if (linkInp && linkInp.value.trim()) {
                if (!/^https?:\/\//i.test(linkInp.value.trim())) {
                    hasError = true;
                    showBannerLiveErr('link_url', 'Destination URL must start with http:// or https://', true);
                    if (!firstInvalid) firstInvalid = linkInp;
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
});
</script>
@endsection
