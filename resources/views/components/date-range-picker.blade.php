@props([
    'fromName' => 'from',
    'toName' => 'to',
    'fromValue' => null,
    'toValue' => null,
    'placeholder' => 'Select Date Range',
    'id' => null,
    'title' => 'Filter by Date Range',
    'minWidth' => '210px',
    'autoSubmit' => false,
])

@php
    $inputId = $id ?? ('daterange_' . $fromName . '_' . $toName . '_' . \Illuminate\Support\Str::random(6));
    $currentFrom = (string) ($fromValue ?? request($fromName, ''));
    $currentTo = (string) ($toValue ?? request($toName, ''));

    $displayFrom = '';
    if ($currentFrom) {
        try {
            $displayFrom = \Carbon\Carbon::parse($currentFrom)->format('d/m/Y');
        } catch (\Exception $e) {
            $displayFrom = $currentFrom;
        }
    }

    $displayTo = '';
    if ($currentTo) {
        try {
            $displayTo = \Carbon\Carbon::parse($currentTo)->format('d/m/Y');
        } catch (\Exception $e) {
            $displayTo = $currentTo;
        }
    }
@endphp

<div class="custom-daterangepicker-wrapper" 
     id="{{ $inputId }}_wrapper" 
     data-custom-daterangepicker-container
     data-auto-submit="{{ $autoSubmit ? 'true' : 'false' }}"
     style="min-width: {{ $minWidth }};">

    <!-- Hidden Inputs for Form Submission (YYYY-MM-DD) -->
    <input type="hidden" 
           name="{{ $fromName }}" 
           id="{{ $inputId }}_from" 
           value="{{ $currentFrom }}" 
           class="custom-daterange-hidden-from">

    <input type="hidden" 
           name="{{ $toName }}" 
           id="{{ $inputId }}_to" 
           value="{{ $currentTo }}" 
           class="custom-daterange-hidden-to">

    <!-- Trigger Button -->
    <div class="custom-daterangepicker-trigger" 
         tabindex="0" 
         role="button" 
         aria-haspopup="dialog" 
         aria-expanded="false" 
         title="{{ $title }}">
        
        <i class="bi bi-calendar3 custom-daterangepicker-icon text-primary"></i>
        
        <span class="custom-daterangepicker-text">
            @if($displayFrom && $displayTo)
                <span class="range-date-val">{{ $displayFrom }}</span>
                <span class="range-arrow-sep">→</span>
                <span class="range-date-val">{{ $displayTo }}</span>
            @elseif($displayFrom)
                <span class="range-date-val">From {{ $displayFrom }}</span>
            @elseif($displayTo)
                <span class="range-date-val">To {{ $displayTo }}</span>
            @else
                <span class="text-muted">{{ $placeholder }}</span>
            @endif
        </span>

        <button type="button" 
                class="custom-daterangepicker-clear {{ ($currentFrom || $currentTo) ? '' : 'd-none' }}" 
                title="Clear date range" 
                aria-label="Clear date range">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>

    <!-- Calendar Range Popup -->
    <div class="custom-daterangepicker-popup" style="display: none;" role="dialog" aria-modal="true">
        <!-- Quick Presets Toolbar -->
        <div class="daterange-presets-bar">
            <button type="button" class="btn-range-preset" data-preset="today">Today</button>
            <button type="button" class="btn-range-preset" data-preset="yesterday">Yesterday</button>
            <button type="button" class="btn-range-preset" data-preset="last7">Last 7 Days</button>
            <button type="button" class="btn-range-preset" data-preset="last30">Last 30 Days</button>
            <button type="button" class="btn-range-preset" data-preset="thisMonth">This Month</button>
        </div>

        <!-- Month Navigation Header -->
        <div class="custom-datepicker-header">
            <button type="button" class="btn-cal-nav prev-month" title="Previous Month">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="cal-month-year-title fw-bold"></div>
            <button type="button" class="btn-cal-nav next-month" title="Next Month">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Weekdays Header -->
        <div class="cal-weekdays">
            <div class="cal-weekday">Su</div>
            <div class="cal-weekday">Mo</div>
            <div class="cal-weekday">Tu</div>
            <div class="cal-weekday">We</div>
            <div class="cal-weekday">Th</div>
            <div class="cal-weekday">Fr</div>
            <div class="cal-weekday">Sa</div>
        </div>

        <!-- Days Grid -->
        <div class="cal-days-grid daterange-grid"></div>

        <!-- Footer Actions -->
        <div class="custom-daterangepicker-footer">
            <button type="button" class="btn-range-action-clear text-danger">Clear</button>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border py-1 px-3 btn-range-action-cancel">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary py-1 px-3 btn-range-action-apply">Apply</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    const MONTH_NAMES = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    function padZero(num) {
        return num < 10 ? '0' + num : '' + num;
    }

    function formatDateDisplay(dateObj) {
        if (!dateObj) return '';
        return padZero(dateObj.getDate()) + '/' + padZero(dateObj.getMonth() + 1) + '/' + dateObj.getFullYear();
    }

    function formatDateInput(dateObj) {
        if (!dateObj) return '';
        return dateObj.getFullYear() + '-' + padZero(dateObj.getMonth() + 1) + '-' + padZero(dateObj.getDate());
    }

    function parseYMD(str) {
        if (!str || typeof str !== 'string') return null;
        const parts = str.trim().split('-');
        if (parts.length === 3) {
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                return new Date(y, m, d);
            }
        }
        return null;
    }

    function isSameDay(d1, d2) {
        if (!d1 || !d2) return false;
        return d1.getFullYear() === d2.getFullYear() &&
               d1.getMonth() === d2.getMonth() &&
               d1.getDate() === d2.getDate();
    }

    function initCustomDateRangePicker(container) {
        if (container._dateRangePickerInitialized) return;
        container._dateRangePickerInitialized = true;

        const trigger = container.querySelector('.custom-daterangepicker-trigger');
        const triggerText = container.querySelector('.custom-daterangepicker-text');
        const clearBtn = container.querySelector('.custom-daterangepicker-clear');
        const hiddenFrom = container.querySelector('.custom-daterange-hidden-from');
        const hiddenTo = container.querySelector('.custom-daterange-hidden-to');
        const popup = container.querySelector('.custom-daterangepicker-popup');
        const titleEl = container.querySelector('.cal-month-year-title');
        const prevBtn = container.querySelector('.prev-month');
        const nextBtn = container.querySelector('.next-month');
        const daysGrid = container.querySelector('.cal-days-grid');
        const clearActionBtn = container.querySelector('.btn-range-action-clear');
        const cancelBtn = container.querySelector('.btn-range-action-cancel');
        const applyBtn = container.querySelector('.btn-range-action-apply');
        const presetBtns = container.querySelectorAll('.btn-range-preset');

        if (!trigger || !popup) return;

        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth() + 1;
        const currentDay = today.getDate();

        // Selected Range State
        let selectedFrom = parseYMD(hiddenFrom.value);
        let selectedTo = parseYMD(hiddenTo.value);

        // In-progress selection state inside open popup
        let tempFrom = selectedFrom ? new Date(selectedFrom) : null;
        let tempTo = selectedTo ? new Date(selectedTo) : null;
        let hoverDate = null;

        // Currently viewed calendar month
        let viewYear = (tempFrom || today).getFullYear();
        let viewMonth = (tempFrom || today).getMonth() + 1;

        function renderCalendar() {
            titleEl.textContent = MONTH_NAMES[viewMonth - 1] + ' ' + viewYear;
            daysGrid.innerHTML = '';

            const firstDayIndex = new Date(viewYear, viewMonth - 1, 1).getDay();
            const totalDaysInMonth = new Date(viewYear, viewMonth, 0).getDate();
            const totalDaysInPrevMonth = new Date(viewYear, viewMonth - 1, 0).getDate();

            // Previous month trailing days
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const dayNum = totalDaysInPrevMonth - i;
                const cell = document.createElement('div');
                cell.className = 'cal-day-cell other-month';
                cell.textContent = dayNum;

                const prevMonthNum = viewMonth === 1 ? 12 : viewMonth - 1;
                const prevYearNum = viewMonth === 1 ? viewYear - 1 : viewYear;
                const cellDate = new Date(prevYearNum, prevMonthNum - 1, dayNum);
                cell._dateObj = cellDate;

                applyRangeClasses(cell, cellDate);
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    onDayClick(cellDate);
                });
                cell.addEventListener('mouseenter', () => onDayHover(cellDate));
                daysGrid.appendChild(cell);
            }

            // Current month days
            for (let day = 1; day <= totalDaysInMonth; day++) {
                const cell = document.createElement('div');
                cell.className = 'cal-day-cell';
                cell.textContent = day;

                const cellDate = new Date(viewYear, viewMonth - 1, day);
                cell._dateObj = cellDate;

                if (viewYear === currentYear && viewMonth === currentMonth && day === currentDay) {
                    cell.classList.add('is-today');
                }

                applyRangeClasses(cell, cellDate);
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    onDayClick(cellDate);
                });
                cell.addEventListener('mouseenter', () => onDayHover(cellDate));
                daysGrid.appendChild(cell);
            }

            // Next month leading days to complete 35 or 42 grid
            const filledCells = firstDayIndex + totalDaysInMonth;
            const remainingCells = (filledCells <= 35 ? 35 : 42) - filledCells;
            for (let day = 1; day <= remainingCells; day++) {
                const cell = document.createElement('div');
                cell.className = 'cal-day-cell other-month';
                cell.textContent = day;

                const nextMonthNum = viewMonth === 12 ? 1 : viewMonth + 1;
                const nextYearNum = viewMonth === 12 ? viewYear + 1 : viewYear;
                const cellDate = new Date(nextYearNum, nextMonthNum - 1, day);
                cell._dateObj = cellDate;

                applyRangeClasses(cell, cellDate);
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    onDayClick(cellDate);
                });
                cell.addEventListener('mouseenter', () => onDayHover(cellDate));
                daysGrid.appendChild(cell);
            }
        }

        function applyRangeClasses(cell, cellDate) {
            cell.classList.remove('range-start', 'range-end', 'in-range', 'in-range-hover');

            const hasStart = !!tempFrom;
            const hasEnd = !!tempTo;

            const cTime = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate()).getTime();
            const sTime = hasStart ? new Date(tempFrom.getFullYear(), tempFrom.getMonth(), tempFrom.getDate()).getTime() : null;
            const eTime = hasEnd ? new Date(tempTo.getFullYear(), tempTo.getMonth(), tempTo.getDate()).getTime() : null;
            const hTime = hoverDate ? new Date(hoverDate.getFullYear(), hoverDate.getMonth(), hoverDate.getDate()).getTime() : null;

            if (sTime !== null && cTime === sTime) {
                cell.classList.add('range-start');
            }
            if (eTime !== null && cTime === eTime) {
                cell.classList.add('range-end');
            }

            // Both start and end selected
            if (sTime !== null && eTime !== null) {
                if (cTime > sTime && cTime < eTime) {
                    cell.classList.add('in-range');
                }
            } else if (sTime !== null && !hasEnd && hTime !== null) {
                // Hovering when only start date is selected
                if (hTime >= sTime && cTime > sTime && cTime <= hTime) {
                    cell.classList.add('in-range-hover');
                } else if (hTime < sTime && cTime < sTime && cTime >= hTime) {
                    cell.classList.add('in-range-hover');
                }
            }
        }

        function updateGridRangeHighlights() {
            const cells = daysGrid.querySelectorAll('.cal-day-cell');
            cells.forEach(cell => {
                if (cell._dateObj) {
                    applyRangeClasses(cell, cell._dateObj);
                }
            });
        }

        function onDayClick(date) {
            const clickedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            if (!tempFrom || (tempFrom && tempTo)) {
                // First click: sets start date, clears end date
                tempFrom = clickedDate;
                tempTo = null;
                hoverDate = null;
                updateGridRangeHighlights();
            } else if (tempFrom && !tempTo) {
                // Second click: sets end date
                if (clickedDate.getTime() < tempFrom.getTime()) {
                    tempTo = new Date(tempFrom);
                    tempFrom = clickedDate;
                } else {
                    tempTo = clickedDate;
                }
                hoverDate = null;
                updateGridRangeHighlights();
                // Automatically apply and close once range is chosen
                applySelectedRange();
            }
        }

        function onDayHover(date) {
            if (tempFrom && !tempTo) {
                hoverDate = date;
                updateGridRangeHighlights();
            }
        }

        daysGrid.addEventListener('mouseleave', function() {
            if (tempFrom && !tempTo && hoverDate) {
                hoverDate = null;
                updateGridRangeHighlights();
            }
        });

        function openPopup() {
            // Close any other open popups across the whole site!
            if (window.closeAllPopups) {
                window.closeAllPopups(container);
            }

            container.classList.add('is-open');
            popup.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');

            // Reset temp state to current committed selection
            tempFrom = selectedFrom ? new Date(selectedFrom) : null;
            tempTo = selectedTo ? new Date(selectedTo) : null;
            hoverDate = null;

            viewYear = (tempFrom || today).getFullYear();
            viewMonth = (tempFrom || today).getMonth() + 1;
            renderCalendar();
        }

        function closePopup() {
            container.classList.remove('is-open');
            popup.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
            hoverDate = null;
        }

        container._closeDateRangePicker = closePopup;

        function applySelectedRange() {
            if (tempFrom && !tempTo) {
                // Single day selection: set to = from
                tempTo = new Date(tempFrom);
            }

            selectedFrom = tempFrom ? new Date(tempFrom) : null;
            selectedTo = tempTo ? new Date(tempTo) : null;

            const ymdFrom = selectedFrom ? formatDateInput(selectedFrom) : '';
            const ymdTo = selectedTo ? formatDateInput(selectedTo) : '';

            hiddenFrom.value = ymdFrom;
            hiddenTo.value = ymdTo;

            updateTriggerDisplay();
            closePopup();

            // Trigger change event
            hiddenFrom.dispatchEvent(new Event('change', { bubbles: true }));
            hiddenTo.dispatchEvent(new Event('change', { bubbles: true }));

            // Optional auto-submit
            if (container.getAttribute('data-auto-submit') === 'true') {
                const form = container.closest('form');
                if (form) form.submit();
            }
        }

        function updateTriggerDisplay() {
            const hasFrom = !!hiddenFrom.value;
            const hasTo = !!hiddenTo.value;

            if (hasFrom && hasTo) {
                const d1 = formatDateDisplay(selectedFrom);
                const d2 = formatDateDisplay(selectedTo);
                if (hiddenFrom.value === hiddenTo.value) {
                    triggerText.innerHTML = `<span class="range-date-val">${d1}</span>`;
                } else {
                    triggerText.innerHTML = `
                        <span class="range-date-val">${d1}</span>
                        <span class="range-arrow-sep">→</span>
                        <span class="range-date-val">${d2}</span>
                    `;
                }
                if (clearBtn) clearBtn.classList.remove('d-none');
            } else if (hasFrom) {
                const d1 = formatDateDisplay(selectedFrom);
                triggerText.innerHTML = `<span class="range-date-val">From ${d1}</span>`;
                if (clearBtn) clearBtn.classList.remove('d-none');
            } else if (hasTo) {
                const d2 = formatDateDisplay(selectedTo);
                triggerText.innerHTML = `<span class="range-date-val">To ${d2}</span>`;
                if (clearBtn) clearBtn.classList.remove('d-none');
            } else {
                triggerText.innerHTML = `<span class="text-muted">{{ $placeholder }}</span>`;
                if (clearBtn) clearBtn.classList.add('d-none');
            }
        }

        function clearRange() {
            selectedFrom = null;
            selectedTo = null;
            tempFrom = null;
            tempTo = null;
            hoverDate = null;

            hiddenFrom.value = '';
            hiddenTo.value = '';

            updateTriggerDisplay();
            closePopup();

            hiddenFrom.dispatchEvent(new Event('change', { bubbles: true }));
            hiddenTo.dispatchEvent(new Event('change', { bubbles: true }));

            if (container.getAttribute('data-auto-submit') === 'true') {
                const form = container.closest('form');
                if (form) form.submit();
            }
        }

        // Quick Presets
        presetBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const preset = this.getAttribute('data-preset');
                const now = new Date();

                switch(preset) {
                    case 'today':
                        tempFrom = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        tempTo = new Date(tempFrom);
                        break;
                    case 'yesterday':
                        tempFrom = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                        tempTo = new Date(tempFrom);
                        break;
                    case 'last7':
                        tempTo = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        tempFrom = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6);
                        break;
                    case 'last30':
                        tempTo = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        tempFrom = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29);
                        break;
                    case 'thisMonth':
                        tempFrom = new Date(now.getFullYear(), now.getMonth(), 1);
                        tempTo = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        break;
                }

                applySelectedRange();
            });
        });

        // Prevent clicks inside popup from bubbling to document
        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Trigger Click
        trigger.addEventListener('click', function(e) {
            if (e.target.closest('.custom-daterangepicker-clear')) return;
            e.stopPropagation();
            if (container.classList.contains('is-open')) {
                closePopup();
            } else {
                openPopup();
            }
        });

        // Clear Icon on Trigger
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                clearRange();
            });
        }

        // Navigation
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (viewMonth === 1) {
                viewMonth = 12;
                viewYear--;
            } else {
                viewMonth--;
            }
            renderCalendar();
        });

        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (viewMonth === 12) {
                viewMonth = 1;
                viewYear++;
            } else {
                viewMonth++;
            }
            renderCalendar();
        });

        // Footer Actions
        clearActionBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            clearRange();
        });

        cancelBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closePopup();
        });

        applyBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            applySelectedRange();
        });

        // Outside Click
        document.addEventListener('click', function(e) {
            if (!e.target || !e.target.isConnected) return;
            if (!container.contains(e.target)) {
                closePopup();
            }
        });
    }

    function initAllDateRangePickers() {
        document.querySelectorAll('[data-custom-daterangepicker-container]').forEach(initCustomDateRangePicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllDateRangePickers);
    } else {
        initAllDateRangePickers();
    }
})();
</script>
@endpush
@endonce
