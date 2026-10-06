@props([
    'name' => 'q',
    'value' => null,
    'placeholder' => 'Search...',
    'id' => null,
    'suggestions' => [],
    'quickChips' => [],
    'headerTitle' => 'Quick Suggestions',
])

@php
    $inputId = $id ?? ('search_suggest_' . $name . '_' . \Illuminate\Support\Str::random(6));
    $currentVal = $value ?? request($name, '');

    // Normalize suggestions collection/array into structured items
    $suggestionItems = collect($suggestions)->map(function ($sug) {
        if (is_string($sug)) {
            return [
                'code' => null,
                'name' => $sug,
                'sub' => null,
                'value' => $sug,
            ];
        }

        $code = is_array($sug) ? ($sug['code'] ?? null) : ($sug->code ?? null);
        $name = is_array($sug)
            ? ($sug['name'] ?? $sug['title'] ?? '')
            : ($sug->name ?? $sug->title ?? '');
        $sub = is_array($sug)
            ? ($sug['subtitle'] ?? $sug['location'] ?? $sug['email'] ?? $sug['desc'] ?? null)
            : ($sug->subtitle ?? $sug->location ?? $sug->email ?? $sug->description ?? null);
        $val = is_array($sug)
            ? ($sug['value'] ?? $sug['search_value'] ?? $code ?? $name)
            : ($sug->search_value ?? $code ?? $name);

        return [
            'code' => $code,
            'name' => $name,
            'sub' => $sub,
            'value' => $val,
        ];
    });

    // Determine quick chips (either passed explicitly or auto-extracted)
    $chips = collect($quickChips);
    if ($chips->isEmpty() && $suggestionItems->isNotEmpty()) {
        $codes = $suggestionItems->pluck('code')->filter()->unique()->take(4);
        $subs = $suggestionItems->pluck('sub')->filter()->map(function ($s) {
            $parts = explode(',', $s);
            return trim($parts[0] ?? $s);
        })->unique()->take(3);

        $chips = $codes->concat($subs)->unique();
    }
@endphp

