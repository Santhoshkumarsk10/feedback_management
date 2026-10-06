@props([
    'name',
    'value' => null,
    'options' => [],
    'placeholder' => 'Select an option',
    'searchPlaceholder' => 'Search options...',
    'autoSubmit' => true,
    'icon' => null,
    'id' => null,
    'minWidth' => '170px',
])

@php
    $inputId = $id ?? ('custom_select_' . $name . '_' . \Illuminate\Support\Str::random(6));
    $currentVal = (string) ($value ?? request($name, ''));

    // Normalize options
    $parsedOptions = collect($options)->map(function ($opt, $key) {
        if (is_scalar($opt) && is_string($key) && !is_numeric($key)) {
            return [
                'value' => (string) $key,
                'label' => (string) $opt,
                'code' => null,
                'sub' => null,
                'dot' => null,
            ];
        }

        if (is_scalar($opt)) {
            return [
                'value' => (string) $opt,
                'label' => (string) $opt,
                'code' => null,
                'sub' => null,
                'dot' => null,
            ];
        }

        $val = is_array($opt) ? ($opt['value'] ?? $opt['id'] ?? '') : ($opt->value ?? $opt->id ?? '');
        $label = is_array($opt) ? ($opt['label'] ?? $opt['name'] ?? $opt['title'] ?? '') : ($opt->label ?? $opt->name ?? $opt->title ?? '');
        $code = is_array($opt) ? ($opt['code'] ?? null) : ($opt->code ?? null);
        $sub = is_array($opt) ? ($opt['sub'] ?? $opt['subtitle'] ?? null) : ($opt->sub ?? $opt->subtitle ?? null);
        $dot = is_array($opt) ? ($opt['dot'] ?? null) : ($opt->dot ?? null);

        return [
            'value' => (string) $val,
            'label' => (string) $label,
            'code' => $code,
            'sub' => $sub,
            'dot' => $dot,
        ];
    });

    // Find currently selected option
    $selectedOption = $parsedOptions->firstWhere('value', $currentVal);
@endphp

