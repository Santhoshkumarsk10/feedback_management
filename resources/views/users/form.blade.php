@extends('layouts.app')
@section('title', $user->exists ? 'Edit User: '.$user->name : 'Add New User')
@section('page_title', $user->exists ? 'Edit User Profile' : 'Create Team Member')
@section('page_subtitle', $user->exists ? 'Update role, department, or login access' : 'Register a new administrator or plant tour organizer')

@section('content')
<div class="mb-3">
    <a href="{{ route('users.index') }}" class="btn-modern-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to User List
    </a>
</div>

<div class="row g-4">
    <!-- Main User Form Column (Col-lg-8) -->
    <div class="col-lg-8">
        <div class="card-modern" style="border-left: 4px solid var(--shibaura-blue);">
            <div class="card-header bg-white">
                <div class="card-title">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span>{{ $user->exists ? 'Update Account Information' : 'New User Details' }}</span>
                </div>
                @if($user->exists)
                    <span class="badge-modern badge-shibaura">ID: #{{ $user->id }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
                    @csrf
                    @if($user->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-modern-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input name="name" class="form-control form-control-modern border-start-0" value="{{ old('name', $user->name) }}" placeholder="e.g. Ramesh Kumar" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Access Role <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <select name="role_id" class="form-select form-select-modern border-start-0" @disabled($user->id === auth()->id()) required>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id || (empty($user->role_id) && $user->role === $r->slug))>
                                            {{ $r->name }} ({{ $r->slug }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                                <div class="small text-muted mt-1">You cannot modify your own role.</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Assigned Manufacturing Plant</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-buildings"></i></span>
                                <select name="plant_id" class="form-select form-select-modern border-start-0">
                                    <option value="">— HQ / Corporate (All Plants) —</option>
                                    @foreach($plants as $p)
                                        <option value="{{ $p->id }}" @selected(old('plant_id', $user->plant_id) == $p->id)>
                                            {{ $p->code }} — {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="small text-muted mt-1">Select the factory unit this user belongs to</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control form-control-modern border-start-0" value="{{ old('email', $user->email) }}" placeholder="name@plant.test">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                <input name="mobile" class="form-control form-control-modern border-start-0" value="{{ old('mobile', $user->mobile) }}" placeholder="9100000000">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">Department / Area</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-building"></i></span>
                                <input name="department" class="form-control form-control-modern border-start-0" value="{{ old('department', $user->department) }}" placeholder="e.g. Production, Quality, Technical Centre">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-modern-label">
                                Password 
                                @if($user->exists)
                                    <small class="text-muted fw-normal">(Leave blank to keep current)</small>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control form-control-modern border-start-0" placeholder="••••••••" {{ $user->exists ? '' : 'required' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="p-3 mb-4 rounded-3 bg-light border border-slate-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold text-dark">Account Status</div>
                                <div class="small text-muted">Active accounts can log into the mobile organizer app or web portal.</div>
                            </div>
                            <div class="form-check form-switch fs-5 m-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $user->is_active ?? true))>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i> {{ $user->exists ? 'Save Changes' : 'Create User' }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn-modern-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Role Privileges & Security Reference (Col-lg-4) -->
    <div class="col-lg-4">
        <div class="card-modern mb-4">
            <div class="card-header bg-white">
                <div class="card-title text-primary">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Role Access Privileges</span>
                </div>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-3" style="font-size: 0.82rem;">
                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                        <div class="fw-bold text-dark">Super Admin</div>
                        <div class="small text-muted">Full administrative privileges across user management, questionnaire configuration, and reporting.</div>
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-info">
                        <div class="fw-bold text-dark">Admin</div>
                        <div class="small text-muted">Technical Centre management, review audit, export logs, and visitor management.</div>
                    </div>
                    <div class="p-2 rounded bg-light border-start border-3 border-success">
                        <div class="fw-bold text-dark">Organizer / Guide</div>
                        <div class="small text-muted">Access to mobile organizer application, conducting customer tours and reviewing received feedback.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-modern p-3 bg-light border">
            <div class="small text-muted">
                <i class="bi bi-info-circle-fill text-primary me-1"></i> Either email or mobile is required for user login authentication.
            </div>
        </div>
    </div>
</div>
@endsection
