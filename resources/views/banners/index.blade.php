@extends('layouts.app')
@section('title', 'Banner CMS Management')
@section('page_title', 'App & Web Banner CMS')
@section('page_subtitle', 'Manage interactive promotional banners, marketing visual sliders, and target device displays')

@section('topbar_actions')
    <a href="{{ route('banners.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-cloud-arrow-up-fill"></i>
        <span>Upload New Banner</span>
    </a>
@endsection

@section('content')
<!-- CMS Unified Navigation Tabs -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div class="cms-hub-nav">
        <a href="{{ route('company.edit') }}" class="cms-hub-tab">
            <i class="bi bi-building-gear"></i>
            <span>Company Identity</span>
        </a>
        <a href="{{ route('banners.index') }}" class="cms-hub-tab active">
            <i class="bi bi-images"></i>
            <span>Promotional Banners</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="/api/cms/banners" target="_blank" class="badge-modern badge-slate text-decoration-none py-2 px-3" title="Inspect Public REST API Endpoint">
            <i class="bi bi-code-slash text-primary"></i> <span>REST API: /api/cms/banners</span>
        </a>
    </div>
</div>

<!-- Filter Tabs & Controls Card -->
<div class="filter-card-wrapper mb-4">
    <!-- Filter Tabs Header -->
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <a href="{{ route('banners.index', array_merge(request()->except(['tab', 'page']))) }}"
               class="filter-tab-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="bi bi-images"></i>
                <span>All Banners</span>
                <span class="filter-tab-badge">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('banners.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Active / Published</span>
                <span class="filter-tab-badge">{{ $tabCounts['active'] ?? 0 }}</span>
            </a>
            <a href="{{ route('banners.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
               class="filter-tab-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
                <i class="bi bi-pause-circle-fill text-muted"></i>
                <span>Draft / Inactive</span>
                <span class="filter-tab-badge">{{ $tabCounts['inactive'] ?? 0 }}</span>
            </a>
        </div>

        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">
                <i class="bi bi-phone"></i> Target Devices: Mobile / Tablet / Web
            </span>
        </div>
    </div>

    <!-- Filter Controls Body -->
    <div class="filter-controls-body">
        <form method="GET" action="{{ route('banners.index') }}" class="d-flex flex-wrap align-items-center gap-2" id="bannerSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <x-search-suggest 
                name="q" 
                placeholder="Search banner headline, target, subtitle..." 
                :suggestions="$allBannerSuggestions ?? []"
                header-title="Banner Directory Suggestions"
            />

            <!-- Target device filter dropdown -->
            <div style="min-width: 170px;">
                <x-custom-select
                    name="target"
                    :options="[
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

            <button type="submit" class="btn-modern-primary btn-sm py-1 px-3">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>

            @if(request()->hasAny(['q', 'tab', 'target']))
                <a href="{{ route('banners.index') }}" class="btn-modern-secondary btn-sm py-1 px-3" title="Clear all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Banners Data Table Card -->
<div class="card-modern">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="width: 70px;">Order</th>
                    <th style="width: 140px;">Preview</th>
                    <th>Headline & Subtitle</th>
                    <th style="width: 140px;">Target Device</th>
                    <th style="width: 150px;">Redirect Link</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                    <tr>
                        <td class="text-center">
                            <span class="badge-modern badge-slate font-monospace fw-bold">#{{ $banner->sort_order }}</span>
                        </td>
                        <td>
                            <div class="banner-thumb-container">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-thumb-img" onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $banner->title }}</div>
                            @if($banner->subtitle)
                                <div class="text-muted small mt-1 text-truncate" style="max-width: 380px;">{{ $banner->subtitle }}</div>
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
                            @if($banner->link_url)
                                <a href="{{ $banner->link_url }}" target="_blank" class="text-primary small text-truncate d-inline-block" style="max-width: 140px;" title="{{ $banner->link_url }}">
                                    <i class="bi bi-box-arrow-up-right"></i> {{ parse_url($banner->link_url, PHP_URL_HOST) ?? 'External Link' }}
                                </a>
                            @else
                                <span class="text-muted small">— None —</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('banners.toggle', $banner) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge-status-btn {{ $banner->is_active ? 'active' : 'inactive' }}" title="Click to toggle status">
                                    <i class="bi {{ $banner->is_active ? 'bi-check-circle-fill' : 'bi-dash-circle-fill' }}"></i>
                                    <span>{{ $banner->is_active ? 'Active' : 'Disabled' }}</span>
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('banners.edit', $banner) }}" class="btn-action-icon text-primary" title="Edit banner">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-icon text-danger border-0 bg-transparent" title="Delete banner">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-images text-muted display-4 d-block mb-3 opacity-50"></i>
                                <h5 class="fw-bold text-dark">No Banners Found</h5>
                                <p class="text-muted small">No promotional banners matched your criteria or none have been uploaded yet.</p>
                                <a href="{{ route('banners.create') }}" class="btn-modern-primary btn-sm mt-2">
                                    <i class="bi bi-cloud-arrow-up-fill"></i> Upload First Banner
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($banners->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $banners->links() }}
        </div>
    @endif
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
@endsection
