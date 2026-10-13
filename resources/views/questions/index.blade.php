@extends('layouts.app')
@section('title', 'Feedback Questionnaire')
@section('page_title', 'Shibaura Technical Centre Questionnaire')
@section('page_subtitle', 'Section-wise configuration of customer evaluation criteria and survey questions')

@section('topbar_actions')
    <a href="{{ route('questions.create') }}" class="btn-modern-primary btn-sm">
        <i class="bi bi-plus-circle-fill"></i>
        <span>Add Question</span>
    </a>
@endsection

@section('content')
@php
    $grouped = $questions->groupBy(fn($q) => $q->section ?: 'General Questionnaire');
    $totalSectionsCount = $grouped->count();
    $totalQuestionsCount = $questions->count();
@endphp

<!-- Section Filter Toolbar -->
<div class="filter-card-wrapper mb-4 p-3 shadow-xs">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Left: Compact Section Filter Dropdown & Counters -->
        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
            <div class="position-relative" id="sectionFilterDropdownWrap">
                <!-- Dropdown Trigger Button -->
                <button type="button" 
                        class="btn-modern-secondary d-inline-flex align-items-center gap-2" 
                        id="sectionFilterDropdownBtn"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <span class="fw-semibold">Filter Sections</span>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-1" id="filterSectionsSummaryBadge" style="font-size: 0.75rem;">
                        All {{ $totalSectionsCount }} selected
                    </span>
                    <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
                </button>

                <!-- Popover Menu -->
                <div class="section-filter-popover" id="sectionFilterPopover" style="display: none;">
                    <!-- Popover Header -->
                    <div class="popover-header-custom p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.88rem;">
                            <i class="bi bi-ui-checks-grid text-primary"></i>
                            <span>Filter Questionnaire Sections</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small">
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" id="btnSelectAllSections">Select All</button>
                            <span class="text-muted">•</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-secondary" id="btnDeselectAllSections">Deselect All</button>
                        </div>
                    </div>

                    <!-- Search Box Inside Popover -->
                    <div class="p-2 border-bottom bg-white">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="sectionFilterSearchInput" placeholder="Quick search sections..." autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary d-none" id="sectionFilterSearchClear" title="Clear search"><i class="bi bi-x"></i></button>
                        </div>
                    </div>

                    <!-- Popover Body: Checkboxes List -->
                    <div class="popover-body-custom p-2" style="max-height: 290px; overflow-y: auto;" id="sectionCheckboxesList">
                        @foreach($grouped as $secTitle => $secItems)
                            @php
                                $secSlug = Str::slug($secTitle);
                            @endphp
                            <label class="section-checkbox-item d-flex align-items-center justify-content-between p-2 rounded cursor-pointer" data-sec-title="{{ strtolower($secTitle) }}">
                                <div class="form-check d-flex align-items-center gap-2 m-0 flex-grow-1 min-w-0">
                                    <input class="form-check-input section-filter-cb flex-shrink-0" 
                                           type="checkbox" 
                                           value="{{ $secSlug }}" 
                                           id="cb_sec_{{ $secSlug }}" 
                                           data-title="{{ $secTitle }}" 
                                           data-count="{{ $secItems->count() }}"
                                           checked>
                                    <span class="form-check-label text-dark small fw-medium text-truncate" for="cb_sec_{{ $secSlug }}" title="{{ $secTitle }}">
                                        {{ $secTitle }}
                                    </span>
                                </div>
                                <span class="badge bg-light text-secondary border ms-2 flex-shrink-0" style="font-size: 0.72rem;">
                                    {{ $secItems->count() }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <!-- Popover Footer Actions -->
                    <div class="popover-footer-custom p-2 px-3 border-top bg-light d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetSectionFilters">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset / All
                        </button>
                        <button type="button" class="btn btn-sm btn-primary px-3" id="btnApplySectionFilters">
                            <i class="bi bi-check2 me-1"></i> Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Visible Stats Strip -->
            <div class="d-flex align-items-center gap-2 text-muted small ms-2">
                <span class="badge-modern badge-slate" id="totalVisibleQuestionsBadge">
                    {{ $totalQuestionsCount }} Questions
                </span>
                <span class="badge-modern badge-shibaura" id="totalVisibleSectionsBadge">
                    {{ $totalSectionsCount }} Sections
                </span>
            </div>
        </div>

        <!-- Right: Collapse / Expand All Action -->
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-modern-secondary btn-sm" id="btnToggleAllSections" title="Expand or collapse question lists">
                <i class="bi bi-arrows-collapse me-1" id="toggleSectionsIcon"></i>
                <span id="toggleSectionsText">Collapse All</span>
            </button>
        </div>
    </div>

    <!-- Removable Chips / Tags Row -->
    <div class="section-chips-container mt-3 pt-2 border-top d-flex flex-wrap align-items-center gap-2" id="sectionChipsContainer">
        <!-- Rendered dynamically via JavaScript -->
    </div>
</div>

<!-- Section-Wise Grouped Question Cards -->
@forelse($grouped as $sectionTitle => $sectionQuestions)
    @php
        $sectionSlug = Str::slug($sectionTitle);
    @endphp
    <div class="card-modern mb-4 section-question-card" 
         id="section-{{ $sectionSlug }}" 
         data-section-slug="{{ $sectionSlug }}" 
         data-section-title="{{ $sectionTitle }}" 
         data-question-count="{{ $sectionQuestions->count() }}">
        <div class="card-header bg-white d-flex align-items-center justify-content-between cursor-pointer section-card-header" 
             style="border-left: 4px solid var(--shibaura-blue); user-select: none;"
             title="Click to toggle section questions">
            <div class="d-flex align-items-center gap-2">
                <span class="stat-icon-bubble shibaura" style="width: 34px; height: 34px; font-size: 1rem;">
                    <i class="bi bi-ui-checks-grid"></i>
                </span>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">{{ $sectionTitle }}</h6>
                    <span class="text-muted small" style="font-size: 0.73rem;">{{ $sectionQuestions->count() }} questions in this section</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-modern badge-shibaura">Section {{ Str::after(Str::before($sectionTitle, '—'), 'Section ') }}</span>
                <i class="bi bi-chevron-down text-muted section-toggle-chevron ms-1 transition-transform"></i>
            </div>
        </div>

        <div class="table-responsive section-table-wrap">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 65px;">Seq</th>
                        <th>Question</th>
                        <th>Response Type</th>
                        <th>Evaluation Options / Scale</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($sectionQuestions as $q)
                    <tr>
                        <td>
                            <span class="badge-modern badge-slate fw-bold">#{{ $q->sort_order }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $q->question }}</div>
                            <div class="small text-muted">ID: #{{ $q->id }}</div>
                        </td>
                        <td>
                            @if($q->type === 'rating')
                                <span class="badge-modern badge-amber">
                                    <i class="bi bi-star-fill"></i> 1-5 Rating Scale
                                </span>
                            @elseif($q->type === 'mcq')
                                <span class="badge-modern badge-shibaura">
                                    <i class="bi bi-ui-checks"></i> Multiple Choice
                                </span>
                            @else
                                <span class="badge-modern badge-indigo">
                                    <i class="bi bi-card-text"></i> Free Text Area
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($q->options && count($q->options) > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($q->options as $opt)
                                        <span class="badge bg-light text-dark border py-1 px-2 small">{{ $opt }}</span>
                                    @endforeach
                                </div>
                            @elseif($q->type === 'rating')
                                <span class="text-muted small">1 (Very Poor) to 5 (Excellent)</span>
                            @else
                                <span class="text-muted small fst-italic">Open remarks</span>
                            @endif
                        </td>
                        <td>
                            @if($q->is_required)
                                <span class="badge-modern badge-rose">Mandatory</span>
                            @else
                                <span class="badge-modern badge-slate">Optional</span>
                            @endif
                        </td>
                        <td>
                            @if($q->is_active)
                                <span class="badge-modern badge-shibaura">
                                    <span class="status-dot active"></span> Active
                                </span>
                            @else
                                <span class="badge-modern badge-slate">
                                    <span class="status-dot inactive"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('questions.edit', $q) }}" class="btn-action-icon edit" title="Edit Question">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="POST" action="{{ route('questions.destroy', $q) }}" class="d-inline"
                                      onsubmit="return confirm('Deactivate or delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action-icon delete" title="Delete Question">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card-modern p-5 text-center text-muted">
        <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-slate-300"></i>
        <h5>No questions configured.</h5>
        <p class="small text-muted">Click the "+ Add Question" button above to configure questions.</p>
    </div>