<div class="custom-select-wrapper" 
     id="{{ $inputId }}_wrapper" 
     data-custom-select-container
     data-auto-submit="{{ $autoSubmit ? 'true' : 'false' }}"
     style="min-width: {{ $minWidth }};">

    <!-- Hidden Form Input -->
    <input type="hidden" 
           name="{{ $name }}" 
           id="{{ $inputId }}" 
           value="{{ $currentVal }}" 
           class="custom-select-hidden-input">

    <!-- Trigger Button -->
    <div class="custom-select-trigger" 
         tabindex="0" 
         role="combobox" 
         aria-expanded="false" 
         aria-haspopup="listbox"
         title="{{ $selectedOption ? $selectedOption['label'] : $placeholder }}">
        
        <div class="custom-select-trigger-content">
            @if($selectedOption && !empty($selectedOption['dot']))
                <span class="status-dot {{ $selectedOption['dot'] }}"></span>
            @elseif($selectedOption && !empty($selectedOption['code']))
                <span class="badge-modern badge-shibaura py-0 px-1" style="font-size: 0.72rem;">{{ $selectedOption['code'] }}</span>
            @elseif($icon)
                <i class="bi {{ $icon }} custom-select-trigger-icon"></i>
            @endif

            <span class="custom-select-trigger-text">
                @if($selectedOption)
                    @if(!empty($selectedOption['code']))
                        {{ $selectedOption['code'] }} ({{ Str::limit($selectedOption['label'], 16) }})
                    @else
                        {{ $selectedOption['label'] }}
                    @endif
                @else
                    <span class="text-muted">{{ $placeholder }}</span>
                @endif
            </span>
        </div>

        <i class="bi bi-chevron-down custom-select-chevron"></i>
    </div>

    <!-- Dropdown Overlay Menu -->
    <div class="custom-select-dropdown" style="display: none;">
        <!-- Search Input Header -->
        <div class="custom-select-search-box">
            <i class="bi bi-search"></i>
            <input type="text" 
                   class="custom-select-search-input" 
                   placeholder="{{ $searchPlaceholder }}" 
                   autocomplete="off"
                   aria-label="{{ $searchPlaceholder }}">
            <button type="button" class="custom-select-search-clear d-none" title="Clear search">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>

        <!-- Options List -->
        <ul class="custom-select-options-list" role="listbox">
            <!-- Reset / All Option -->
            <li class="custom-select-option {{ $currentVal === '' ? 'is-selected' : '' }}" 
                role="option" 
                data-value="" 
                data-label="{{ $placeholder }}"
                data-search="{{ strtolower($placeholder) }}">
                <div class="custom-select-option-main">
                    @if($icon)
                        <i class="bi {{ $icon }} text-muted me-1"></i>
                    @else
                        <i class="bi bi-grid-fill text-muted me-1"></i>
                    @endif
                    <span class="custom-select-option-text fw-medium">{{ $placeholder }}</span>
                </div>
                <i class="bi bi-check-lg custom-select-option-check"></i>
            </li>

            <!-- Options -->
            @foreach($parsedOptions as $opt)
                @php
                    $isSelected = ($currentVal !== '' && $currentVal === $opt['value']);
                    $searchTerms = strtolower($opt['label'] . ' ' . ($opt['code'] ?? '') . ' ' . ($opt['sub'] ?? ''));
                @endphp
                <li class="custom-select-option {{ $isSelected ? 'is-selected' : '' }}" 
                    role="option" 
                    data-value="{{ $opt['value'] }}" 
                    data-label="{{ $opt['label'] }}"
                    data-code="{{ $opt['code'] ?? '' }}"
                    data-dot="{{ $opt['dot'] ?? '' }}"
                    data-search="{{ $searchTerms }}">
                    
                    <div class="custom-select-option-main">
                        @if(!empty($opt['code']))
                            <span class="badge-modern badge-shibaura me-1 fw-bold" style="font-size: 0.72rem;">
                                <i class="bi bi-qr-code"></i> <span class="opt-highlight-target">{{ $opt['code'] }}</span>
                            </span>
                        @elseif(!empty($opt['dot']))
                            <span class="status-dot {{ $opt['dot'] }} me-1"></span>
                        @endif

                        <span class="custom-select-option-text opt-highlight-target">{{ $opt['label'] }}</span>

                        @if(!empty($opt['sub']))
                            <span class="small text-muted ms-1 opt-highlight-target">({{ $opt['sub'] }})</span>
                        @endif
                    </div>

                    <i class="bi bi-check-lg custom-select-option-check"></i>
                </li>
            @endforeach
        </ul>

        <!-- Empty search result message -->
        <div class="custom-select-empty d-none">
            <i class="bi bi-search text-muted d-block mb-1 fs-5"></i>
            <span>No matching options found</span>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    function initCustomSelect(container) {
        if (container._customSelectInitialized) return;
        container._customSelectInitialized = true;

        const trigger = container.querySelector('.custom-select-trigger');
        const triggerText = container.querySelector('.custom-select-trigger-text');
        const triggerContent = container.querySelector('.custom-select-trigger-content');
        const hiddenInput = container.querySelector('.custom-select-hidden-input');
        const dropdown = container.querySelector('.custom-select-dropdown');
        const searchInput = container.querySelector('.custom-select-search-input');
        const searchClear = container.querySelector('.custom-select-search-clear');
        const optionsList = container.querySelector('.custom-select-options-list');
        const emptyNotice = container.querySelector('.custom-select-empty');
        const form = container.closest('form');
        const shouldAutoSubmit = container.getAttribute('data-auto-submit') === 'true';

        if (!trigger || !dropdown) return;

        const options = Array.from(optionsList.querySelectorAll('.custom-select-option'));

        // Cache original text for highlighting
        options.forEach(opt => {
            const targets = opt.querySelectorAll('.opt-highlight-target');
            opt._origTexts = Array.from(targets).map(el => el.textContent.trim());
        });

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightMatch(text, query) {
            if (!query) return text;
            const regex = new RegExp('(' + escapeRegExp(query) + ')', 'gi');
            return text.replace(regex, '<mark class="suggestion-highlight">$1</mark>');
        }

        function openDropdown() {
            // Close other open custom selects first
            document.querySelectorAll('[data-custom-select-container].is-open').forEach(other => {
                if (other !== container && other._closeCustomSelect) {
                    other._closeCustomSelect();
                }
            });

            container.classList.add('is-open');
            dropdown.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');

            // Reset search query & filter
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                if (searchClear) searchClear.classList.add('d-none');

                // CURSOR AUTO FOCUS: Immediate focus into search box on open!
                requestAnimationFrame(() => {
                    searchInput.focus();
                });
                setTimeout(() => {
                    searchInput.focus();
                }, 15);
            }
        }

        function closeDropdown() {
            container.classList.remove('is-open');
            dropdown.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
            clearFocusedOption();
        }

        container._closeCustomSelect = closeDropdown;

        function clearFocusedOption() {
            options.forEach(el => el.classList.remove('is-focused'));
        }

        function getVisibleOptions() {
            return options.filter(el => !el.classList.contains('d-none'));
        }

        function selectOption(opt) {
            const val = opt.getAttribute('data-value') || '';
            const label = opt.getAttribute('data-label') || '';
            const code = opt.getAttribute('data-code') || '';
            const dot = opt.getAttribute('data-dot') || '';

            // Update hidden input
            hiddenInput.value = val;

            // Update visual active state
            options.forEach(el => el.classList.remove('is-selected'));
            opt.classList.add('is-selected');

            // Update trigger display
            let contentHtml = '';
            if (dot) {
                contentHtml += `<span class="status-dot ${dot}"></span> `;
            } else if (code) {
                contentHtml += `<span class="badge-modern badge-shibaura py-0 px-1" style="font-size: 0.72rem;">${code}</span> `;
            }
            if (val === '') {
                contentHtml += `<span class="text-muted">${label}</span>`;
            } else {
                contentHtml += code ? `${code} (${label.substring(0, 16)})` : label;
            }
            triggerText.innerHTML = contentHtml;

            closeDropdown();

            // Auto-submit form if enabled
            if (shouldAutoSubmit && form) {
                form.submit();
            }
        }

        function filterOptions(query) {
            query = query.trim().toLowerCase();
            let visibleCount = 0;

            options.forEach(opt => {
                const searchData = (opt.getAttribute('data-search') || '').toLowerCase();
                const isMatch = !query || searchData.includes(query);

                if (isMatch) {
                    opt.classList.remove('d-none');
                    visibleCount++;

                    // Highlight matching text
                    const targets = opt.querySelectorAll('.opt-highlight-target');
                    targets.forEach((el, idx) => {
                        const orig = opt._origTexts ? opt._origTexts[idx] : el.textContent;
                        el.innerHTML = highlightMatch(orig, query);
                    });
                } else {
                    opt.classList.add('d-none');
                }
            });

            if (emptyNotice) {
                if (visibleCount === 0 && query.length > 0) {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            }

            clearFocusedOption();
        }

        // Toggle dropdown on trigger click
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (container.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        // Trigger keyboard open
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                if (!container.classList.contains('is-open')) {
                    openDropdown();
                }
            }
        });

        // Search input events
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = searchInput.value;
                if (searchClear) {
                    if (q.length > 0) searchClear.classList.remove('d-none');
                    else searchClear.classList.add('d-none');
                }
                filterOptions(q);
            });

            searchInput.addEventListener('keydown', function(e) {
                const visible = getVisibleOptions();
                const focused = visible.find(el => el.classList.contains('is-focused'));
                let idx = focused ? visible.indexOf(focused) : -1;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    clearFocusedOption();
                    let nextIdx = (idx + 1) % visible.length;
                    if (visible[nextIdx]) {
                        visible[nextIdx].classList.add('is-focused');
                        visible[nextIdx].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    clearFocusedOption();
                    let prevIdx = (idx - 1 + visible.length) % visible.length;
                    if (visible[prevIdx]) {
                        visible[prevIdx].classList.add('is-focused');
                        visible[prevIdx].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (focused) {
                        selectOption(focused);
                    } else if (visible.length > 0) {
                        selectOption(visible[0]);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                    trigger.focus();
                }
            });
        }

        // Search clear button
        if (searchClear) {
            searchClear.addEventListener('click', function(e) {
                e.stopPropagation();
                searchInput.value = '';
                searchClear.classList.add('d-none');
                filterOptions('');
                searchInput.focus();
            });
        }

        // Option click
        optionsList.addEventListener('click', function(e) {
            const opt = e.target.closest('.custom-select-option');
            if (opt) {
                selectOption(opt);
            }
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    function initAllCustomSelects() {
        document.querySelectorAll('[data-custom-select-container]').forEach(initCustomSelect);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllCustomSelects);
    } else {
        initAllCustomSelects();
    }
})();
</script>
@endpush
@endonce
