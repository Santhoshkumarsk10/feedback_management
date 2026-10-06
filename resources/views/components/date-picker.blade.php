@props([
    'name',
    'value' => null,
    'placeholder' => 'dd/mm/yyyy',
    'id' => null,
    'title' => 'Select Date',
    'minWidth' => '135px',
])

@php
    $inputId = $id ?? ('date_picker_' . $name . '_' . \Illuminate\Support\Str::random(6));
    $currentVal = (string) ($value ?? request($name, ''));

    // Format display date: if YYYY-MM-DD, format to DD/MM/YYYY
    $displayText = '';
    if ($currentVal) {
        try {
            $displayText = \Carbon\Carbon::parse($currentVal)->format('d/m/Y');
        } catch (\Exception $e) {
            $displayText = $currentVal;
        }
    }
@endphp

<div class="custom-datepicker-wrapper" 
     id="{{ $inputId }}_wrapper" 
     data-custom-datepicker-container
     style="min-width: {{ $minWidth }};">

    <!-- Hidden Input for Form Submission (YYYY-MM-DD) -->
    <input type="hidden" 
           name="{{ $name }}" 
           id="{{ $inputId }}" 
           value="{{ $currentVal }}" 
           class="custom-datepicker-hidden">

    <!-- Trigger Button -->
    <div class="custom-datepicker-trigger" 
         tabindex="0" 
         role="button" 
         aria-haspopup="dialog" 
         aria-expanded="false" 
         title="{{ $title }}">
        
        <i class="bi bi-calendar3 custom-datepicker-icon"></i>
        
        <span class="custom-datepicker-text">
            @if($displayText)
                {{ $displayText }}
            @else
                <span class="text-muted">{{ $placeholder }}</span>
            @endif
        </span>

        <button type="button" 
                class="custom-datepicker-clear {{ $currentVal ? '' : 'd-none' }}" 
                title="Clear date" 
                aria-label="Clear date">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>

    <!-- Calendar Popup -->
    <div class="custom-datepicker-popup" style="display: none;" role="dialog" aria-modal="true">
        <!-- Month / Year Header -->
        <div class="custom-datepicker-header">
            <button type="button" class="btn-cal-nav prev-month" title="Previous Month">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="cal-month-year-title"></div>
            <button type="button" class="btn-cal-nav next-month" title="Next Month">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Weekdays Row -->
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
        <div class="cal-days-grid"></div>

        <!-- Footer Actions -->
        <div class="custom-datepicker-footer">
            <button type="button" class="btn-cal-action clear">Clear</button>
            <button type="button" class="btn-cal-action today">Today</button>
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

    function formatDateForDisplay(year, month, day) {
        return padZero(day) + '/' + padZero(month) + '/' + year;
    }

    function formatDateForInput(year, month, day) {
        return year + '-' + padZero(month) + '-' + padZero(day);
    }

    function initCustomDatePicker(container) {
        if (container._datePickerInitialized) return;
        container._datePickerInitialized = true;

        const trigger = container.querySelector('.custom-datepicker-trigger');
        const triggerText = container.querySelector('.custom-datepicker-text');
        const clearBtn = container.querySelector('.custom-datepicker-clear');
        const hiddenInput = container.querySelector('.custom-datepicker-hidden');
        const popup = container.querySelector('.custom-datepicker-popup');
        const titleEl = container.querySelector('.cal-month-year-title');
        const prevBtn = container.querySelector('.prev-month');
        const nextBtn = container.querySelector('.next-month');
        const daysGrid = container.querySelector('.cal-days-grid');
        const todayBtn = container.querySelector('.btn-cal-action.today');
        const clearActionBtn = container.querySelector('.btn-cal-action.clear');

        if (!trigger || !popup) return;

        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth() + 1;
        const currentDay = today.getDate();

        // Active state
        let selectedYear = null;
        let selectedMonth = null;
        let selectedDay = null;

        // Parse initial value if present (YYYY-MM-DD)
        if (hiddenInput.value) {
            const parts = hiddenInput.value.split('-');
            if (parts.length === 3) {
                selectedYear = parseInt(parts[0], 10);
                selectedMonth = parseInt(parts[1], 10);
                selectedDay = parseInt(parts[2], 10);
            }
        }

        // Currently viewed month/year in calendar
        let viewYear = selectedYear || currentYear;
        let viewMonth = selectedMonth || currentMonth;

        function renderCalendar() {
            titleEl.textContent = MONTH_NAMES[viewMonth - 1] + ' ' + viewYear;
            daysGrid.innerHTML = '';

            const firstDayIndex = new Date(viewYear, viewMonth - 1, 1).getDay(); // 0 is Sunday
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
                cell.addEventListener('click', () => {
                    selectDate(prevYearNum, prevMonthNum, dayNum);
                });
                daysGrid.appendChild(cell);
            }

            // Current month days
            for (let day = 1; day <= totalDaysInMonth; day++) {
                const cell = document.createElement('div');
                cell.className = 'cal-day-cell';
                cell.textContent = day;

                const isToday = (viewYear === currentYear && viewMonth === currentMonth && day === currentDay);
                if (isToday) cell.classList.add('is-today');

                const isSelected = (viewYear === selectedYear && viewMonth === selectedMonth && day === selectedDay);
                if (isSelected) cell.classList.add('is-selected');

                cell.addEventListener('click', () => {
                    selectDate(viewYear, viewMonth, day);
                });
                daysGrid.appendChild(cell);
            }

            // Next month leading days to complete grid (42 cells total)
            const filledCells = firstDayIndex + totalDaysInMonth;
            const remainingCells = (filledCells <= 35 ? 35 : 42) - filledCells;
            for (let day = 1; day <= remainingCells; day++) {
                const cell = document.createElement('div');
                cell.className = 'cal-day-cell other-month';
                cell.textContent = day;

                const nextMonthNum = viewMonth === 12 ? 1 : viewMonth + 1;
                const nextYearNum = viewMonth === 12 ? viewYear + 1 : viewYear;
                cell.addEventListener('click', () => {
                    selectDate(nextYearNum, nextMonthNum, day);
                });
                daysGrid.appendChild(cell);
            }
        }

        function openPopup() {
            // Close other open popups first
            document.querySelectorAll('[data-custom-datepicker-container].is-open').forEach(other => {
                if (other !== container && other._closeDatePicker) other._closeDatePicker();
            });

            container.classList.add('is-open');
            popup.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');

            // Sync viewed month/year to selected or today
            viewYear = selectedYear || currentYear;
            viewMonth = selectedMonth || currentMonth;
            renderCalendar();
        }

        function closePopup() {
            container.classList.remove('is-open');
            popup.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
        }

        container._closeDatePicker = closePopup;

        function selectDate(year, month, day) {
            selectedYear = year;
            selectedMonth = month;
            selectedDay = day;

            const ymd = formatDateForInput(year, month, day);
            const dmy = formatDateForDisplay(year, month, day);

            hiddenInput.value = ymd;
            triggerText.innerHTML = dmy;
            if (clearBtn) clearBtn.classList.remove('d-none');

            closePopup();

            // Dispatch change event on hidden input in case listeners exist
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function clearDate() {
            selectedYear = null;
            selectedMonth = null;
            selectedDay = null;

            hiddenInput.value = '';
            triggerText.innerHTML = '<span class="text-muted">dd/mm/yyyy</span>';
            if (clearBtn) clearBtn.classList.add('d-none');

            closePopup();
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Toggle on trigger click
        trigger.addEventListener('click', function(e) {
            if (e.target.closest('.custom-datepicker-clear')) return;
            if (container.classList.contains('is-open')) {
                closePopup();
            } else {
                openPopup();
            }
        });

        // Trigger keyboard open
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                if (!container.classList.contains('is-open')) openPopup();
            } else if (e.key === 'Escape') {
                closePopup();
            }
        });

        // Clear icon on trigger
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                clearDate();
            });
        }

        // Navigation buttons
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

        // Footer buttons
        todayBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            selectDate(currentYear, currentMonth, currentDay);
        });

        clearActionBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            clearDate();
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closePopup();
            }
        });
    }

    function initAllDatePickers() {
        document.querySelectorAll('[data-custom-datepicker-container]').forEach(initCustomDatePicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllDatePickers);
    } else {
        initAllDatePickers();
    }
})();
</script>
@endpush
@endonce
