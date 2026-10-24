@extends('layouts.app')
@section('title', 'Banner CMS Management')
@section('page_title', 'App & Web Banner CMS')
@section('page_subtitle',
    'Manage interactive promotional banners, marketing visual sliders, and target device
    displays')
@section('topbar_actions')
    @can('bannercms.create')
        <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addBannerModal">
            <i class="bi bi-cloud-arrow-up-fill"></i>
            <span>Upload New Banner</span>
        </button>
    @endcan
@endsection
@section('summary_cards')
    @php
        $totalBanners = $tabCounts['all'] ?? 0;
        $activeBanners = $tabCounts['active'] ?? 0;
        $inactiveBanners = $tabCounts['inactive'] ?? 0;
    @endphp
    <div class="row g-3">
        <!-- 1. Total Banners -->
        <div class="col-6 col-lg-3">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/media-library.svg" width="48" height="48" alt="Media Library">
                        <div>
                            <div class="saas-stat-value">{{ number_format($totalBanners) }}</div>
                            <div class="saas-stat-label">Total banners</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge cyan">Media</span>
                </div>
            </div>
        </div>
        <!-- 2. Live Banners -->
        <div class="col-6 col-lg-3">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/kiosk-live.svg" width="48" height="48" alt="Live Banners">
                        <div>
                            <div class="saas-stat-value">{{ number_format($activeBanners) }}</div>
                            <div class="saas-stat-label">Active on kiosk</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge emerald">Live</span>
                </div>
            </div>
        </div>
        <!-- 3. Offline Banners -->
        <div class="col-6 col-lg-3">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/hidden-draft.svg" width="48" height="48" alt="Offline Banners">
                        <div>
                            <div class="saas-stat-value">{{ number_format($inactiveBanners) }}</div>
                            <div class="saas-stat-label">Hidden / Draft</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge amber">Offline</span>
                </div>
            </div>
        </div>
        <!-- 4. Device Targets -->
        <div class="col-6 col-lg-3">
            <div class="saas-stat-card">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/icons/devices.svg" width="48" height="48" alt="Device Targets">
                        <div>
                            <div class="saas-stat-value">3 Devices</div>
                            <div class="saas-stat-label">Mobile • Tablet • Web</div>
                        </div>
                    </div>
                    <span class="saas-stat-badge indigo">Omnichannel</span>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <!-- Filter Tabs & Controls Card -->
    <div class="saas-toolbar-card">
        <div class="saas-filter-pills">
            <a href="{{ route('banners.index', array_merge(request()->except(['tab', 'page']))) }}"
                class="saas-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <span>All banners</span>
                <span class="pill-counter">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('banners.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
                class="saas-pill-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
                <span>Active / Published</span>
                <span class="pill-counter">{{ $tabCounts['active'] ?? 0 }}</span>
            </a>
            <a href="{{ route('banners.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
                class="saas-pill-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
                <span>Draft / Inactive</span>
                <span class="pill-counter">{{ $tabCounts['inactive'] ?? 0 }}</span>
            </a>
        </div>

        <div class="saas-toolbar-controls">
            <form method="GET" action="{{ route('banners.index') }}" class="d-flex flex-wrap align-items-center gap-2"
                id="bannerSearchForm">
                @if (request('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif
                @if (request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif

                <x-search-suggest name="q" placeholder="Search headline, target..." :suggestions="$allBannerSuggestions ?? []"
                    header-title="Banner Suggestions" />

                <!-- Target device filter dropdown -->
                <div style="min-width: 150px;">
                    <x-custom-select name="target" :options="[
                        ['value' => 'all', 'label' => 'All Targets'],
                        ['value' => 'mobile', 'label' => 'Mobile Only'],
                        ['value' => 'tablet', 'label' => 'Tablet Only'],
                        ['value' => 'web', 'label' => 'Web Only'],
                    ]"
                    :value="request('target', 'all')"
                    placeholder="Device Target"
                    icon="bi-display"
                    auto-submit
                    />
                </div>

                <button type="submit" class="saas-btn-filter">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                @if(request()->hasAny(['q', 'tab', 'target']))
                    <a href="{{ route('banners.index') }}" class="saas-btn-filter text-decoration-none" title="Clear all filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="saas-table-card">
        <div class="table-responsive">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ORDER</th>
                        <th style="width: 130px;">PREVIEW</th>
                        <th>HEADLINE & SUBTITLE</th>
                        <th style="width: 140px;">TARGET DEVICE</th>
                        <th style="width: 150px;">REDIRECT LINK</th>
                        <th style="width: 110px;">STATUS</th>
                        <th style="width: 120px;" class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td class="text-center">
                                <span class="saas-code-badge">#{{ $banner->sort_order }}</span>
                            </td>
                            <td>
                                <div class="banner-thumb-container">
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                        class="banner-thumb-img"
                                        onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $banner->title }}</div>
                                @if ($banner->subtitle)
                                    <div class="text-muted small mt-1 text-truncate" style="max-width: 380px;">
                                        {{ $banner->subtitle }}</div>
                                @endif
                            </td>
                            <td>
                                @switch($banner->target)
                                    @case('mobile')
                                        <span class="badge-modern badge-sky"><i class="bi bi-phone"></i> Mobile App</span>
                                    @break
                                    @case('tablet')
                                        <span class="badge-modern badge-purple"><i class="bi bi-tablet"></i> Tablet Kiosk</span>
                                    @break
                                    @case('web')
                                        <span class="badge-modern badge-teal"><i class="bi bi-browser-chrome"></i> Web Only</span>
                                    @break
                                    @default
                                        <span class="badge-modern badge-primary"><i class="bi bi-globe2"></i> All Devices</span>
                                @endswitch
                            </td>
                            <td>
                                @if ($banner->link_url)
                                    <a href="{{ $banner->link_url }}" target="_blank"
                                        class="text-primary small text-truncate d-inline-block" style="max-width: 140px;"
                                        title="{{ $banner->link_url }}">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        {{ parse_url($banner->link_url, PHP_URL_HOST) ?? 'External Link' }}
                                    </a>
                                @else
                                    <span class="text-muted small">— None —</span>
                                @endif
                            </td>
                            <td>
                                @can('bannercms.edit')
                                    <form action="{{ route('banners.toggle', $banner) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <label class="saas-status-switch m-0" title="Click to toggle status">
                                            <input type="checkbox" class="saas-switch-input"
                                                {{ $banner->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="saas-switch-slider"></span>
                                            <span
                                                class="saas-switch-label {{ $banner->is_active ? '' : 'inactive' }}">{{ $banner->is_active ? 'Active' : 'Disabled' }}</span>
                                        </label>
                                    </form>
                                @else
                                    <span
                                        class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} border">
                                        {{ $banner->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    @can('bannercms.edit')
                                        <button type="button" class="btn-action-edit-pill" title="Edit banner"
                                            data-bs-toggle="modal" data-bs-target="#editBannerModal"
                                            data-id="{{ $banner->id }}" data-title="{{ $banner->title }}"
                                            data-subtitle="{{ $banner->subtitle }}" data-target="{{ $banner->target }}"
                                            data-link-url="{{ $banner->link_url }}"
                                            data-sort-order="{{ $banner->sort_order }}"
                                            data-is-active="{{ $banner->is_active ? '1' : '0' }}"
                                            data-image-url="{{ $banner->image_url }}" onclick="openEditBannerModal(this)">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit</span>
                                        </button>
                                    @endcan
                                    @can('bannercms.delete')
                                        <form action="{{ route('banners.destroy', $banner) }}" method="POST"
                                            class="d-inline" data-confirm="Are you sure you want to delete this banner?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete-pill" title="Delete banner">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-images text-muted display-4 d-block mb-3 opacity-50"></i>
                                        <h5 class="fw-bold text-dark">No Banners Found</h5>
                                        <p class="text-muted small">No promotional banners matched your criteria or none have
                                            been uploaded yet.</p>
                                        @can('bannercms.create')
                                            <button type="button" class="btn-action-edit-pill mt-2" data-bs-toggle="modal"
                                                data-bs-target="#addBannerModal">
                                                <i class="bi bi-cloud-arrow-up-fill"></i> Upload First Banner
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="saas-table-footer">
                <div class="saas-footer-text">
                    Showing <b>{{ $banners->firstItem() ?? 0 }}</b> to <b>{{ $banners->lastItem() ?? 0 }}</b> of
                    <b>{{ $banners->total() }}</b> banners
                </div>
                <div>
                    {{ $banners->links() }}
                </div>
            </div>
        </div>
        <style>
            .banner-thumb-container {
                width: 110px;
                height: 60px;
                border-radius: 8px;
                overflow: hidden;
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .banner-thumb-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.25s ease;
            }
            .banner-thumb-container:hover .banner-thumb-img {
                transform: scale(1.08);
            }
            .badge-status-btn {
                border: none;
                border-radius: 20px;
                padding: 3px 10px;
                font-size: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                transition: all 0.2s ease;
            }
            .badge-status-btn.active {
                background: rgba(16, 185, 129, 0.15);
                color: #059669;
            }
            .badge-status-btn.active:hover {
                background: rgba(16, 185, 129, 0.25);
            }
            .badge-status-btn.inactive {
                background: rgba(100, 116, 139, 0.15);
                color: #64748b;
            }
            .badge-status-btn.inactive:hover {
                background: rgba(100, 116, 139, 0.25);
            }
            .btn-action-icon {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: background 0.15s ease;
            }
            .btn-action-icon:hover {
                background: #f1f5f9;
            }
        </style>
        @can('bannercms.create')
        <!-- ADD BANNER MODAL (Without Scroll) -->
        <div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <form method="POST" action="{{ route('banners.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="modal-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="stat-icon-bubble cyan p-2 rounded-3">
                                    <i class="bi bi-cloud-arrow-up-fill text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-dark mb-0" id="addBannerModalLabel">Upload New Banner
                                    </h5>
                                    <p class="text-muted small mb-0">Publish an interactive promotional banner or visual slider
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-modern-label required" for="add_banner_title">Banner Headline</label>
                                    <input type="text" name="title" id="add_banner_title"
                                        class="form-control form-control-modern"
                                        placeholder="e.g. Precision Injection Moulding" required maxlength="200">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-modern-label required" for="add_banner_target">Target Device</label>
                                    <select name="target" id="add_banner_target" class="form-select form-control-modern"
                                        required>
                                        <option value="all">All Devices (Universal)</option>
                                        <option value="mobile">Mobile App Only</option>
                                        <option value="tablet">Tablet / Kiosk</option>
                                        <option value="web">Web Browser Only</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-modern-label" for="add_banner_subtitle">Subtitle / Supporting
                                        Caption</label>
                                    <input type="text" name="subtitle" id="add_banner_subtitle"
                                        class="form-control form-control-modern"
                                        placeholder="e.g. Empowering manufacturing with Japanese precision" maxlength="500">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-modern-label" for="add_banner_link">Redirect Link (URL)</label>
                                    <input type="url" name="link_url" id="add_banner_link"
                                        class="form-control form-control-modern"
                                        placeholder="https://www.shibaura-machine.co.in">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-modern-label required" for="add_banner_sort">Sort Order</label>
                                    <input type="number" name="sort_order" id="add_banner_sort"
                                        class="form-control form-control-modern" value="1" min="0"
                                        max="9999" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-modern-label required" for="add_banner_image">Banner Graphic Asset
                                        (JPG, PNG, WEBP)</label>
                                    <input type="file" name="image" id="add_banner_image"
                                        class="form-control form-control-modern" accept="image/*" required
                                        onchange="previewBannerImg(this, 'add_banner_preview')">
                                </div>
                                <div class="col-md-5 d-flex align-items-center justify-content-between">
                                    <div
                                        class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between w-100 h-100">
                                        <div>
                                            <span class="fw-semibold text-dark small d-block">Active Status</span>
                                            <span class="text-muted" style="font-size: 0.72rem;">Visible in slider</span>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                id="add_banner_is_active" checked style="width: 2.2em; height: 1.2em;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 bg-light rounded-3 border text-center"
                                        style="max-height: 80px; overflow: hidden;">
                                        <img id="add_banner_preview" src="" alt="Preview" class="img-fluid rounded"
                                            style="max-height: 65px; display: none;">
                                        <span id="add_banner_preview_placeholder" class="small text-muted"><i
                                                class="bi bi-image me-1"></i> Image preview will appear here upon
                                            selection</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-modern-primary btn-sm px-4">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        @can('bannercms.edit')
        <!-- EDIT BANNER MODAL (Without Scroll) -->
        <div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <form method="POST" id="editBannerForm" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="stat-icon-bubble emerald p-2 rounded-3">
                                    <i class="bi bi-pencil-square text-emerald-600 fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-dark mb-0" id="editBannerModalLabel">Edit Promotional
                                        Banner</h5>
                                    <p class="text-muted small mb-0">Update headline, destination link, or replace graphic
                                        image</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-modern-label required" for="edit_banner_title">Banner Headline</label>
                                    <input type="text" name="title" id="edit_banner_title"
                                        class="form-control form-control-modern" required maxlength="200">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-modern-label required" for="edit_banner_target">Target Device</label>
                                    <select name="target" id="edit_banner_target" class="form-select form-control-modern"
                                        required>
                                        <option value="all">All Devices (Universal)</option>
                                        <option value="mobile">Mobile App Only</option>
                                        <option value="tablet">Tablet / Kiosk</option>
                                        <option value="web">Web Browser Only</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-modern-label" for="edit_banner_subtitle">Subtitle / Supporting
                                        Caption</label>
                                    <input type="text" name="subtitle" id="edit_banner_subtitle"
                                        class="form-control form-control-modern" maxlength="500">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-modern-label" for="edit_banner_link">Redirect Link (URL)</label>
                                    <input type="url" name="link_url" id="edit_banner_link"
                                        class="form-control form-control-modern">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-modern-label required" for="edit_banner_sort">Sort Order</label>
                                    <input type="number" name="sort_order" id="edit_banner_sort"
                                        class="form-control form-control-modern" min="0" max="9999" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-modern-label" for="edit_banner_image">Replace Graphic Asset <small
                                            class="text-muted fw-normal">(Optional)</small></label>
                                    <input type="file" name="image" id="edit_banner_image"
                                        class="form-control form-control-modern" accept="image/*"
                                        onchange="previewBannerImg(this, 'edit_banner_preview')">
                                </div>
                                <div class="col-md-5 d-flex align-items-center justify-content-between">
                                    <div
                                        class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between w-100 h-100">
                                        <div>
                                            <span class="fw-semibold text-dark small d-block">Active Status</span>
                                            <span class="text-muted" style="font-size: 0.72rem;">Visible in slider</span>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                id="edit_banner_is_active" style="width: 2.2em; height: 1.2em;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 bg-light rounded-3 border text-center"
                                        style="max-height: 80px; overflow: hidden;">
                                        <img id="edit_banner_preview" src="" alt="Current Banner"
                                            class="img-fluid rounded" style="max-height: 65px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-modern-primary btn-sm px-4">
                                <i class="bi bi-check2-circle me-1"></i> Update Banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    @endsection
    @push('scripts')
        <script>
            function previewBannerImg(input, targetImgId) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById(targetImgId);
                        if (img) {
                            img.src = e.target.result;
                            img.style.display = 'inline-block';
                        }
                        const placeholder = document.getElementById(targetImgId + '_placeholder');
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
            function openEditBannerModal(btn) {
                const id = btn.getAttribute('data-id');
                const form = document.getElementById('editBannerForm');
                form.action = `/banners/${id}`;
                document.getElementById('edit_banner_title').value = btn.getAttribute('data-title') || '';
                document.getElementById('edit_banner_subtitle').value = btn.getAttribute('data-subtitle') || '';
                document.getElementById('edit_banner_target').value = btn.getAttribute('data-target') || 'all';
                document.getElementById('edit_banner_link').value = btn.getAttribute('data-link-url') || '';
                document.getElementById('edit_banner_sort').value = btn.getAttribute('data-sort-order') || '1';
                document.getElementById('edit_banner_is_active').checked = btn.getAttribute('data-is-active') === '1';
                const previewImg = document.getElementById('edit_banner_preview');
                const imgUrl = btn.getAttribute('data-image-url');
                if (previewImg && imgUrl) {
                    previewImg.src = imgUrl;
                }
                document.getElementById('edit_banner_image').value = '';
            }
        </script>
    @endpush
