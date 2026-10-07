@extends('layouts.app')
@section('title', 'Register Plant Visitor')
@section('page_title', 'Register Plant Visitor')
@section('page_subtitle', 'Record a new visitor arrival for the current operational shift')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('dashboard') }}" class="btn-modern-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="card-modern">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title">
                    <i class="bi bi-person-plus-fill text-primary"></i>
                    <span>Visitor Registration & Shift Assignment</span>
                </div>
                @if($currentShift)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-clock-history"></i> Current Shift: {{ $currentShift->name }} ({{ $currentShift->formatted_24h_range }})
                    </span>
                @endif
            </div>

            <div class="card-body p-4">
                <form action="{{ route('visits.store') }}" method="POST">
                    @csrf

                    <!-- Operational Shift Selection -->
                    <div class="p-3 mb-4 rounded-3 border bg-light">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="bi bi-clock-fill text-primary me-1"></i> Operational Duty Shift <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            @foreach($shifts as $s)
                                @php
                                    $isSelected = old('shift_id', $currentShift?->id) == $s->id;
                                @endphp
                                <div class="col-12 col-md-4">
                                    <label class="d-block p-2 rounded border cursor-pointer {{ $isSelected ? 'bg-white border-primary shadow-sm' : 'bg-white' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="radio" name="shift_id" value="{{ $s->id }}" {{ $isSelected ? 'checked' : '' }} required>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $s->name }}</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ $s->formatted_24h_range }}</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('shift_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Visitor Identity Details -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                Visitor Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="visitor_name" class="form-control @error('visitor_name') is-invalid @enderror" 
                                   value="{{ old('visitor_name') }}" placeholder="e.g. Rajesh Kumar" required>
                            @error('visitor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                Company / Organization <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="visitor_company" class="form-control @error('visitor_company') is-invalid @enderror" 
                                   value="{{ old('visitor_company') }}" placeholder="e.g. TVS Motor Company Ltd" required>
                            @error('visitor_company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Designation / Role</label>
                            <input type="text" name="visitor_designation" class="form-control @error('visitor_designation') is-invalid @enderror" 
                                   value="{{ old('visitor_designation') }}" placeholder="e.g. Plant Head / Tooling Lead">
                            @error('visitor_designation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Mobile Contact Number</label>
                            <input type="text" name="visitor_mobile" class="form-control @error('visitor_mobile') is-invalid @enderror" 
                                   value="{{ old('visitor_mobile') }}" placeholder="10-digit mobile (e.g. 9840112233)" maxlength="10">
                            @error('visitor_mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Email Address</label>
                            <input type="email" name="visitor_email" class="form-control @error('visitor_email') is-invalid @enderror" 
                                   value="{{ old('visitor_email') }}" placeholder="visitor@company.com">
                            @error('visitor_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Visit Date</label>
                            <input type="date" name="visit_date" class="form-control" value="{{ old('visit_date', today()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Purpose of Visit</label>
                        <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror" 
                               value="{{ old('purpose', 'Plant Tour & Technical Demo') }}" placeholder="e.g. Injection Molding Trial, Die Casting Witness">
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Register Plant Visitor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
