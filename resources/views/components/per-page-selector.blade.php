@props([
    'current' => null,
    'options' => [10, 25, 50, 100],
    'name' => 'per_page',
])

@php
    $currentVal = (int) ($current ?? request($name, 10));
    if (!in_array($currentVal, $options)) {
        $currentVal = 10;
    }
@endphp

<div class="per-page-selector-component" title="Change rows displayed per page">
    <div class="dropdown per-page-dropdown">
        <button class="btn per-page-trigger-btn dropdown-toggle" 
                type="button" 
                id="perPageDropdown_{{ $name }}" 
                data-bs-toggle="dropdown" 
                data-bs-auto-close="true"
                aria-expanded="false"
                aria-label="Rows per page selector">
            <span class="per-page-icon-pill">
                <i class="bi bi-layers-half"></i>
            </span>
            <span class="per-page-trigger-label">
                <span class="per-page-prefix">Show</span>
                <strong class="per-page-selected-val">{{ $currentVal }}</strong>
                <span class="per-page-suffix">/ page</span>
            </span>
            <i class="bi bi-chevron-down per-page-chevron-icon"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end per-page-menu-panel shadow-lg" aria-labelledby="perPageDropdown_{{ $name }}">
            <li class="per-page-menu-header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="per-page-header-title">
                        <i class="bi bi-sliders2 text-primary me-1"></i> Page Size
                    </span>
                    <span class="per-page-cap-badge" title="Maximum displayed records capped at recent 1,000">Max 1,000</span>
                </div>
            </li>
            <li><hr class="dropdown-divider my-1 opacity-50"></li>
            @foreach($options as $size)
                @php $isActive = ($currentVal === $size); @endphp
                <li>
                    <a class="dropdown-item per-page-item-link {{ $isActive ? 'is-active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery([$name => $size, 'page' => 1]) }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="per-page-size-chip {{ $isActive ? 'active' : '' }}">
                                {{ $size }}
                            </span>
                            <div class="per-page-size-info">
                                <span class="per-page-size-name">{{ $size }} per page</span>
                                @if($size === 10)
                                    <span class="per-page-size-hint">Standard view</span>
                                @elseif($size === 25)
                                    <span class="per-page-size-hint">Compact view</span>
                                @elseif($size === 50)
                                    <span class="per-page-size-hint">Extended view</span>
                                @elseif($size === 100)
                                    <span class="per-page-size-hint">Full batch view</span>
                                @endif
                            </div>
                        </div>
                        @if($isActive)
                            <span class="per-page-check-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
