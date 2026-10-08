@extends('layouts.app')
@section('title', 'Submit Feedback on Behalf — ' . $visit->visitor_name)
@section('page_title', 'Submit Feedback on Behalf')
@section('page_subtitle', 'Record plant feedback responses directly on behalf of or alongside visitor ' .
    $visit->visitor_name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="mb-3">
                <a href="{{ route('dashboard') }}" class="btn-modern-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <!-- Visitor & Shift Context Card -->
            <div class="card-modern mb-4" style="border-left: 4px solid var(--shibaura-blue);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 mb-1">
                                <i class="bi bi-clock-history"></i> Awaiting Feedback
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="fw-bold text-dark mb-0">{{ $visit->visitor_name }}</h4>
                                @if ($visit->visitor_code)
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"
                                        style="font-size: 0.75rem;">
                                        <i class="bi bi-qr-code me-1"></i> {{ $visit->visitor_code }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-secondary fw-semibold">{{ $visit->visitor_designation ?: 'Plant Visitor' }} •
                                <span class="text-dark">{{ $visit->visitor_company }}</span></div>
                        </div>
                        <div class="text-end">
                            @if ($visit->shift)
                                @php
                                    $shiftBadgeColor = match ($visit->shift->code) {
                                        'SHIFT-A' => 'badge-indigo',
                                        'SHIFT-B' => 'badge-amber',
                                        'SHIFT-C' => 'badge-purple',
                                        default => 'badge-slate',
                                    };
                                @endphp
                                <span class="badge-modern {{ $shiftBadgeColor }} fs-6 px-3 py-1">
                                    <i class="bi bi-clock"></i> {{ $visit->shift->name }}
                                    ({{ $visit->shift->formatted_24h_range }})
                                </span>
                            @endif
                            <div class="small text-muted mt-1">Visit Date: {{ $visit->visit_date->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-sm-4">
                            <span class="text-muted d-block">Contact Phone:</span>
                            <span class="fw-bold text-dark"><i class="bi bi-telephone text-muted me-1"></i>
                                {{ $visit->visitor_mobile ?: '—' }}</span>
                        </div>
                        <div class="col-sm-4">
                            <span class="text-muted d-block">Tour Purpose:</span>
                            <span class="fw-bold text-dark">{{ $visit->purpose ?: 'General Technical Tour' }}</span>
                        </div>
                        <div class="col-sm-4">
                            <span class="text-muted d-block">Recording Staff:</span>
                            <span class="badge bg-primary text-white"><i class="bi bi-shield-check"></i>
                                {{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Submission Form -->
            <form action="{{ route('visits.feedback.store', $visit) }}" method="POST">
                @csrf

                <!-- Section-Wise Questions -->
                @foreach ($sections as $secName => $secQuestions)
                    <div class="card-modern mb-4">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between"
                            style="border-left: 4px solid var(--shibaura-blue);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="stat-icon-bubble shibaura"
                                    style="width: 32px; height: 32px; font-size: 0.9rem;">
                                    <i class="bi bi-card-checklist"></i>
                                </span>
                                <span class="fw-bold text-dark fs-6">{{ $secName }}</span>
                            </div>
                            <span class="badge-modern badge-slate">{{ $secQuestions->count() }} Questions</span>
                        </div>

                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-4">
                                @foreach ($secQuestions as $q)
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                            <label class="form-label fw-bold text-dark mb-0">
                                                {{ $loop->iteration }}. {{ $q->question }}
                                                @if ($q->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            @if ($q->is_required)
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle small py-0 px-2"
                                                    style="font-size: 0.65rem;">Required</span>
                                            @endif
                                        </div>

                                        <input type="hidden"
                                            name="answers[{{ $loop->parent->index * 10 + $loop->index }}][question_id]"
                                            value="{{ $q->id }}">

                                        @if ($q->type === 'rating')
                                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    @php
                                                        $labels = [
                                                            5 => '5 ★ (Excellent / சிறந்தவை)',
                                                            4 => '4 ★ (Good / நன்று)',
                                                            3 => '3 ★ (Average / திருப்திகரம்)',
                                                            2 => '2 ★ (Fair / சுமாரானது)',
                                                            1 => '1 ★ (Poor / போதாது)',
                                                        ];
                                                    @endphp
                                                    <label
                                                        class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-pill px-3 py-1"
                                                        style="font-size: 0.8rem; cursor: pointer;">
                                                        <input type="radio"
                                                            name="answers[{{ $loop->parent->index * 10 + $loop->index }}][answer]"
                                                            value="{{ $i }}"
                                                            {{ old('answers.' . ($loop->parent->index * 10 + $loop->index) . '.answer', 5) == $i ? 'checked' : '' }}
                                                            {{ $q->is_required ? 'required' : '' }}>
                                                        <span>{{ $labels[$i] }}</span>
                                                    </label>
                                                @endfor
                                            </div>
                                        @else
                                            <input type="text"
                                                name="answers[{{ $loop->parent->index * 10 + $loop->index }}][answer]"
                                                class="form-control mt-2" placeholder="Type response here..."
                                                value="{{ old('answers.' . ($loop->parent->index * 10 + $loop->index) . '.answer') }}"
                                                {{ $q->is_required ? 'required' : '' }}>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Overall Evaluation & Comments -->
                <div class="card-modern mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>Overall Experience Rating & Remarks</span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">
                                Overall Tour Satisfaction Rating <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ([5 => '5 ★ High / Exceptional', 4 => '4 ★ Very Good', 3 => '3 ★ Satisfactory', 2 => '2 ★ Needs Improvement', 1 => '1 ★ Unsatisfactory'] as $score => $text)
                                    <label
                                        class="btn btn-outline-primary d-flex align-items-center gap-2 rounded-pill px-3 py-2 cursor-pointer"
                                        style="font-size: 0.85rem;">
                                        <input type="radio" name="overall_rating" value="{{ $score }}"
                                            {{ old('overall_rating', 5) == $score ? 'checked' : '' }} required>
                                        <span class="fw-bold">{{ $text }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('overall_rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">
                                Visitor Comments / Suggestions / Feedback Remarks
                            </label>
                            <textarea name="comments" class="form-control @error('comments') is-invalid @enderror" rows="4"
                                placeholder="Record any specific appreciation, machine trial observations, or customer requests...">{{ old('comments') }}</textarea>
                            @error('comments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">These notes will be archived in the plant audit log and
                                visible in supervisor reports.</div>
                        </div>
                    </div>
                </div>

                <!-- Submission Actions -->
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-white border shadow-sm mb-5">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>Submit Feedback on Behalf</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
