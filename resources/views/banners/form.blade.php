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
<div class="row g-4">
    <!-- Left Column: Form -->
    <div class="col-12 col-xl-7">
        <div class="card-modern">
            <div class="card-modern-header">
                <div>
                    <h3 class="card-modern-title">
                        <i class="bi bi-image-fill text-primary"></i>
                        <span>{{ $isEdit ? 'Update Banner Details' : 'Banner Information & Creative Asset' }}</span>
                    </h3>
                    <p class="card-modern-subtitle">Configure banner target audience, order priority, and image file</p>
                </div>
            </div>

            <div class="card-modern-body p-4">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger mb-4">
                        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Please resolve the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ $isEdit ? route('banners.update', $banner) : route('banners.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      id="bannerForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <!-- Headline & Subtitle -->
                    <div class="mb-3">
                        <label class="form-label-modern required" for="title">Banner Title / Headline</label>
                        <input type="text" 
                               class="form-control-modern @error('title') is-invalid @enderror" 
                               id="bannerTitleInput" 
                               name="title" 
                               value="{{ old('title', $banner->title) }}" 
                               placeholder="e.g. Leading Precision Injection Moulding Solutions" 
                               required>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern" for="subtitle">Subtitle / Supporting Text</label>
                        <input type="text" 
                               class="form-control-modern @error('subtitle') is-invalid @enderror" 
                               id="bannerSubtitleInput" 
                               name="subtitle" 
                               value="{{ old('subtitle', $banner->subtitle) }}" 
                               placeholder="e.g. Empowering Indian Manufacturing with Japanese Engineering Excellence">
                        @error('subtitle')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Target Device & Sort Order -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label-modern required">Target Display Device</label>
                            <x-custom-select
                                name="target"
                                :options="[
                                    ['value' => 'all', 'label' => 'All Platforms (Mobile, Tablet, Web)'],
                                    ['value' => 'mobile', 'label' => 'Mobile App Only'],
                                    ['value' => 'tablet', 'label' => 'Tablet Kiosk App Only'],
                                    ['value' => 'web', 'label' => 'Web Management Only'],
                                ]"
                                :selected="old('target', $banner->target ?? 'all')"
                                placeholder="Select Target Device"
                                icon="bi-display"
                            />
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
                                   required>
                            <small class="text-muted d-block mt-1">Lower numbers appear first (e.g. 1, 2, 3)</small>
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Image File Upload Dropzone -->
                    <div class="mb-4">
                        <label class="form-label-modern {{ $isEdit ? '' : 'required' }}" for="image">Banner Graphic Asset</label>
                        <div class="modern-upload-dropzone" id="bannerDropzone">
                            <input type="file" 
                                   id="bannerImageInput" 
                                   name="image" 
                                   accept="image/*"
                                   onchange="previewBannerFile(this)"
                                   {{ $isEdit ? '' : 'required' }}>
                            <div class="upload-dropzone-content">
                                <div class="upload-logo-current-preview" style="width: 100px; height: 56px;">
                                    <img id="formBannerThumbnail" 
                                         src="{{ $isEdit ? $banner->image_url : asset('images/shibaura-logo-cropped.webp') }}" 
                                         alt="Banner Thumbnail"
                                         style="object-fit: cover; width: 100%; height: 100%; border-radius: 6px;"
                                         onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="upload-instructions-title d-flex align-items-center gap-2">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-5"></i>
                                        <span id="bannerFileNameText">{{ $isEdit ? 'Upload Replacement Image or Drag & Drop' : 'Upload Banner Image or Drag & Drop' }}</span>
                                    </div>
                                    <p class="upload-instructions-desc">
                                        JPG, PNG, WebP, GIF • 16:9 Landscape recommended • Max 5MB
                                    </p>
                                </div>
                                <button type="button" class="btn-modern-secondary btn-sm px-3" style="pointer-events: none;">
                                    Browse
                                </button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Link URL -->
                    <div class="mb-4">
                        <label class="form-label-modern" for="link_url">Click Destination URL (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" 
                                   class="form-control-modern @error('link_url') is-invalid @enderror border-start-0" 
                                   id="link_url" 
                                   name="link_url" 
                                   value="{{ old('link_url', $banner->link_url) }}" 
                                   placeholder="https://www.shibaura-machine.co.in/machines">
                        </div>
                        @error('link_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Is Active Switch -->
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3 mb-4">
                        <input class="form-check-input ms-0 mt-0" 
                               type="checkbox" 
                               role="switch" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               style="width: 2.75rem; height: 1.45rem; cursor: pointer;"
                               {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="is_active">
                            Publish & Make Immediately Active in Mobile/Tablet App
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top">
                        <a href="{{ route('banners.index') }}" class="btn-modern-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn-modern-primary px-4">
                            <i class="bi {{ $isEdit ? 'bi-check2' : 'bi-cloud-arrow-up-fill' }}"></i>
                            <span>{{ $isEdit ? 'Update Banner' : 'Create & Publish Banner' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive App Simulator Preview -->
    <div class="col-12 col-xl-5">
        <div class="card-modern sticky-top" style="top: 85px;">
            <div class="card-modern-header">
                <h4 class="card-modern-title fs-6">
                    <i class="bi bi-phone text-primary"></i>
                    <span>App Screen Simulation</span>
                </h4>
                <span class="badge-modern badge-sky">Live Visual</span>
            </div>

            <div class="card-modern-body p-4">
                <p class="text-muted small mb-3">
                    Here is how this promotional banner will look inside the visitor welcome carousel and mobile slider:
                </p>

                <!-- Mobile Mockup Container -->
                <div class="mobile-mockup-wrapper mx-auto">
                    <!-- Banner Slide Item -->
                    <div class="sim-banner-card">
                        <div class="sim-banner-image-wrap">
                            <img id="simBannerImg" 
                                 src="{{ $isEdit ? $banner->image_url : asset('images/shibaura-logo-cropped.webp') }}" 
                                 alt="Banner Preview" 
                                 class="sim-banner-img"
                                 onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                            <div class="sim-banner-gradient-overlay"></div>
                        </div>

                        <div class="sim-banner-content">
                            <span class="sim-badge" id="simTargetBadge">
                                {{ strtoupper($banner->target ?? 'ALL DEVICES') }}
                            </span>
                            <h4 class="sim-title" id="simBannerTitle">
                                {{ $banner->title ?: 'Precision Engineering Excellence' }}
                            </h4>
                            <p class="sim-sub" id="simBannerSubtitle">
                                {{ $banner->subtitle ?: 'Japanese manufacturing technology for zero-defect production' }}
                            </p>
                        </div>
                    </div>

                    <!-- Slide Dots Simulation -->
                    <div class="d-flex justify-content-center gap-1 mt-3">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>

                <div class="bg-light-subtle rounded-3 p-3 border mt-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span class="fw-bold small text-dark">Optimization Recommendation</span>
                    </div>
                    <ul class="mb-0 small text-secondary ps-3">
                        <li>Maintain clean contrast if placing text directly in the banner creative.</li>
                        <li>Recommended aspect ratio: <strong>16:9</strong> (Landscape) for tablet & kiosk displays.</li>
                        <li>Maximum upload size is <strong>5 MB</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mobile-mockup-wrapper {
    max-width: 360px;
    background: #0f172a;
    border-radius: 20px;
    padding: 14px;
    box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.35);
}
.sim-banner-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #1e293b;
    height: 190px;
}
.sim-banner-image-wrap {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
.sim-banner-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.85;
}
.sim-banner-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(15,23,42,0.1) 0%, rgba(15,23,42,0.85) 100%);
}
.sim-banner-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 12px 14px;
    z-index: 2;
}
.sim-badge {
    background: rgba(2, 132, 199, 0.85);
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
.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #475569;
}
.dot.active {
    width: 18px;
    border-radius: 4px;
    background: #38bdf8;
}
</style>

<script>
function previewBannerFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        const nameTxt = document.getElementById('bannerFileNameText');
        if (nameTxt) {
            nameTxt.innerHTML = `<span class="text-primary fw-bold">${file.name}</span> (${(file.size / 1024).toFixed(1)} KB)`;
        }

        reader.onload = function(e) {
            const preview = document.getElementById('simBannerImg');
            const thumb = document.getElementById('formBannerThumbnail');
            if (preview) preview.src = e.target.result;
            if (thumb) thumb.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const titleInp = document.getElementById('bannerTitleInput');
    const subInp = document.getElementById('bannerSubtitleInput');

    if (titleInp) {
        titleInp.addEventListener('input', e => {
            const el = document.getElementById('simBannerTitle');
            if (el) el.textContent = e.target.value.trim() || 'Banner Title Headline';
        });
    }

    if (subInp) {
        subInp.addEventListener('input', e => {
            const el = document.getElementById('simBannerSubtitle');
            if (el) el.textContent = e.target.value.trim() || 'Supporting subtitle caption text';
        });
    }
});
</script>
@endsection
