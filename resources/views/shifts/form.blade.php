@extends('layouts.app')
@section('title', $shift->exists ? 'Edit Shift: '.$shift->code : 'Register New Shift')
@section('page_title', $shift->exists ? 'Edit Shift Schedule' : 'Register Factory Shift')
@section('page_subtitle', $shift->exists ? 'Update shift operating hours, identifier, and status' : 'Define factory shift timings for plant production and visitor scheduling')

@section('content')
<div class="mb-3">
    <a href="{{ route('shifts.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Shift List
    </a>
</div>

<div class="row g-4">
    <!-- Main Shift Form Column -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>{{ $shift->exists ? 'Modify Shift Details' : 'New Shift Configuration' }}</span>
                </div>
                @if($shift->exists)
                    <span class="badge-modern badge-shibaura">{{ $shift->code }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                @if(! $shift->exists)
                    <!-- Quick Preset Selectors -->
                    <div class="mb-4 p-3 rounded-3 bg-light border border-slate-200">
                        <div class="small fw-bold text-dark mb-2 d-flex align-items-center gap-1">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Quick Preset Fill (Standard Shifts):</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shift-preset-btn"
                                    data-code="SHIFT-A" data-name="Shift A" data-start="06:00" data-end="14:00"
                                    data-desc="Morning production operations & customer plant visitor tours">
                                <i class="bi bi-sun me-1"></i> Shift A (06:00 – 14:00)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shift-preset-btn"
                                    data-code="SHIFT-B" data-name="Shift B" data-start="14:00" data-end="22:00"
                                    data-desc="Afternoon & evening manufacturing and assembly line shift">
                                <i class="bi bi-sunset me-1"></i> Shift B (14:00 – 22:00)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shift-preset-btn"
                                    data-code="SHIFT-C" data-name="Shift C" data-start="22:00" data-end="06:00"
                                    data-desc="Night operational shift and overnight maintenance monitoring">
                                <i class="bi bi-moon-stars me-1"></i> Shift C (22:00 – 06:00)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shift-preset-btn"
                                    data-code="SHIFT-GEN" data-name="General Shift" data-start="08:30" data-end="17:30"
                                    data-desc="Regular administrative and technical support shift">
                                <i class="bi bi-briefcase me-1"></i> General (08:30 – 17:30)
                            </button>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ $shift->exists ? route('shifts.update', $shift) : route('shifts.store') }}" id="shiftForm" novalidate>
                    @csrf
                    @if($shift->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4 mb-4">
                        <!-- Shift Code -->
                        <div class="col-md-5">
                            <label class="form-modern-label" for="shift_code">Shift Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-qr-code"></i></span>
                                <input name="code" 
                                       id="shift_code"
                                       class="form-control form-control-modern border-start-0 @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $shift->code) }}" 
                                       placeholder="e.g. SHIFT-A" 
                                       required 
                                       minlength="2"
                                       maxlength="30"
                                       style="text-transform: uppercase;">
                            </div>
                            <div class="form-text text-muted small mt-1">Unique shift identifier (e.g. SHIFT-A, SHIFT-B)</div>
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shift Display Name -->
                        <div class="col-md-7">
                            <label class="form-modern-label" for="shift_name">Shift Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-bookmark"></i></span>
                                <input name="name" 
                                       id="shift_name"
                                       class="form-control form-control-modern border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $shift->name) }}" 
                                       placeholder="e.g. Shift A" 
                                       required
                                       minlength="2"
                                       maxlength="100">
                            </div>
                            <div class="form-text text-muted small mt-1">Official operational shift title</div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shift Timings (Start & End) -->
                        <div class="col-md-6">
                            <label class="form-modern-label" for="shift_start_time">Start Time (24H) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-clock"></i></span>
                                <input type="time"
                                       name="start_time" 
                                       id="shift_start_time"
                                       class="form-control form-control-modern border-start-0 @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', $shift->exists ? $shift->start_time_short : ($shift->start_time ?? '06:00')) }}" 
                                       required>
                            </div>
                            <div class="form-text text-muted small mt-1" id="start_12h_preview">12-Hour format preview</div>
                            @error('start_time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label" for="shift_end_time">End Time (24H) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-clock-fill"></i></span>
                                <input type="time"
                                       name="end_time" 
                                       id="shift_end_time"
                                       class="form-control form-control-modern border-start-0 @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', $shift->exists ? $shift->end_time_short : ($shift->end_time ?? '14:00')) }}" 
                                       required>
                            </div>
                            <div class="form-text text-muted small mt-1" id="end_12h_preview">12-Hour format preview</div>
                            @error('end_time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shift Computation Info Banner -->
                        <div class="col-md-12">
                            <div class="p-3 rounded-3 bg-white border border-slate-200 shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3" id="shiftSummaryCard">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-2 bg-primary-subtle text-primary fs-4" id="summaryIcon">
                                        <i class="bi bi-stopwatch"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Calculated Shift Scope</div>
                                        <div class="fw-bold fs-6 text-dark" id="summaryRangeText">06:00 – 14:00</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge-modern badge-slate fs-6" id="summaryDurationBadge">8 hrs</span>
                                    <span class="badge-modern badge-cyan" id="summaryTypeBadge">Day Shift</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-modern-label mb-0" for="shift_description">Operational Description & Notes</label>
                                <span class="small text-muted char-count" data-target="shift_description">0 / 1000</span>
                            </div>
                            <textarea name="description" 
                                      id="shift_description"
                                      rows="3" 
                                      class="form-control form-control-modern @error('description') is-invalid @enderror" 
                                      placeholder="e.g. Primary morning machining and visitor escorting period">{{ old('description', $shift->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status Toggle -->
                        <div class="col-md-12">
                            <div class="p-3 rounded-3 bg-light border border-slate-200">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold text-dark">Active Shift Schedule</div>
                                        <div class="small text-muted">Active shifts are available for assignment and reporting.</div>
                                    </div>
                                    <div class="form-check form-switch fs-5 m-0">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="shift_act" @checked(old('is_active', $shift->is_active ?? true))>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $shift->exists ? 'Save Changes' : 'Register Shift' }}
                        </button>
                        <a href="{{ route('shifts.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Help & Context Column -->
    <div class="col-lg-4">
        <!-- Standard Shibaura Machine Shifts Card -->
        <div class="card-modern mb-4">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <span>Plant Shift Standards</span>
                </div>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    Standard 24-hour continuous 3-shift pattern implemented across Shibaura Machine manufacturing plants:
                </p>

                <div class="timeline-shift-list d-flex flex-column gap-3">
                    <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">Shift A</span>
                            <span class="badge-modern badge-cyan py-0 px-2">Morning</span>
                        </div>
                        <div class="small font-monospace text-primary fw-semibold">06:00 – 14:00 (8 hrs)</div>
                        <div class="small text-muted mt-1">Main production machining, customer plant tours, demo trials.</div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border-start border-4 border-warning">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">Shift B</span>
                            <span class="badge-modern badge-amber py-0 px-2">Afternoon / Evening</span>
                        </div>
                        <div class="small font-monospace text-dark fw-semibold">14:00 – 22:00 (8 hrs)</div>
                        <div class="small text-muted mt-1">Afternoon manufacturing line operations and machine assembly.</div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border-start border-4 border-indigo">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">Shift C</span>
                            <span class="badge-modern badge-indigo py-0 px-2">Night / Overnight</span>
                        </div>
                        <div class="small font-monospace text-dark fw-semibold">22:00 – 06:00 (8 hrs)</div>
                        <div class="small text-muted mt-1">Night operations, unmanned machining runs, scheduled maintenance.</div>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small mt-4 mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-moon-stars text-info fs-5"></i>
                    <div>
                        <strong>Overnight Shifts:</strong> When End Time is earlier than Start Time, the system automatically marks it as crossing midnight.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('shift_start_time');
    const endInput = document.getElementById('shift_end_time');
    const codeInput = document.getElementById('shift_code');
    const nameInput = document.getElementById('shift_name');
    const descInput = document.getElementById('shift_description');
    const start12h = document.getElementById('start_12h_preview');
    const end12h = document.getElementById('end_12h_preview');
    const rangeText = document.getElementById('summaryRangeText');
    const durationBadge = document.getElementById('summaryDurationBadge');
    const typeBadge = document.getElementById('summaryTypeBadge');
    const iconWrapper = document.getElementById('summaryIcon');

    function format12h(time24) {
        if (!time24) return '—';
        const parts = time24.split(':');
        let hours = parseInt(parts[0], 10);
        const mins = parts[1] || '00';
        if (isNaN(hours)) return '—';
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // '0' becomes '12'
        const paddedHours = hours < 10 ? '0' + hours : hours;
        return `${paddedHours}:${mins} ${ampm}`;
    }

    function updateCalculations() {
        const s = startInput.value;
        const e = endInput.value;

        if (start12h) start12h.textContent = s ? `12-Hour format: ${format12h(s)}` : '12-Hour format preview';
        if (end12h) end12h.textContent = e ? `12-Hour format: ${format12h(e)}` : '12-Hour format preview';

        if (!s || !e) return;

        const [sH, sM] = s.split(':').map(Number);
        const [eH, eM] = e.split(':').map(Number);

        let startMinutes = sH * 60 + sM;
        let endMinutes = eH * 60 + eM;
        let isOvernight = false;

        if (endMinutes <= startMinutes) {
            endMinutes += 24 * 60; // Next day
            isOvernight = true;
        }

        const diffMinutes = endMinutes - startMinutes;
        const diffHours = Math.floor(diffMinutes / 60);
        const remainingMinutes = diffMinutes % 60;

        let durationStr = `${diffHours} hrs`;
        if (remainingMinutes > 0) {
            durationStr = `${diffHours}h ${remainingMinutes}m`;
        }

        if (rangeText) {
            rangeText.textContent = `${s} – ${e} (${format12h(s)} – ${format12h(e)})`;
        }

        if (durationBadge) {
            durationBadge.textContent = durationStr;
        }

        if (typeBadge) {
            if (isOvernight) {
                typeBadge.className = 'badge-modern badge-indigo';
                typeBadge.innerHTML = '<i class="bi bi-moon-stars-fill me-1"></i> Overnight Shift';
                if (iconWrapper) {
                    iconWrapper.className = 'rounded-circle p-2 bg-indigo-subtle text-indigo fs-4';
                    iconWrapper.innerHTML = '<i class="bi bi-moon-stars"></i>';
                }
            } else {
                typeBadge.className = 'badge-modern badge-cyan';
                typeBadge.innerHTML = '<i class="bi bi-sun-fill me-1"></i> Day Shift';
                if (iconWrapper) {
                    iconWrapper.className = 'rounded-circle p-2 bg-primary-subtle text-primary fs-4';
                    iconWrapper.innerHTML = '<i class="bi bi-sun"></i>';
                }
            }
        }
    }

    if (startInput) startInput.addEventListener('input', updateCalculations);
    if (endInput) endInput.addEventListener('input', updateCalculations);
    updateCalculations();

    // Preset buttons
    document.querySelectorAll('.shift-preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (codeInput) codeInput.value = this.dataset.code;
            if (nameInput) nameInput.value = this.dataset.name;
            if (startInput) startInput.value = this.dataset.start;
            if (endInput) endInput.value = this.dataset.end;
            if (descInput) descInput.value = this.dataset.desc;
            updateCalculations();
        });
    });

    // Character counter for description
    if (descInput) {
        const counter = document.querySelector('.char-count[data-target="shift_description"]');
        function updateCharCount() {
            if (counter) counter.textContent = `${descInput.value.length} / 1000`;
        }
        descInput.addEventListener('input', updateCharCount);
        updateCharCount();
    }
});
</script>
@endpush
