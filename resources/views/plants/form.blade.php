@extends('layouts.app')
@section('title', $plant->exists ? 'Edit Plant: '.$plant->code : 'Register New Plant')
@section('page_title', $plant->exists ? 'Edit Plant Details' : 'Register Manufacturing Facility')
@section('page_subtitle', $plant->exists ? 'Update factory metadata, address, contact, and operation status' : 'Add a new Shibaura Machine manufacturing plant or technical experience facility')

@section('content')
<div class="mb-3">
    <a href="{{ route('plants.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Plant List
    </a>
</div>

<div class="row g-4">
    <!-- Main Plant Form Column -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-buildings-fill text-primary"></i>
                    <span>{{ $plant->exists ? 'Modify Plant Configuration' : 'New Plant Information' }}</span>
                </div>
                @if($plant->exists)
                    <span class="badge-modern badge-shibaura">{{ $plant->code }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $plant->exists ? route('plants.update', $plant) : route('plants.store') }}">
                    @csrf
                    @if($plant->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-modern-label">Plant Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-qr-code"></i></span>
                                <input name="code" class="form-control form-control-modern border-start-0" value="{{ old('code', $plant->code) }}" placeholder="e.g. PLANT-01" required style="text-transform: uppercase;">
                            </div>
                            <div class="small text-muted mt-1">Unique facility identifier</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-modern-label">Facility / Division Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input name="name" class="form-control form-control-modern border-start-0" value="{{ old('name', $plant->name) }}" placeholder="e.g. Plant 1 — Machine Tools Division" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-modern-label">Factory Location / Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-geo-alt"></i></span>
                                <input name="location" class="form-control form-control-modern border-start-0" value="{{ old('location', $plant->location) }}" placeholder="e.g. Chembarambakkam, Chennai, Tamil Nadu - 600123">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Operations Contact Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="contact_email" class="form-control form-control-modern border-start-0" value="{{ old('contact_email', $plant->contact_email) }}" placeholder="plant1.ops@shibaura-machine.in">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Desk / Helpdesk Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input name="contact_phone" class="form-control form-control-modern border-start-0" value="{{ old('contact_phone', $plant->contact_phone) }}" placeholder="+91 44 2681 1201">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-modern-label">Facility Description & Specialization</label>
                            <textarea name="description" rows="3" class="form-control form-control-modern" placeholder="e.g. Machining Centers, Horizontal Boring, Injection Molding Demo Cell, and Customer Experience Zone">{{ old('description', $plant->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold text-dark">Operational Status</div>
                                <div class="small text-muted">Active plants appear in user assignment, visit logs, and feedback reporting.</div>
                            </div>
                            <div class="form-check form-switch fs-5 m-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="plant_act" @checked(old('is_active', $plant->is_active ?? true))>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $plant->exists ? 'Save Changes' : 'Register Facility' }}
                        </button>
                        <a href="{{ route('plants.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Info Sidebar (Col-lg-4) -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>Plant Hierarchy</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon-bubble shibaura" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">User Assignment</h6>
                        <p class="small text-muted mb-0">Tour organizers and engineers can be mapped to specific plants. When visitors arrive, feedback will be tracked by facility.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="stat-icon-bubble emerald" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Multi-Plant Ready</h6>
                        <p class="small text-muted mb-0">You can create multiple manufacturing units, divisions, and experience labs across locations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
