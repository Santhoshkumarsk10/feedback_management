@extends('layouts.app')
@section('title', 'Feedback Detail #'.$feedback->id)
@section('page_title', 'Tour Feedback Audit')
@section('page_subtitle', 'Detailed response breakdown submitted by '.$feedback->visit->visitor_name)

@section('content')
<div class="mb-3">
    <a href="{{ route('feedbacks.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Feedback Submissions
    </a>
</div>

<!-- Executive Summary Hero Card -->
<div class="card-modern mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <!-- Overall Rating Hero Score -->
            <div class="col-lg-3 text-center border-end-lg">
                <div class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.05em;">Overall Rating</div>
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <span class="display-4 fw-bold text-dark" style="font-family: var(--font-heading); line-height: 1;">
                        {{ number_format($feedback->overall_rating, 1) }}
                    </span>
                    <span class="fs-4 text-warning">★</span>
                </div>
                <div class="mb-2">
                    @include('partials.rating', ['value' => $feedback->overall_rating])
                </div>
                <div class="small text-muted">
                    Submitted on {{ $feedback->submitted_at->format('d M Y, h:i A') }}
                </div>
            </div>

            <!-- Visitor & Tour Details -->
            <div class="col-lg-9 ps-lg-4">
                <div class="row g-3">
                    <!-- Visitor Info -->
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200 h-100">
                            <div class="small text-muted fw-semibold mb-1">
                                <i class="bi bi-person-fill text-primary"></i> Visitor
                            </div>
                            <div class="fw-bold text-dark fs-6">{{ $feedback->visit->visitor_name }}</div>
                            @if($feedback->visit->visitor_designation)
                                <div class="small text-secondary fw-semibold">{{ $feedback->visit->visitor_designation }}</div>
                            @endif
                            @if($feedback->visit->visitor_company)
                                <div class="small text-muted">{{ $feedback->visit->visitor_company }}</div>
                            @endif
                            @if($feedback->visit->visitor_mobile)
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-telephone"></i> {{ $feedback->visit->visitor_mobile }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Organizer Info -->
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200 h-100">
                            <div class="small text-muted fw-semibold mb-1">
                                <i class="bi bi-shield-check text-primary"></i> Tour Organizer
                            </div>
                            <div class="fw-bold text-dark fs-6">{{ $feedback->organizer->name }}</div>
                            @if($feedback->organizer->department)
                                <div class="small text-muted">{{ $feedback->organizer->department }}</div>
                            @endif
                            <div class="small text-muted mt-1">
                                <i class="bi bi-envelope"></i> {{ $feedback->organizer->email ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- Visit Info -->
                    <div class="col-sm-12 col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200 h-100">
                            <div class="small text-muted fw-semibold mb-1">
                                <i class="bi bi-calendar-event text-primary"></i> Visit Details
                            </div>
                            <div class="fw-bold text-dark fs-6">{{ $feedback->visit->visit_date->format('d M Y') }}</div>
                            <div class="small text-muted mt-1">
                                <strong>Purpose:</strong> {{ $feedback->visit->purpose ?? 'General Plant Tour' }}
                            </div>
                            @if($feedback->visit->shift)
                                <div class="mt-2">
                                    <span class="badge-modern badge-indigo">
                                        <i class="bi bi-clock"></i> {{ $feedback->visit->shift->name }} ({{ $feedback->visit->shift->formatted_24h_range }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($feedback->comments)
                    <div class="mt-3 p-3 rounded-3 bg-light border" style="border-left: 4px solid var(--shibaura-blue) !important;">
                        <div class="small fw-bold text-primary mb-1">
                            <i class="bi bi-chat-quote-fill me-1"></i> Visitor Remarks & Feedback:
                        </div>
                        <p class="mb-0 text-slate-800 fst-italic">“{{ $feedback->comments }}”</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Section-Wise Grouped Survey Responses -->
@php
    $groupedAnswers = $feedback->answers->groupBy(fn($a) => $a->question->section ?: 'General Questionnaire');
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
        <i class="bi bi-ui-checks text-primary me-1"></i> Section-Wise Survey Breakdown
    </h5>
    <span class="badge-modern badge-shibaura">{{ count($feedback->answers) }} Responses</span>
</div>

@forelse($groupedAnswers as $sectionName => $answers)
    <div class="card-modern mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="d-flex align-items-center gap-2">
                <span class="stat-icon-bubble shibaura" style="width: 32px; height: 32px; font-size: 0.95rem;">
                    <i class="bi bi-check2-circle"></i>
                </span>
                <span class="fw-bold text-dark fs-6">{{ $sectionName }}</span>
            </div>
            <span class="badge-modern badge-shibaura">{{ $answers->count() }} Answers</span>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 55px;">#</th>
                        <th style="width: 55%;">Question</th>
                        <th>Type</th>
                        <th class="text-end">Visitor's Response</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($answers as $idx => $a)
                    <tr>
                        <td>
                            <span class="badge-modern badge-slate fw-bold">#{{ $a->question->sort_order ?? ($idx + 1) }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark fs-6">{{ $a->question->question }}</div>
                        </td>
                        <td>
                            @if($a->question->type === 'rating')
                                <span class="badge-modern badge-amber"><i class="bi bi-star-fill"></i> Rating</span>
                            @elseif($a->question->type === 'mcq')
                                <span class="badge-modern badge-shibaura"><i class="bi bi-ui-checks"></i> Choice</span>
                            @else
                                <span class="badge-modern badge-indigo"><i class="bi bi-card-text"></i> Text</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($a->question->type === 'rating')
                                @include('partials.rating', ['value' => $a->answer])
                            @elseif($a->answer)
                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 fw-semibold">
                                    {{ $a->answer }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card-modern p-4 text-center text-muted">
        No answers recorded for this feedback.
    </div>
@endforelse
@endsection
