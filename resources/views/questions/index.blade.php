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
@endphp

<!-- Section Quick-Jump Tabs -->
<div class="filter-card-wrapper mb-4">
    <div class="filter-tabs-header">
        <div class="filter-tabs-nav">
            <span class="small fw-bold text-muted text-uppercase me-2" style="letter-spacing: 0.05em;">
                <i class="bi bi-folder2-open text-primary me-1"></i> Form Sections:
            </span>
            @foreach($grouped as $secTitle => $secItems)
                <a href="#section-{{ Str::slug($secTitle) }}" class="filter-tab-btn">
                    <span>{{ Str::limit($secTitle, 30) }}</span>
                    <span class="filter-tab-badge">{{ $secItems->count() }}</span>
                </a>
            @endforeach
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge-modern badge-slate">{{ $questions->count() }} Total Questions</span>
        </div>
    </div>
</div>

<!-- Section-Wise Grouped Question Cards -->
@forelse($grouped as $sectionTitle => $sectionQuestions)
    <div class="card-modern mb-4" id="section-{{ Str::slug($sectionTitle) }}">
        <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="d-flex align-items-center gap-2">
                <span class="stat-icon-bubble shibaura" style="width: 34px; height: 34px; font-size: 1rem;">
                    <i class="bi bi-ui-checks-grid"></i>
                </span>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">{{ $sectionTitle }}</h6>
                    <span class="text-muted small" style="font-size: 0.73rem;">{{ $sectionQuestions->count() }} questions in this section</span>
                </div>
            </div>
            <span class="badge-modern badge-shibaura">Section {{ Str::after(Str::before($sectionTitle, '—'), 'Section ') }}</span>
        </div>

        <div class="table-responsive">
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
@endsection