@endforelse

<!-- Empty State: Zero Sections Selected Notice -->
<div class="card-modern p-5 text-center my-4 d-none" id="noSectionsSelectedNotice">
    <div class="stat-icon-bubble mx-auto mb-3 slate" style="width: 54px; height: 54px; font-size: 1.6rem;">
        <i class="bi bi-filter-circle"></i>
    </div>
    <h5 class="fw-bold text-dark">No Questionnaire Sections Selected</h5>
    <p class="text-muted small mb-3">All sections are currently filtered out. Use the filter dropdown above or click below to show all sections.</p>
    <button type="button" class="btn-modern-primary btn-sm px-3" onclick="resetAllSectionFilters()">
        <i class="bi bi-check2-all me-1"></i> Show All Sections
    </button>
</div>

<style>
/* Section Filter Popover Styling */
.section-filter-popover {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 390px;
    max-width: 92vw;
    background: #ffffff;
    border: 1px solid var(--slate-200);
    border-radius: 12px;
    z-index: 1050;
    box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.18), 0 4px 12px -2px rgba(15, 23, 42, 0.08);
    animation: fadeInDown 0.15s ease-out;
}

.section-checkbox-item {
    transition: background-color 0.15s ease;
}
.section-checkbox-item:hover {
    background-color: #f1f5f9;
}
.section-checkbox-item input:checked ~ span {
    color: var(--slate-900);
    font-weight: 600;
}

