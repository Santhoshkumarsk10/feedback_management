@extends('layouts.app')
@section('title', $question->exists ? 'Edit Question' : 'Add Question')
@section('page_title', $question->exists ? 'Edit Survey Question' : 'Create Survey Question')
@section('page_subtitle', 'Configure question text, section assignment, response type, and options')

@section('content')
<div class="mb-3">
    <a href="{{ route('questions.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Questions List
    </a>
</div>

<div class="row g-4">
    <!-- Left Column: Form Configuration -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-patch-question-fill text-primary"></i>
                    <span>{{ $question->exists ? 'Update Question Configuration' : 'New Question Form' }}</span>
                </div>
                @if($question->exists)
                    <span class="badge-modern badge-shibaura">ID: #{{ $question->id }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $question->exists ? route('questions.update', $question) : route('questions.store') }}" id="questionForm" novalidate>
                    @csrf
                    @if($question->exists)
                        @method('PUT')
                    @endif

                    <!-- Section Assignment -->
                    <div class="mb-3">
                        <label class="form-modern-label">Form Section <span class="text-danger">*</span></label>
                        <input list="sectionList" name="section" id="sectionInput" class="form-control form-control-modern @error('section') is-invalid @enderror" value="{{ old('section', $question->section) }}" placeholder="Select or type section name e.g. Section 2 — Overall Experience" minlength="2" maxlength="150" required>
                        @error('section')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_section"></div>
                        <datalist id="sectionList">
                            <option value="Section 1 — Visit Details & Purpose">
                            <option value="Section 2 — Overall Experience">
                            <option value="Section 3 — Facilities & Activities Experienced">
                            <option value="Section 4 — Technical Centre Facilities Rating">
                            <option value="Section 5 — Impact of Your Visit">
                            <option value="Section 6 — Recommendation">
                            <option value="Section 7 — Customer Feedback & Suggestions">
                        </datalist>
                        <div class="small text-muted mt-1">Questions will be organized and displayed under this section.</div>
                    </div>

                    <!-- Question Text -->
                    <div class="mb-3">
                        <label class="form-modern-label">Question Text <span class="text-danger">*</span></label>
                        <input name="question" id="questionInput" class="form-control form-control-modern @error('question') is-invalid @enderror" value="{{ old('question', $question->question) }}" placeholder="e.g. How would you rate the quality and relevance of the demonstrations?" minlength="3" maxlength="255" required>
                        @error('question')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_question"></div>
                    </div>

                    <!-- Type and Order -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-modern-label">Response Type <span class="text-danger">*</span></label>
                            <select name="type" id="questionTypeSelect" class="form-select form-select-modern @error('type') is-invalid @enderror">
                                @foreach(['rating' => '⭐ Rating Scale (1 to 5 Stars)', 'mcq' => '🔘 Multiple Choice (Options)', 'text' => '📝 Free Text (Open comments)'] as $k => $l)
                                    <option value="{{ $k }}" @selected(old('type', $question->type) === $k)>{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label">Display Order / Sequence</label>
                            <input type="number" min="0" max="9999" name="sort_order" class="form-control form-control-modern @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $question->sort_order ?? 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="small text-muted mt-1">Lower numbers appear first within the section.</div>
                        </div>
                    </div>

                    <!-- Options Box -->
                    <div class="mb-4" id="optionsBox">
                        <label class="form-modern-label">
                            Multiple Choice Options
                            <small class="text-muted fw-normal">(One answer option per line)</small>
                        </label>
                        <textarea name="options" id="optionsInput" rows="4" class="form-control form-control-modern @error('options') is-invalid @enderror" placeholder="Definitely&#10;To some extent&#10;Not really&#10;Not applicable" maxlength="2000">{{ old('options', implode("\n", $question->options ?? [])) }}</textarea>
                        @error('options')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none custom-live-err" id="live_err_options"></div>
                        <div class="small text-muted mt-1">Only required when response type is set to Multiple Choice.</div>
                    </div>

                    <!-- Toggles for Required and Active -->
                    <div class="row g-3 p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Mandatory Answer</div>
                                    <div class="small text-muted">Customer must answer before submitting</div>
                                </div>
                                <div class="form-check form-switch fs-5 m-0">
                                    <input type="hidden" name="is_required" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_required" value="1" id="req" @checked(old('is_required', $question->is_required ?? true))>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Active Status</div>
                                    <div class="small text-muted">Enable this question in the feedback form</div>
                                </div>
                                <div class="form-check form-switch fs-5 m-0">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $question->is_active ?? true))>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $question->exists ? 'Save Changes' : 'Create Question' }}
                        </button>
                        <a href="{{ route('questions.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Visitor Preview & Section Guide -->
    <div class="col-lg-4">
        <!-- Live Preview Card -->
        <div class="card-modern mb-4">
            <div class="card-header bg-white">
                <div class="card-title text-primary">
                    <i class="bi bi-eye-fill"></i>
                    <span>Live Visitor Preview</span>
                </div>
                <span class="badge-modern badge-shibaura">Client View</span>
            </div>
            <div class="card-body p-4 bg-light">
                <div class="p-3 bg-white rounded-3 border shadow-xs">
                    <div class="small fw-bold text-muted text-uppercase mb-1" id="previewSection" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        {{ $question->section ?: 'Section 2 — Overall Experience' }}
                    </div>
                    <div class="fw-bold text-dark fs-6 mb-3" id="previewQuestion">
                        {{ $question->question ?: 'Question text preview will appear here...' }}
                    </div>

                    <!-- Dynamic Preview Control -->
                    <div id="previewControls">
                        <!-- Rating preview -->
                        <div id="previewRating" class="d-flex gap-2 text-warning fs-4 mb-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star text-muted"></i>
                        </div>
                        <div class="small text-muted" id="previewScaleLabel">1 (Very Poor) to 5 (Excellent)</div>

                        <!-- MCQ Preview -->
                        <div id="previewMcq" class="d-none">
                            <div class="d-flex flex-column gap-2" id="previewOptionsList"></div>
                        </div>

                        <!-- Text Preview -->
                        <div id="previewText" class="d-none">
                            <textarea class="form-control form-control-sm bg-light" rows="3" placeholder="Visitor types feedback remarks here..." disabled></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections Reference Card -->
        <div class="card-modern">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-diagram-3-fill text-primary"></i>
                    <span>Form Sections Reference</span>
                </div>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-2" style="font-size: 0.8rem;">
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 1:</strong> Visit Details & Purpose
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 2:</strong> Overall Experience (1-5★)
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 3:</strong> Facilities & Activities (Demo/Trial/VR)
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 4:</strong> Technical Centre Facilities (1-5★)
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 5:</strong> Impact of Your Visit
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 6:</strong> Recommendation
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <strong>Sec 7:</strong> Customer Feedback & Suggestions
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
    const form = document.getElementById('questionForm');
    const typeSelect = document.getElementById('questionTypeSelect');
    const optionsBox = document.getElementById('optionsBox');
    const optionsInput = document.getElementById('optionsInput');
    const questionInput = document.getElementById('questionInput');
    const sectionInput = document.getElementById('sectionInput');

    const previewSection = document.getElementById('previewSection');
    const previewQuestion = document.getElementById('previewQuestion');
    const previewRating = document.getElementById('previewRating');
    const previewScaleLabel = document.getElementById('previewScaleLabel');
    const previewMcq = document.getElementById('previewMcq');
    const previewOptionsList = document.getElementById('previewOptionsList');
    const previewText = document.getElementById('previewText');

    function showLiveErr(id, msg, persistent = false) {
        const errEl = document.getElementById('live_err_' + id);
        const input = id === 'section' ? sectionInput : (id === 'question' ? questionInput : optionsInput);
        if (input) input.classList.add('is-invalid');
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.remove('d-none');
            errEl.classList.add('d-block');
            clearTimeout(errEl._timer);
            if (!persistent) {
                errEl._timer = setTimeout(() => {
                    errEl.classList.remove('d-block');
                    errEl.classList.add('d-none');
                }, 2500);
            }
        }
    }

    function clearLiveErr(id) {
        const errEl = document.getElementById('live_err_' + id);
        const input = id === 'section' ? sectionInput : (id === 'question' ? questionInput : optionsInput);
        if (input) input.classList.remove('is-invalid');
        if (errEl) {
            errEl.classList.remove('d-block');
            errEl.classList.add('d-none');
        }
    }

    const forbiddenCharRegex = /[<>{}\[\]$^*~=\\\|]/;
    const disallowedChars = /[<>{}\[\]$^*~=\\\|]/g;

    function bindQuestionFieldInterceptors(input, id, minLen, maxLen, msg) {
        if (!input) return;

        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (forbiddenCharRegex.test(e.key)) {
                    e.preventDefault();
                    showLiveErr(id, msg);
                    return;
                }
                if (maxLen && this.value.length >= maxLen && this.selectionStart === this.selectionEnd) {
                    e.preventDefault();
                    return;
                }
            }
        });

        input.addEventListener('beforeinput', function(e) {
            if (e.data) {
                for (let i = 0; i < e.data.length; i++) {
                    if (forbiddenCharRegex.test(e.data[i])) {
                        e.preventDefault();
                        showLiveErr(id, msg);
                        return;
                    }
                }
            }
        });

        input.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && disallowedChars.test(text)) {
                e.preventDefault();
                let cleaned = text.replace(disallowedChars, '');
                if (maxLen) {
                    const avail = maxLen - (this.value.length - (this.selectionEnd - this.selectionStart));
                    if (avail > 0) cleaned = cleaned.slice(0, avail);
                    else cleaned = '';
                }
                document.execCommand('insertText', false, cleaned);
            }
        });

        input.addEventListener('input', function() {
            if (disallowedChars.test(this.value)) {
                this.value = this.value.replace(disallowedChars, '');
                showLiveErr(id, msg);
            } else if (this.value.trim().length >= minLen) {
                clearLiveErr(id);
            }
            if (maxLen && this.value.length > maxLen) {
                this.value = this.value.slice(0, maxLen);
            }
        });
    }

    bindQuestionFieldInterceptors(sectionInput, 'section', 2, 50, 'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.');
    bindQuestionFieldInterceptors(questionInput, 'question', 3, 255, 'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.');
    bindQuestionFieldInterceptors(optionsInput, 'options', 2, 1000, 'Tags and symbols like < > { } [ ] $ ^ * = \\ | are not allowed.');

    optionsInput.addEventListener('input', function() {
        const lines = (this.value || '').split('\n').map(l => l.trim()).filter(l => l.length > 0);
        if (lines.length >= 2) {
            clearLiveErr('options');
        }
    });

    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            let firstInvalid = null;

            const sVal = sectionInput.value.trim();
            if (!sVal) {
                hasError = true;
                showLiveErr('section', 'Form Section is required.', true);
                if (!firstInvalid) firstInvalid = sectionInput;
            } else if (sVal.length < 2) {
                hasError = true;
                showLiveErr('section', 'Form Section must be at least 2 characters.', true);
                if (!firstInvalid) firstInvalid = sectionInput;
            }

            const qVal = questionInput.value.trim();
            if (!qVal) {
                hasError = true;
                showLiveErr('question', 'Question Text is required.', true);
                if (!firstInvalid) firstInvalid = questionInput;
            } else if (qVal.length < 3) {
                hasError = true;
                showLiveErr('question', 'Question Text must be at least 3 characters.', true);
                if (!firstInvalid) firstInvalid = questionInput;
            }

            if (typeSelect.value === 'mcq') {
                const lines = (optionsInput.value || '').split('\n').map(l => l.trim()).filter(l => l.length > 0);
                if (lines.length < 2) {
                    hasError = true;
                    showLiveErr('options', 'Please enter at least 2 Multiple Choice options (one per line).', true);
                    if (!firstInvalid) firstInvalid = optionsInput;
                }
            }

            if (hasError) {
                e.preventDefault();
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    function updatePreview() {
        const type = typeSelect.value;
        const qText = questionInput.value.trim();
        const sText = sectionInput.value.trim();

        previewQuestion.textContent = qText || 'Question text preview will appear here...';
        previewSection.textContent = sText || 'Form Section';

        if (type === 'rating') {
            optionsBox.classList.add('d-none');
            previewRating.classList.remove('d-none');
            previewScaleLabel.classList.remove('d-none');
            previewMcq.classList.add('d-none');
            previewText.classList.add('d-none');
            clearLiveErr('options');
        } else if (type === 'mcq') {
            optionsBox.classList.remove('d-none');
            previewRating.classList.add('d-none');
            previewScaleLabel.classList.add('d-none');
            previewMcq.classList.remove('d-none');
            previewText.classList.add('d-none');

            const lines = (optionsInput.value || '').split('\n').map(l => l.trim()).filter(l => l.length > 0);
            previewOptionsList.innerHTML = '';
            if (lines.length === 0) {
                previewOptionsList.innerHTML = '<span class="text-muted small fst-italic">Enter options on the left...</span>';
            } else {
                lines.forEach((opt, idx) => {
                    const div = document.createElement('div');
                    div.className = 'form-check small';
                    div.innerHTML = `<input class="form-check-input" type="radio" name="previewRadio" id="pr_${idx}" ${idx === 0 ? 'checked' : ''} disabled><label class="form-check-label text-dark" for="pr_${idx}">${opt}</label>`;
                    previewOptionsList.appendChild(div);
                });
            }
        } else {
            optionsBox.classList.add('d-none');
            previewRating.classList.add('d-none');
            previewScaleLabel.classList.add('d-none');
            previewMcq.classList.add('d-none');
            previewText.classList.remove('d-none');
            clearLiveErr('options');
        }
    }

    typeSelect.addEventListener('change', updatePreview);
    questionInput.addEventListener('input', updatePreview);
    sectionInput.addEventListener('input', updatePreview);
    optionsInput.addEventListener('input', updatePreview);

    updatePreview();
});
</script>
@endpush