<div class="filter-input-search search-suggest-wrapper position-relative" data-suggest-container>
    <i class="bi bi-search"></i>
    <input type="text"
           name="{{ $name }}"
           id="{{ $inputId }}"
           value="{{ $currentVal }}"
           maxlength="60"
           class="form-control form-control-modern form-control-sm search-suggest-input @error($name) is-invalid @enderror"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           aria-autocomplete="list"
           aria-expanded="false"
           {{ $attributes->except(['name', 'value', 'placeholder', 'id', 'suggestions', 'quick-chips', 'quickChips', 'header-title', 'headerTitle']) }}>

    <button type="button" class="search-clear-btn {{ $currentVal ? '' : 'd-none' }}" title="Clear search" aria-label="Clear search">
        <i class="bi bi-x-circle-fill"></i>
    </button>

    <div class="search-validation-toast d-none" style="position: absolute; top: calc(100% + 4px); left: 0; z-index: 1060; background: #dc3545; color: #fff; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); pointer-events: none;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <span>Special character not allowed</span>
    </div>

    @if($suggestionItems->isNotEmpty() || $chips->isNotEmpty())
        <!-- Autocomplete Suggestions Dropdown -->
        <div class="search-suggestions-dropdown" style="display: none;">
            <div class="suggestion-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-stars text-primary"></i>
                    <span>{{ $headerTitle }}</span>
                </div>
                <span class="suggestion-keyboard-hint">Press <kbd>↵ Enter</kbd> to filter</span>
            </div>

            @if($chips->isNotEmpty())
                <div class="suggestion-chips-bar">
                    <span class="suggestion-chips-label"><i class="bi bi-funnel-fill text-primary"></i> Quick:</span>
                    @foreach($chips as $chip)
                        @php $chipVal = is_array($chip) ? ($chip['value'] ?? $chip['label'] ?? '') : $chip; @endphp
                        <button type="button" class="suggestion-chip" data-search="{{ $chipVal }}" title="Filter by {{ $chipVal }}">
                            {{ $chipVal }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if($suggestionItems->isNotEmpty())
                <ul class="suggestion-list" role="listbox">
                    @foreach($suggestionItems as $item)
                        <li class="suggestion-item"
                            role="option"
                            data-code="{{ $item['code'] ?? '' }}"
                            data-name="{{ $item['name'] }}"
                            data-sub="{{ $item['sub'] ?? '' }}"
                            data-value="{{ $item['value'] }}"
                            title="Filter by {{ $item['value'] }}">
                            @if(!empty($item['code']))
                                <div class="suggestion-item-left">
                                    <span class="badge-modern badge-shibaura fw-bold">
                                        <i class="bi bi-qr-code"></i> <span class="highlight-code">{{ $item['code'] }}</span>
                                    </span>
                                </div>
                            @endif
                            <div class="suggestion-item-body">
                                <div class="suggestion-item-title highlight-name">{{ $item['name'] }}</div>
                                @if(!empty($item['sub']))
                                    <div class="suggestion-item-sub">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <span class="highlight-sub">{{ $item['sub'] }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="suggestion-item-action">
                                <span class="suggestion-badge-action">Filter <i class="bi bi-arrow-return-left"></i></span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="suggestion-no-results d-none">
                <i class="bi bi-search"></i>
                <div>No matching suggestions found for "<strong class="no-results-query"></strong>"</div>
                <div class="text-muted small mt-1">Press <kbd class="bg-light text-dark border">Enter</kbd> to search across entire directory</div>
            </div>

            <div class="suggestion-footer">
                <span><kbd>↑</kbd> <kbd>↓</kbd> navigate</span>
                <span><kbd>↵ Enter</kbd> to filter list</span>
                <span><kbd>Esc</kbd> close</span>
            </div>
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
(function() {
    function initSearchSuggest(container) {
        if (container._suggestInitialized) return;
        container._suggestInitialized = true;

        const input = container.querySelector('.search-suggest-input');
        const dropdown = container.querySelector('.search-suggestions-dropdown');
        const clearBtn = container.querySelector('.search-clear-btn');
        const suggestionList = container.querySelector('.suggestion-list');
        const chipsBar = container.querySelector('.suggestion-chips-bar');
        const noResults = container.querySelector('.suggestion-no-results');
        const noResultsQuery = container.querySelector('.no-results-query');

        if (!input) return;

        const form = input.closest('form');
        const items = suggestionList ? Array.from(suggestionList.querySelectorAll('.suggestion-item')) : [];
        const chips = chipsBar ? Array.from(chipsBar.querySelectorAll('.suggestion-chip')) : [];

        // Cache original text for highlighting
        items.forEach(item => {
            const codeEl = item.querySelector('.highlight-code');
            const nameEl = item.querySelector('.highlight-name');
            const subEl = item.querySelector('.highlight-sub');
            if (codeEl) item._origCode = codeEl.textContent.trim();
            if (nameEl) item._origName = nameEl.textContent.trim();
            if (subEl) item._origSub = subEl.textContent.trim();
        });

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function highlightText(origText, query) {
            if (!origText) return '';
            if (!query) return escapeHtml(origText);
            const regex = new RegExp('(' + escapeRegExp(query) + ')', 'gi');
            return escapeHtml(origText).replace(regex, '<mark class="suggestion-highlight">$1</mark>');
        }

        function openDropdown() {
            if (!dropdown) return;
            dropdown.style.display = 'block';
            input.setAttribute('aria-expanded', 'true');
            filterSuggestions();
        }

        function closeDropdown() {
            if (!dropdown) return;
            dropdown.style.display = 'none';
            input.setAttribute('aria-expanded', 'false');
            clearActiveItem();
        }

        function clearActiveItem() {
            items.forEach(el => el.classList.remove('active'));
        }

        function getVisibleItems() {
            return items.filter(el => !el.classList.contains('d-none'));
        }

        function hasActiveFilterValues() {
            if (!form) return false;
            let hasValues = false;
            const elements = form.querySelectorAll('input:not([type="hidden"]), select');
            elements.forEach(el => {
                if (el !== input && el.value && el.value !== 'all' && el.value.trim() !== '') {
                    hasValues = true;
                }
            });
            return hasValues;
        }

        function hasActiveUrlFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            for (const [key, val] of urlParams.entries()) {
                if (key !== 'page' && key !== 'tab' && key !== 'tier' && val && val.trim() !== '') {
                    return true;
                }
            }
            return false;
        }

        function submitSearch(val) {
            if (val !== undefined && val !== null) {
                input.value = val;
            }
            closeDropdown();
            if (form) {
                const searchVal = input.value.trim();
                const activeUrl = hasActiveUrlFilters();
                const hasFilters = hasActiveFilterValues();

                if (!searchVal && !activeUrl && !hasFilters) {
                    showSearchToast('Please enter search keywords or select a filter.');
                    input.focus();
                    return;
                }

                if (!searchVal) {
                    input.disabled = true;
                    form.submit();
                    setTimeout(() => { input.disabled = false; }, 100);
                } else {
                    form.submit();
                }
            }
        }

        if (form && !form._suggestFilterBound) {
            form._suggestFilterBound = true;
            form.addEventListener('submit', function(e) {
                const searchVal = input.value.trim();
                const activeUrl = hasActiveUrlFilters();
                const hasFilters = hasActiveFilterValues();

                if (!searchVal && !activeUrl && !hasFilters) {
                    e.preventDefault();
                    showSearchToast('Please enter search keywords or select a filter.');
                    input.focus();
                    return false;
                }

                if (!searchVal) {
                    input.disabled = true;
                    setTimeout(() => { input.disabled = false; }, 100);
                }
            });
        }

        function filterSuggestions() {
            if (!dropdown) return;
            const query = input.value.trim().toLowerCase();

            // Toggle clear button
            if (clearBtn) {
                if (input.value.trim().length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }
            }

            let visibleCount = 0;

            items.forEach(item => {
                const code = (item.getAttribute('data-code') || '').toLowerCase();
                const name = (item.getAttribute('data-name') || '').toLowerCase();
                const sub = (item.getAttribute('data-sub') || '').toLowerCase();

                const isMatch = !query || code.includes(query) || name.includes(query) || sub.includes(query);

                if (isMatch) {
                    item.classList.remove('d-none');
                    visibleCount++;

                    const codeEl = item.querySelector('.highlight-code');
                    const nameEl = item.querySelector('.highlight-name');
                    const subEl = item.querySelector('.highlight-sub');

                    if (codeEl && item._origCode) codeEl.innerHTML = highlightText(item._origCode, query);
                    if (nameEl && item._origName) nameEl.innerHTML = highlightText(item._origName, query);
                    if (subEl && item._origSub) subEl.innerHTML = highlightText(item._origSub, query);
                } else {
                    item.classList.add('d-none');
                }
            });

            // Filter chips
            chips.forEach(chip => {
                const val = (chip.getAttribute('data-search') || '').toLowerCase();
                if (!query || val.includes(query)) {
                    chip.classList.remove('d-none');
                } else {
                    chip.classList.add('d-none');
                }
            });

            // Empty state
            if (noResults) {
                if (visibleCount === 0 && query.length > 0) {
                    noResults.classList.remove('d-none');
                    if (noResultsQuery) noResultsQuery.textContent = input.value.trim();
                } else {
                    noResults.classList.add('d-none');
                }
            }

            clearActiveItem();
        }

        const searchToast = container.querySelector('.search-validation-toast');
        let toastTimeout = null;

        function showSearchToast(msg) {
            if (!searchToast) return;
            const span = searchToast.querySelector('span');
            if (span) span.textContent = msg;
            searchToast.classList.remove('d-none');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                searchToast.classList.add('d-none');
            }, 2500);
        }

        // Allowed search characters: Unicode letters, numbers, whitespace, - _ . , / @ & ( )
        const allowedSearchChar = /^[\p{L}\p{N}\s\-_.,\/@&()]$/u;
        const disallowedSearchRegex = /[^\p{L}\p{N}\s\-_.,\/@&()]/gu;

        // 1. Click / focus -> show suggestions immediately!
        input.addEventListener('click', openDropdown);
        input.addEventListener('focus', openDropdown);

        // 2a. Block typing disallowed characters on keystroke
        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (!allowedSearchChar.test(e.key)) {
                    e.preventDefault();
                    showSearchToast('Special characters like < > { } [ ] ; $ % * = are not allowed.');
                    return;
                }
            }

            const visibleItems = getVisibleItems();
            const activeItem = visibleItems.find(el => el.classList.contains('active'));
            let currentIndex = activeItem ? visibleItems.indexOf(activeItem) : -1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (dropdown && dropdown.style.display === 'none') {
                    openDropdown();
                    return;
                }
                if (visibleItems.length > 0) {
                    clearActiveItem();
                    let nextIndex = (currentIndex + 1) % visibleItems.length;
                    visibleItems[nextIndex].classList.add('active');
                    visibleItems[nextIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (dropdown && dropdown.style.display === 'none') {
                    openDropdown();
                    return;
                }
                if (visibleItems.length > 0) {
                    clearActiveItem();
                    let prevIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length;
                    visibleItems[prevIndex].classList.add('active');
                    visibleItems[prevIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (dropdown && dropdown.style.display !== 'none' && activeItem) {
                    const selectedVal = activeItem.getAttribute('data-value') || activeItem.getAttribute('data-code') || activeItem.getAttribute('data-name');
                    submitSearch(selectedVal);
                } else {
                    submitSearch(input.value.trim());
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // 2b. Block disallowed characters on virtual keyboards / mobile IME
        input.addEventListener('beforeinput', function(e) {
            if (e.data) {
                for (let i = 0; i < e.data.length; i++) {
                    if (!allowedSearchChar.test(e.data[i])) {
                        e.preventDefault();
                        showSearchToast('Special characters like < > { } [ ] ; $ % * = are not allowed.');
                        return;
                    }
                }
            }
        });

        // 2c. Clean paste
        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && disallowedSearchRegex.test(text)) {
                e.preventDefault();
                const cleaned = text.replace(disallowedSearchRegex, '');
                document.execCommand('insertText', false, cleaned);
                showSearchToast('Disallowed special characters removed.');
                filterSuggestions();
            }
        });

        // 2d. Typing -> fallback filter and update suggestions
        input.addEventListener('input', function() {
            if (disallowedSearchRegex.test(this.value)) {
                this.value = this.value.replace(disallowedSearchRegex, '');
                showSearchToast('Special characters like < > { } [ ] ; $ % * = are not allowed.');
            }
            if (dropdown && dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
                input.setAttribute('aria-expanded', 'true');
            }
            filterSuggestions();
        });

        // 4. Click suggestion item
        if (suggestionList) {
            suggestionList.addEventListener('click', function(e) {
                const item = e.target.closest('.suggestion-item');
                if (item) {
                    const val = item.getAttribute('data-value') || item.getAttribute('data-code') || item.getAttribute('data-name');
                    submitSearch(val);
                }
            });
        }

        // 5. Click chip
        if (chipsBar) {
            chipsBar.addEventListener('click', function(e) {
                const chip = e.target.closest('.suggestion-chip');
                if (chip) {
                    const val = chip.getAttribute('data-search');
                    submitSearch(val);
                }
            });
        }

        // 6. Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                input.value = '';
                clearBtn.classList.add('d-none');
                input.focus();
                openDropdown();
            });
        }

        // 7. Click outside closes dropdown
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    function initAll() {
        document.querySelectorAll('[data-suggest-container]').forEach(initSearchSuggest);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
@endpush
@endonce