/* Removable Chips Styling */
.section-chip {
    transition: all 0.2s ease;
    font-size: 0.8rem;
    line-height: 1.2;
    cursor: default;
    background: #ffffff;
    border: 1.5px solid var(--slate-200);
    border-radius: 50px;
    padding: 0.28rem 0.65rem;
}
.section-chip:hover {
    border-color: var(--shibaura-blue);
    background-color: #f8fafc;
}
.section-chip .btn-close-chip {
    cursor: pointer;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    color: var(--slate-400);
    border: none;
    background: transparent;
    padding: 0;
    margin-left: 0.35rem;
    font-size: 0.85rem;
    transition: color 0.15s ease, transform 0.15s ease;
}
.section-chip .btn-close-chip:hover {
    color: #ef4444;
    transform: scale(1.15);
}

.section-card-header {
    transition: background-color 0.15s ease;
}
.section-card-header:hover {
    background-color: #f8fafc !important;
}
.section-toggle-chevron {
    transition: transform 0.2s ease;
}
.section-toggle-chevron.is-collapsed {
    transform: rotate(-90deg);
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownWrap = document.getElementById('sectionFilterDropdownWrap');
    const dropdownBtn = document.getElementById('sectionFilterDropdownBtn');
    const popover = document.getElementById('sectionFilterPopover');
    const searchInput = document.getElementById('sectionFilterSearchInput');
    const searchClear = document.getElementById('sectionFilterSearchClear');
    const checkboxesList = document.getElementById('sectionCheckboxesList');
    const checkboxes = Array.from(checkboxesList ? checkboxesList.querySelectorAll('.section-filter-cb') : []);
    const chipsContainer = document.getElementById('sectionChipsContainer');
    const summaryBadge = document.getElementById('filterSectionsSummaryBadge');
    const totalVisQuestions = document.getElementById('totalVisibleQuestionsBadge');
    const totalVisSections = document.getElementById('totalVisibleSectionsBadge');
    const noSectionsNotice = document.getElementById('noSectionsSelectedNotice');
    const sectionCards = Array.from(document.querySelectorAll('.section-question-card'));

    const btnSelectAll = document.getElementById('btnSelectAllSections');
    const btnDeselectAll = document.getElementById('btnDeselectAllSections');
    const btnReset = document.getElementById('btnResetSectionFilters');
    const btnApply = document.getElementById('btnApplySectionFilters');
    const btnToggleAll = document.getElementById('btnToggleAllSections');
    const toggleIcon = document.getElementById('toggleSectionsIcon');
    const toggleText = document.getElementById('toggleSectionsText');

    let allCollapsed = false;

    // Toggle popover open / close
    function openPopover() {
        if (!popover) return;
        popover.style.display = 'block';
        dropdownBtn.setAttribute('aria-expanded', 'true');
        if (searchInput) {
            searchInput.value = '';
            filterCheckboxItems('');
            setTimeout(() => searchInput.focus(), 30);
        }
    }

    function closePopover() {
        if (!popover) return;
        popover.style.display = 'none';
        dropdownBtn.setAttribute('aria-expanded', 'false');
    }

    if (dropdownBtn) {
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (popover.style.display === 'block') {
                closePopover();
            } else {
                openPopover();
            }
        });
    }

    // Close on click outside or Escape
    document.addEventListener('click', function(e) {
        if (popover && dropdownWrap && !dropdownWrap.contains(e.target)) {
            closePopover();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popover && popover.style.display === 'block') {
            closePopover();
            dropdownBtn.focus();
        }
    });

    // Quick search inside popover
    function filterCheckboxItems(query) {
        query = query.trim().toLowerCase();
        const items = checkboxesList.querySelectorAll('.section-checkbox-item');
        items.forEach(item => {
            const title = item.getAttribute('data-sec-title') || '';
            const match = !query || title.includes(query);
            item.classList.toggle('d-none', !match);
        });
        if (searchClear) {
            searchClear.classList.toggle('d-none', query.length === 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterCheckboxItems(this.value);
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            filterCheckboxItems('');
            searchInput.focus();
        });
    }

    // Select All / Deselect All
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateSummaryBadgeAndChips();
        });
    }

    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateSummaryBadgeAndChips();
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            applyFilters();
            closePopover();
        });
    }

    if (btnApply) {
        btnApply.addEventListener('click', function() {
            applyFilters();
            closePopover();
        });
    }

    // Checkbox live change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            applyFilters();
        });
    });

    // Apply filters to page section cards
    function applyFilters() {
        const selectedSlugs = new Set(checkboxes.filter(cb => cb.checked).map(cb => cb.value));
        let visibleQuestionsCount = 0;
        let visibleSectionsCount = 0;

        sectionCards.forEach(card => {
            const slug = card.getAttribute('data-section-slug');
            const count = parseInt(card.getAttribute('data-question-count') || '0', 10);
            const isVisible = selectedSlugs.has(slug);

            if (isVisible) {
                card.style.display = 'block';
                visibleQuestionsCount += count;
                visibleSectionsCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Notice when 0 sections selected
        if (noSectionsNotice) {
            noSectionsNotice.classList.toggle('d-none', visibleSectionsCount > 0);
        }

        // Update counts
        if (totalVisQuestions) {
            totalVisQuestions.textContent = `${visibleQuestionsCount} Questions`;
        }
        if (totalVisSections) {
            totalVisSections.textContent = `${visibleSectionsCount} Sections`;
        }

        updateSummaryBadgeAndChips();
    }

    // Update summary badge & render chips
    function updateSummaryBadgeAndChips() {
        const total = checkboxes.length;
        const selected = checkboxes.filter(cb => cb.checked);
        const selectedCount = selected.length;

        // Update dropdown button summary badge
        if (summaryBadge) {
            if (selectedCount === total) {
                summaryBadge.textContent = `All ${total} selected`;
                summaryBadge.className = 'badge bg-primary text-white rounded-pill px-2 py-1';
            } else if (selectedCount === 0) {
                summaryBadge.textContent = `0 of ${total} selected`;
                summaryBadge.className = 'badge bg-danger text-white rounded-pill px-2 py-1';
            } else {
                summaryBadge.textContent = `${selectedCount} of ${total} selected`;
                summaryBadge.className = 'badge bg-info text-dark rounded-pill px-2 py-1';
            }
        }

        // Render chips
        if (!chipsContainer) return;
        chipsContainer.innerHTML = '';

        if (selectedCount === 0) {
            chipsContainer.innerHTML = `
                <div class="d-flex align-items-center gap-2 small text-danger py-1">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>All sections hidden by filter.</span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" onclick="resetAllSectionFilters()">
                        Show All Sections
                    </button>
                </div>
            `;
            return;
        }

        // Header label for chips
        const labelEl = document.createElement('span');
        labelEl.className = 'small fw-bold text-muted d-flex align-items-center gap-1 me-1';
        labelEl.innerHTML = `<i class="bi bi-tags-fill text-primary"></i> Active Sections:`;
        chipsContainer.appendChild(labelEl);

        // Render individual chip for each selected section
        selected.forEach(cb => {
            const slug = cb.value;
            const title = cb.getAttribute('data-title') || '';
            const count = cb.getAttribute('data-count') || '0';

            const chip = document.createElement('div');
            chip.className = 'section-chip d-inline-flex align-items-center gap-1 shadow-xs';
            chip.setAttribute('data-sec-slug', slug);
            chip.innerHTML = `
                <span class="text-dark fw-medium text-truncate" style="max-width: 250px;" title="${title}">${title}</span>
                <span class="badge bg-light text-secondary border ms-1 py-0 px-1" style="font-size: 0.68rem;">${count}</span>
                <button type="button" class="btn-close-chip" title="Remove ${title}" onclick="removeSectionChip('${slug}')">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;
            chipsContainer.appendChild(chip);
        });

        // "Reset All" quick link if fewer than all are selected
        if (selectedCount < total) {
            const resetBtn = document.createElement('button');
            resetBtn.type = 'button';
            resetBtn.className = 'btn btn-link btn-sm text-decoration-none text-muted p-0 ms-2 small';
            resetBtn.innerHTML = `<i class="bi bi-arrow-counterclockwise"></i> Reset to All`;
            resetBtn.onclick = resetAllSectionFilters;
            chipsContainer.appendChild(resetBtn);
        }
    }

    // Remove single chip
    window.removeSectionChip = function(slug) {
        const cb = document.getElementById('cb_sec_' + slug);
        if (cb) {
            cb.checked = false;
            applyFilters();
        }
    };

    // Reset all filters
    window.resetAllSectionFilters = function() {
        checkboxes.forEach(cb => cb.checked = true);
        applyFilters();
        closePopover();
    };

    // Toggle expand / collapse individual card
    document.querySelectorAll('.section-card-header').forEach(hdr => {
        hdr.addEventListener('click', function() {
            const card = this.closest('.section-question-card');
            const tableWrap = card.querySelector('.section-table-wrap');
            const chevron = this.querySelector('.section-toggle-chevron');
            if (tableWrap) {
                const isHidden = tableWrap.style.display === 'none';
                tableWrap.style.display = isHidden ? 'block' : 'none';
                if (chevron) {
                    chevron.classList.toggle('is-collapsed', !isHidden);
                }
            }
        });
    });

    // Toggle expand / collapse all cards
    if (btnToggleAll) {
        btnToggleAll.addEventListener('click', function() {
            allCollapsed = !allCollapsed;
            sectionCards.forEach(card => {
                const tableWrap = card.querySelector('.section-table-wrap');
                const chevron = card.querySelector('.section-toggle-chevron');
                if (tableWrap) {
                    tableWrap.style.display = allCollapsed ? 'none' : 'block';
                }
                if (chevron) {
                    chevron.classList.toggle('is-collapsed', allCollapsed);
                }
            });

            if (toggleIcon) {
                toggleIcon.className = allCollapsed ? 'bi bi-arrows-expand me-1' : 'bi bi-arrows-collapse me-1';
            }
            if (toggleText) {
                toggleText.textContent = allCollapsed ? 'Expand All' : 'Collapse All';
            }
        });
    }

    // Initial render
    applyFilters();
});
</script>
@endpush
@endsection
