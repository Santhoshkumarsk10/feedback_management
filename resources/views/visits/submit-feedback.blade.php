@extends(auth()->check() ? 'layouts.app' : 'layouts.empty')
@section('title', 'Submit Feedback — ' . $visit->visitor_name)
@section('page_title', 'Submit Feedback')
@section('page_subtitle', 'Record plant feedback responses for visitor ' . $visit->visitor_name)

@section('content')
    @if(!auth()->check())
        <div class="py-3 px-3 mb-4 bg-white border-bottom shadow-sm">
            <div class="container d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/shibaura-logo-crisp.webp') }}" height="36" alt="Shibaura Machine" onerror="this.src='{{ asset('images/logo.png') }}'">
                    <div>
                        <span class="fw-bold fs-6 text-dark d-block">Shibaura Machine India</span>
                        <span class="text-muted small" style="font-size: 0.75rem;">Plant Experience &amp; Visitor Feedback</span>
                    </div>
                </div>
                <a href="{{ route('visitor.home') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-house me-1"></i> Visitor Home
                </a>
            </div>
        </div>
    @endif

    <div class="container py-2">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <div class="mb-3">
                    <a href="{{ auth()->check() ? route('dashboard') : route('visitor.home') }}" class="btn-modern-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> {{ auth()->check() ? 'Back to Dashboard' : 'Back to Visitor Feedback Home' }}
                    </a>
                </div>

            @if ($visit->is_feedback_expired)
                <div class="alert-modern danger mb-4 d-flex align-items-center gap-3">
                    <i class="bi bi-x-octagon-fill fs-3 text-danger"></i>
                    <div>
                        <div class="fw-bold">Feedback Collection Expired</div>
                        <div class="small">The visit day ({{ $visit->visit_date->format('d/m/Y') }}) has ended. As per policy, feedback can only be submitted on the day of the visit and cannot be collected on subsequent days.</div>
                    </div>
                </div>
            @endif

            <!-- Visitor Context Card -->
            <div class="card-modern mb-4" style="border-left: 4px solid var(--shibaura-blue);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            @if ($visit->is_feedback_expired)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 mb-1">
                                    <i class="bi bi-clock-history"></i> Feedback Expired (Day Ended)
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 mb-1">
                                    <i class="bi bi-clock-history"></i> Awaiting Feedback
                                </span>
                            @endif
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
                            <div class="text-secondary fw-semibold">{{ $visit->visitor_designation ?: 'Visitor' }} •
                                <span class="text-dark">{{ $visit->visitor_company }}</span></div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-light text-dark border px-3 py-1 fs-6">
                                <i class="bi bi-calendar3 me-1"></i> {{ $visit->visit_date->format('d M Y') }}
                            </div>
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
                            <span class="text-muted d-block">Recording Staff / Mode:</span>
                            <span class="badge bg-primary text-white"><i class="bi bi-shield-check"></i>
                                {{ auth()->check() ? auth()->user()->name : 'Visitor Direct Self-Service' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Submission Form -->
            <form action="{{ route('visits.feedback.store', $visit) }}" method="POST" id="feedbackSubmitForm" novalidate>
                @csrf

                <!-- Manufacturing Plant & Tour Organizer Selection -->
                <div class="card-modern mb-4" style="border-left: 4px solid var(--shibaura-blue);">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stat-icon-bubble shibaura" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <i class="bi bi-buildings-fill"></i>
                            </span>
                            <span class="fw-bold text-dark fs-6">Facility & Organizer Selection</span>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small px-2 py-1">Mandatory Fields</span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark" for="feedback_plant_id">
                                    Manufacturing Plant / Facility <span class="text-danger">*</span>
                                </label>
                                <x-custom-select 
                                    name="plant_id"
                                    id="feedback_plant_id"
                                    :options="$plants->map(fn($p) => [
                                        'value' => (string)$p->id,
                                        'label' => $p->name,
                                        'code' => $p->code,
                                        'sub' => $p->location,
                                    ])"
                                    :value="(string) old('plant_id', $visit->plant_id ?? ($plants->first()->id ?? ''))"
                                    placeholder="— Select Evaluated Plant Unit —"
                                    search-placeholder="Search plant facility or code..."
                                    icon="bi-buildings"
                                    :auto-submit="false"
                                    :allow-empty="false"
                                    min-width="100%"
                                />
                                <div id="plant_id_client_error" class="validation-feedback-box error d-none mt-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>Please select the manufacturing plant facility.</span>
                                </div>
                                @error('plant_id')
                                    <div class="validation-feedback-box error mt-1">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                                <div class="small text-muted mt-1">Specify which factory division or technical centre is being evaluated.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark" for="feedback_organizer_id">
                                    Host Tour Organizer / Staff <span class="text-danger">*</span>
                                </label>
                                <x-custom-select 
                                    name="organizer_id"
                                    id="feedback_organizer_id"
                                    :options="$organizers->map(fn($o) => [
                                        'value' => (string)$o->id,
                                        'label' => $o->name,
                                        'sub' => $o->department ? $o->department . ' • ' . ucfirst($o->role ?? 'Staff') : ucfirst($o->role ?? 'Staff'),
                                    ])"
                                    :value="(string) old('organizer_id', $visit->organizer_id ?? '')"
                                    placeholder="— Select Host Organizer —"
                                    search-placeholder="Search staff or organizer name..."
                                    icon="bi-person-badge"
                                    :auto-submit="false"
                                    :allow-empty="true"
                                    min-width="100%"
                                />
                                <div id="organizer_id_client_error" class="validation-feedback-box error d-none mt-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>Please select the host tour organizer.</span>
                                </div>
                                @error('organizer_id')
                                    <div class="validation-feedback-box error mt-1">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                                <div class="small text-muted mt-1">The staff escort/organizer assigned to this visitor experience.</div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <div class="p-3 rounded-3 bg-light border question-card-item {{ $q->is_required ? 'is-required-question' : '' }}" 
                                         id="question_card_{{ $q->id }}"
                                         data-question-id="{{ $q->id }}"
                                         data-required="{{ $q->is_required ? 'true' : 'false' }}">
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
                                                            5 => '5 ★ (Excellent)',
                                                            4 => '4 ★ (Good)',
                                                            3 => '3 ★ (Average)',
                                                            2 => '2 ★ (Fair)',
                                                            1 => '1 ★ (Poor)',
                                                        ];
                                                    @endphp
                                                    <label
                                                        class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-pill px-3 py-1"
                                                        style="font-size: 0.8rem; cursor: pointer;">
                                                        <input type="radio"
                                                            name="answers[{{ $loop->parent->index * 10 + $loop->index }}][answer]"
                                                            value="{{ $i }}"
                                                            {{ old('answers.' . ($loop->parent->index * 10 + $loop->index) . '.answer') == $i ? 'checked' : '' }}
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
                                                maxlength="500"
                                                {{ $q->is_required ? 'required' : '' }}>
                                        @endif

                                        <div class="question-error-box validation-feedback-box error d-none mt-2">
                                            <i class="bi bi-exclamation-circle-fill"></i>
                                            <span>Please provide an evaluation for this mandatory question.</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Overall Evaluation & Comments -->
                <div class="card-modern mb-4" id="overall_rating_card">
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
                            <div id="overall_rating_client_error" class="validation-feedback-box error d-none mt-2">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>Please select an overall tour satisfaction rating.</span>
                            </div>
                            @error('overall_rating')
                                <div class="validation-feedback-box error mt-2">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark mb-0">
                                    Visitor Comments / Suggestions / Feedback Remarks
                                </label>
                                <span id="commentsCharCount" class="small text-muted" style="font-size: 0.75rem;">{{ mb_strlen(old('comments', '')) }}/2000</span>
                            </div>
                            <textarea name="comments" id="commentsTextarea" class="form-control @error('comments') is-invalid @enderror" rows="4"
                                maxlength="2000"
                                placeholder="Record any specific appreciation, machine trial observations, or customer requests...">{{ old('comments') }}</textarea>
                            <div id="comments_limit_error" class="validation-feedback-box error d-none mt-1">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>Maximum character limit reached (2000/2000 characters).</span>
                            </div>
                            <div id="comments_char_error" class="validation-feedback-box error d-none mt-1">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>Comments contain unsupported special characters (&lt; &gt; { } [ ] $ ^ * = \ |).</span>
                            </div>
                            @error('comments')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">These notes will be archived in the plant audit log and
                                visible in supervisor reports.</div>
                        </div>
                    </div>
                </div>

                <!-- Submission Actions -->
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-white border shadow-sm mb-5">
                    <a href="{{ auth()->check() ? route('dashboard') : route('visitor.home') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    @if ($visit->is_feedback_expired)
                        <button type="button" class="btn btn-secondary px-4 py-2 fw-bold d-flex align-items-center gap-2 disabled" disabled>
                            <i class="bi bi-lock-fill fs-5"></i>
                            <span>Feedback Expired (Locked)</span>
                        </button>
                    @else
                        <button type="submit" id="submitFeedbackBtn" class="btn btn-primary px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>{{ auth()->check() ? 'Submit Feedback on Behalf' : 'Submit Feedback' }}</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedbackSubmitForm');
    if (!form) return;

    const plantInput = form.querySelector('input[name="plant_id"]');
    const plantTrigger = document.querySelector('#feedback_plant_id_wrapper .custom-select-trigger');
    const plantError = document.getElementById('plant_id_client_error');

    const orgInput = form.querySelector('input[name="organizer_id"]');
    const orgTrigger = document.querySelector('#feedback_organizer_id_wrapper .custom-select-trigger');
    const orgError = document.getElementById('organizer_id_client_error');

    const overallRatingCard = document.getElementById('overall_rating_card');
    const overallRatingError = document.getElementById('overall_rating_client_error');

    // Real-time clearance on plant selection
    if (plantInput) {
        plantInput.addEventListener('change', function() {
            if (this.value.trim()) {
                if (plantTrigger) plantTrigger.classList.remove('is-invalid');
                if (plantError) plantError.classList.add('d-none');
            }
        });
    }

    // Real-time clearance on organizer selection
    if (orgInput) {
        orgInput.addEventListener('change', function() {
            if (this.value.trim()) {
                if (orgTrigger) orgTrigger.classList.remove('is-invalid');
                if (orgError) orgError.classList.add('d-none');
            }
        });
    }

    // Real-time clearance on questions
    document.querySelectorAll('.question-card-item input, .question-card-item textarea').forEach(input => {
        input.addEventListener('change', function() {
            const card = this.closest('.question-card-item');
            if (card) {
                card.classList.remove('border-danger', 'bg-danger-subtle');
                card.classList.add('bg-light');
                const errBox = card.querySelector('.question-error-box');
                if (errBox) errBox.classList.add('d-none');
            }
        });
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                const card = this.closest('.question-card-item');
                if (card) {
                    card.classList.remove('border-danger', 'bg-danger-subtle');
                    card.classList.add('bg-light');
                    const errBox = card.querySelector('.question-error-box');
                    if (errBox) errBox.classList.add('d-none');
                }
            }
        });
    });

    // Real-time clearance on overall rating
    document.querySelectorAll('input[name="overall_rating"]').forEach(input => {
        input.addEventListener('change', function() {
            if (overallRatingError) overallRatingError.classList.add('d-none');
            if (overallRatingCard) overallRatingCard.classList.remove('border-danger');
        });
    });

    // Form submit validation interceptor
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let firstInvalidElement = null;

        // 1. Validate Plant
        if (!plantInput || !plantInput.value.trim()) {
            isValid = false;
            if (plantTrigger) plantTrigger.classList.add('is-invalid');
            if (plantError) plantError.classList.remove('d-none');
            if (!firstInvalidElement) firstInvalidElement = plantTrigger;
        } else {
            if (plantTrigger) plantTrigger.classList.remove('is-invalid');
            if (plantError) plantError.classList.add('d-none');
        }

        // 2. Validate Organizer
        if (!orgInput || !orgInput.value.trim()) {
            isValid = false;
            if (orgTrigger) orgTrigger.classList.add('is-invalid');
            if (orgError) orgError.classList.remove('d-none');
            if (!firstInvalidElement) firstInvalidElement = orgTrigger;
        } else {
            if (orgTrigger) orgTrigger.classList.remove('is-invalid');
            if (orgError) orgError.classList.add('d-none');
        }

        // 3. Validate Mandatory Questions in Sections
        document.querySelectorAll('.question-card-item.is-required-question').forEach(card => {
            const checkedRadio = card.querySelector('input[type="radio"]:checked');
            const textInput = card.querySelector('input[type="text"]');
            const textarea = card.querySelector('textarea');
            const errBox = card.querySelector('.question-error-box');

            let answered = false;
            if (checkedRadio) {
                answered = true;
            } else if (textInput && textInput.value.trim() !== '') {
                answered = true;
            } else if (textarea && textarea.value.trim() !== '') {
                answered = true;
            }

            if (!answered) {
                isValid = false;
                card.classList.add('border-danger', 'bg-danger-subtle');
                card.classList.remove('bg-light');
                if (errBox) errBox.classList.remove('d-none');
                if (!firstInvalidElement) firstInvalidElement = card;
            } else {
                card.classList.remove('border-danger', 'bg-danger-subtle');
                card.classList.add('bg-light');
                if (errBox) errBox.classList.add('d-none');
            }
        });

        // 4. Validate Overall Tour Satisfaction Rating
        const overallRatingChecked = form.querySelector('input[name="overall_rating"]:checked');
        if (!overallRatingChecked) {
            isValid = false;
            if (overallRatingError) overallRatingError.classList.remove('d-none');
            if (overallRatingCard) overallRatingCard.classList.add('border-danger');
            if (!firstInvalidElement) firstInvalidElement = overallRatingCard;
        } else {
            if (overallRatingError) overallRatingError.classList.add('d-none');
            if (overallRatingCard) overallRatingCard.classList.remove('border-danger');
        }

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();

            if (firstInvalidElement) {
                firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (firstInvalidElement.focus) firstInvalidElement.focus();
            }

            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Please fill all mandatory fields marked with an asterisk (*).',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            }
            return false;
        }
    });

    // Real-time Comments Character Counter & Limit/Character Warning
    const commentsArea = document.getElementById('commentsTextarea');
    const commentsCount = document.getElementById('commentsCharCount');
    const commentsLimitErr = document.getElementById('comments_limit_error');
    const commentsCharErr = document.getElementById('comments_char_error');
    const unsafeCommentsRegex = /[<>{}\[\]$^*~=\\\|]/;

    if (commentsArea && commentsCount) {
        function updateCommentsCount() {
            const len = commentsArea.value.length;
            commentsCount.textContent = `${len}/2000`;
            const hasUnsafe = unsafeCommentsRegex.test(commentsArea.value);

            if (hasUnsafe) {
                if (commentsCharErr) commentsCharErr.classList.remove('d-none');
                commentsArea.classList.add('is-invalid');
            } else {
                if (commentsCharErr) commentsCharErr.classList.add('d-none');
                if (len < 2000) commentsArea.classList.remove('is-invalid');
            }

            if (len >= 2000) {
                commentsCount.className = 'small text-danger fw-bold';
                if (commentsLimitErr) commentsLimitErr.classList.remove('d-none');
            } else if (len >= 1800) {
                commentsCount.className = 'small text-warning-emphasis fw-semibold';
                if (commentsLimitErr) commentsLimitErr.classList.add('d-none');
            } else {
                commentsCount.className = 'small text-muted';
                if (commentsLimitErr) commentsLimitErr.classList.add('d-none');
            }
        }
        commentsArea.addEventListener('input', updateCommentsCount);
        commentsArea.addEventListener('keyup', updateCommentsCount);
        commentsArea.addEventListener('paste', () => setTimeout(updateCommentsCount, 10));
        updateCommentsCount();
    }

    if (feedbackForm && commentsArea) {
        feedbackForm.addEventListener('submit', function(e) {
            if (unsafeCommentsRegex.test(commentsArea.value)) {
                e.preventDefault();
                commentsArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                commentsArea.focus();
                if (commentsCharErr) commentsCharErr.classList.remove('d-none');
                return false;
            }
        });
    }
});
</script>
@endpush
