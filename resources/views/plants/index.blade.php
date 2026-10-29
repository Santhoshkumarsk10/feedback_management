@extends('layouts.app')
@section('title', 'Plant Master')
@section('page_title', 'Plant Master')
@section('page_subtitle', 'Manage Shibaura Machine manufacturing plant details, locations, and operational facilities')

@section('topbar_actions')
    @can('plantmaster.create')
        <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addPlantModal">
            <i class="bi bi-building-add"></i>
            <span>Register New Plant</span>
        </button>
    @else
        <span class="badge bg-secondary-subtle text-secondary py-1 px-2 border"><i class="bi bi-eye"></i> View Only</span>
    @endcan
@endsection

@section('summary_cards')
@php
    $totalPlants = $tabCounts['all'] ?? 0;
    $activePlants = $tabCounts['active'] ?? 0;
    $inactivePlants = $tabCounts['inactive'] ?? 0;
    $locationsCount = $allPlantSuggestions ? $allPlantSuggestions->pluck('location')->unique()->filter()->count() : 0;
@endphp
<div class="row g-3">
    <!-- 1. Total Plants -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/total-plants.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($totalPlants) }}</div>
                        <div class="saas-stat-label">Total plants</div>
                    </div>
                </div>
                <span class="saas-stat-badge slate">Network</span>
            </div>
        </div>
    </div>

    <!-- 2. Active Plants -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/active-plants.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($activePlants) }}</div>
                        <div class="saas-stat-label">Active plants</div>
                    </div>
                </div>
                <span class="saas-stat-badge emerald">Fully operational</span>
            </div>
        </div>
    </div>

    <!-- 3. Inactive Plants -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/inactive-plants.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($inactivePlants) }}</div>
                        <div class="saas-stat-label">Inactive plants</div>
                    </div>
                </div>
                <span class="saas-stat-badge amber">Standby</span>
            </div>
        </div>
    </div>

    <!-- 4. Locations -->
    <div class="col-6 col-lg-3">
        <div class="saas-stat-card">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <img src="/icons/locations.svg" width="48" height="48" alt="">
                    <div>
                        <div class="saas-stat-value">{{ number_format($locationsCount) }}</div>
                        <div class="saas-stat-label">Locations</div>
                    </div>
                </div>
                <span class="saas-stat-badge indigo">Industrial zones</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Tabs & Controls Card -->
<div class="saas-toolbar-card">
    <!-- Left: Filter Pills -->
    <div class="saas-filter-pills">
        <a href="{{ route('plants.index', array_merge(request()->except(['tab', 'page']))) }}"
           class="saas-pill-btn {{ ($currentTab ?? 'all') === 'all' ? 'active' : '' }}">
            <span>All plants</span>
            <span class="pill-counter">{{ $tabCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('plants.index', array_merge(request()->except(['page']), ['tab' => 'active'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'active' ? 'active' : '' }}">
            <span>Active</span>
            <span class="pill-counter">{{ $tabCounts['active'] ?? 0 }}</span>
        </a>
        <a href="{{ route('plants.index', array_merge(request()->except(['page']), ['tab' => 'inactive'])) }}"
           class="saas-pill-btn {{ ($currentTab ?? '') === 'inactive' ? 'active' : '' }}">
            <span>Inactive</span>
            <span class="pill-counter">{{ $tabCounts['inactive'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Right: Search & Filter -->
    <div class="saas-toolbar-controls">
        <form method="GET" action="{{ route('plants.index') }}" class="d-flex align-items-center gap-2 m-0" id="plantSearchForm">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="saas-search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" class="saas-search-input" placeholder="Search by plant code, name or location">
            </div>

            <button type="submit" class="saas-btn-filter">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['q', 'tab']))
                <a href="{{ route('plants.index') }}" class="saas-btn-filter text-decoration-none" title="Clear filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Plants Table Card -->
<div class="saas-table-card">
    <div class="saas-table-header d-flex align-items-center justify-content-between p-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-building-gear text-primary"></i>
            <span class="fw-bold text-dark">Plant Directory</span>
        </div>
        <span class="badge-modern badge-slate">{{ $plants->total() }} Facilities</span>
    </div>


    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Plant</th>
                    <th style="width: 22%;">Location</th>
                    <th style="width: 22%;">Contact</th>
                    <th class="text-center" style="width: 10%;">Feedback</th>
                    <th style="width: 8%;">Status</th>
                    <th class="text-end" style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($plants as $p)
                <tr>
                    <td>
                        <span class="saas-code-badge mb-1">
                            <i class="bi bi-qr-code"></i> {{ $p->code }}
                        </span>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.92rem;">{{ $p->name }}</div>
                        @if($p->description)
                            <div class="small text-muted line-clamp-1 mt-0" style="max-width: 360px;">
                                {{ $p->description }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($p->location)
                            @php
                                $locParts = explode(',', $p->location);
                                $city = trim($locParts[0] ?? '');
                                $state = trim(implode(',', array_slice($locParts, 1)));
                            @endphp
                            <div class="d-flex align-items-start gap-2">
                                <div class="text-danger mt-1"><i class="bi bi-geo-alt-fill fs-6"></i></div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $city }}</div>
                                    <div class="small text-muted">{{ $state }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->contact_email)
                            <div class="small text-dark mb-1">
                                <i class="bi bi-envelope text-muted me-1"></i> {{ $p->contact_email }}
                            </div>
                        @endif
                        @if($p->contact_phone)
                            <div class="small text-muted">
                                <i class="bi bi-telephone text-muted me-1"></i> {{ $p->contact_phone }}
                            </div>
                        @endif
                        @if(!$p->contact_email && !$p->contact_phone)
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('feedbacks.index', ['plant_id' => $p->id]) }}" class="badge-modern badge-slate text-decoration-none py-1 px-2" title="View feedback submissions for this plant">
                            <i class="bi bi-chat-left-text me-1"></i> {{ $p->feedbacks_count }} feedbacks
                        </a>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('plants.toggle', $p) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <label class="saas-status-switch m-0" title="Click to toggle status">
                                <input type="checkbox" class="saas-switch-input" {{ $p->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="saas-switch-slider"></span>
                                <span class="saas-switch-label {{ $p->is_active ? '' : 'inactive' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span>
                            </label>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-2 align-items-center">
                            @can('plantmaster.edit')
                                <button type="button" class="btn-action-edit-pill" title="Edit plant"
                                        data-bs-toggle="modal" data-bs-target="#editPlantModal"
                                        data-id="{{ $p->id }}"
                                        data-code="{{ $p->code }}"
                                        data-name="{{ $p->name }}"
                                        data-location="{{ $p->location }}"
                                        data-email="{{ $p->contact_email }}"
                                        data-phone="{{ $p->contact_phone }}"
                                        data-description="{{ $p->description }}"
                                        data-is-active="{{ $p->is_active ? '1' : '0' }}"
                                        onclick="openEditPlantModal(this)">
                                    <i class="bi bi-pencil-fill"></i>
                                    <span>Edit</span>
                                </button>
                            @endcan
                            @can('plantmaster.delete')
                                <form method="POST" action="{{ route('plants.destroy', $p) }}" class="d-inline"
                                      data-confirm="Are you sure you want to delete {{ $p->name }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-delete-pill" type="submit" title="Delete plant" @disabled($p->users_count > 0)>
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-modern py-4">
                            <div class="empty-icon-bubble mb-3">
                                <i class="bi bi-buildings text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No plants found</h5>
                            <p class="text-muted small mb-3">No plant records match your current search or status filter.</p>
                            @can('plantmaster.create')
                                <button type="button" class="btn-modern-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#addPlantModal">
                                    <i class="bi bi-building-add"></i>
                                    <span>Register First Plant</span>
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="saas-table-footer">
        <div class="saas-footer-text">
            Showing {{ $plants->firstItem() ?? 0 }} to {{ $plants->lastItem() ?? 0 }} of {{ $plants->total() }} plants @if($plants->total() >= 1000) <span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1 fs-xs">Recent 1,000 max</span>@endif
        </div>
        <div class="saas-pagination">
            {{ $plants->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('plantmaster.create')
<!-- ADD PLANT MODAL (Without Scroll) -->
<div class="modal fade" id="addPlantModal" tabindex="-1" aria-labelledby="addPlantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="addPlantForm" action="{{ route('plants.store') }}" novalidate>
                @csrf
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble cyan p-2 rounded-3">
                            <i class="bi bi-building-add text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="addPlantModalLabel">Register New Plant Facility</h5>
                            <p class="text-muted small mb-0">Add a Shibaura Machine manufacturing division or technical centre</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-modern-label required" for="add_plant_code">Plant Code</label>
                            <input type="text" name="code" id="add_plant_code" class="form-control form-control-modern text-uppercase" placeholder="PLANT-05" required minlength="2" maxlength="20" pattern="^[a-zA-Z0-9_\-]+$" title="Plant code should contain letters, digits, hyphen, or underscore">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Letters, digits, hyphen, underscore</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-modern-label required" for="add_plant_name">Facility Name</label>
                            <input type="text" name="name" id="add_plant_name" class="form-control form-control-modern" placeholder="e.g. Plant 5 — Precision Tooling Centre" required minlength="2" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_plant_location">Facility Location / City</label>
                            <input type="text" name="location" id="add_plant_location" class="form-control form-control-modern" placeholder="e.g. Chembarambakkam, Chennai" minlength="3" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-modern-label mb-0" for="add_plant_email">Contact Email</label>
                                <span class="validation-counter text-muted" style="font-size: 0.72rem;">Optional</span>
                            </div>
                            <div class="input-icon-group">
                                <span class="input-icon-badge" id="badge_add_plant_email"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="contact_email" id="add_plant_email" class="form-control form-control-modern" placeholder="plant5.ops@shibaura-machine.in" maxlength="100" pattern="^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$" title="Please provide a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).">
                            </div>
                            <div id="feedback_add_plant_email" class="validation-feedback-box error d-none">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="feedback_add_plant_email_text"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="add_plant_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="add_plant_phone" class="form-control form-control-modern" placeholder="9844268125" minlength="10" maxlength="10" pattern="^[6-9][0-9]{9}$" title="10-digit mobile number starting with 6, 7, 8, or 9">
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between h-100">
                                <div>
                                    <span class="fw-semibold text-dark small d-block">Facility Status</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">Operational / Accepting Visits</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add_plant_is_active" checked style="width: 2.2em; height: 1.2em;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-modern-label" for="add_plant_description">Description & Manufacturing Capabilities</label>
                            <textarea name="description" id="add_plant_description" class="form-control form-control-modern" rows="2" placeholder="e.g. Specialized CNC horizontal boring machines, precision tooling divisions, and demo test cells" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-building-add me-1"></i> Register Plant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('plantmaster.edit')
<!-- EDIT PLANT MODAL (Without Scroll) -->
<div class="modal fade" id="editPlantModal" tabindex="-1" aria-labelledby="editPlantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="editPlantForm" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon-bubble emerald p-2 rounded-3">
                            <i class="bi bi-pencil-square text-emerald-600 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="editPlantModalLabel">Edit Plant Facility</h5>
                            <p class="text-muted small mb-0">Update division capabilities, contact info, and status</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-modern-label required" for="edit_plant_code">Plant Code</label>
                            <input type="text" name="code" id="edit_plant_code" class="form-control form-control-modern text-uppercase" required minlength="2" maxlength="20" pattern="^[a-zA-Z0-9_\-]+$" title="Plant code should contain letters, digits, hyphen, or underscore">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Letters, digits, hyphen, underscore</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-modern-label required" for="edit_plant_name">Facility Name</label>
                            <input type="text" name="name" id="edit_plant_name" class="form-control form-control-modern" required minlength="2" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_plant_location">Facility Location / City</label>
                            <input type="text" name="location" id="edit_plant_location" class="form-control form-control-modern" minlength="3" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-modern-label mb-0" for="edit_plant_email">Contact Email</label>
                                <span class="validation-counter text-muted" style="font-size: 0.72rem;">Optional</span>
                            </div>
                            <div class="input-icon-group">
                                <span class="input-icon-badge" id="badge_edit_plant_email"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="contact_email" id="edit_plant_email" class="form-control form-control-modern" maxlength="100" pattern="^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$" title="Please provide a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).">
                            </div>
                            <div id="feedback_edit_plant_email" class="validation-feedback-box error d-none">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="feedback_edit_plant_email_text"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-modern-label" for="edit_plant_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="edit_plant_phone" class="form-control form-control-modern" minlength="10" maxlength="10" pattern="^[6-9][0-9]{9}$" title="10-digit mobile number starting with 6, 7, 8, or 9">
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between h-100">
                                <div>
                                    <span class="fw-semibold text-dark small d-block">Facility Status</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">Operational / Accepting Visits</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_plant_is_active" style="width: 2.2em; height: 1.2em;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-modern-label" for="edit_plant_description">Description & Manufacturing Capabilities</label>
                            <textarea name="description" id="edit_plant_description" class="form-control form-control-modern" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern-primary btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Plant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
    const emailRegex = /^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/;
    const emailAllowedKey = /^[a-zA-Z0-9@._\-]$/;
    const emailDisallowed = /[^a-zA-Z0-9@._\-]/g;

    ['add_plant_email', 'edit_plant_email'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !emailAllowedKey.test(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            if (emailDisallowed.test(this.value)) {
                this.value = this.value.replace(emailDisallowed, '');
            }
            validatePlantEmail(id);
        });

        input.addEventListener('blur', function() {
            validatePlantEmail(id);
        });
    });

    function setFieldState(inputEl, feedbackEl, textEl, isValid, message = '', isOptionalEmpty = false) {
        if (!inputEl) return;

        if (isOptionalEmpty) {
            inputEl.classList.remove('is-invalid', 'is-valid');
            if (feedbackEl) feedbackEl.classList.add('d-none');
            if (textEl) textEl.textContent = '';
            return;
        }

        if (isValid) {
            inputEl.classList.remove('is-invalid');
            inputEl.classList.add('is-valid');
            if (feedbackEl) feedbackEl.classList.add('d-none');
            if (textEl) textEl.textContent = '';
        } else {
            inputEl.classList.remove('is-valid');
            inputEl.classList.add('is-invalid');
            if (feedbackEl) {
                feedbackEl.classList.remove('d-none');
                feedbackEl.classList.add('error');
            }
            if (textEl) textEl.textContent = message;
        }
    }

    function triggerShake(element) {
        if (!element) return;
        element.classList.remove('field-shake');
        void element.offsetWidth;
        element.classList.add('field-shake');
    }

    function validatePlantEmail(id) {
        const input = document.getElementById(id);
        if (!input) return true;
        const feedbackEl = document.getElementById('feedback_' + id);
        const textEl = document.getElementById('feedback_' + id + '_text');
        const val = input.value.trim();

        if (!val) {
            setFieldState(input, feedbackEl, textEl, true, '', true);
            return true;
        }

        if (!emailRegex.test(val)) {
            setFieldState(input, feedbackEl, textEl, false, 'Please enter a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).');
            return false;
        }

        setFieldState(input, feedbackEl, textEl, true);
        return true;
    }

    document.getElementById('addPlantForm')?.addEventListener('submit', function(e) {
        const emailInp = document.getElementById('add_plant_email');
        if (!validatePlantEmail('add_plant_email')) {
            e.preventDefault();
            if (emailInp) {
                emailInp.focus();
                triggerShake(emailInp);
            }
        }
    });

    document.getElementById('editPlantForm')?.addEventListener('submit', function(e) {
        const emailInp = document.getElementById('edit_plant_email');
        if (!validatePlantEmail('edit_plant_email')) {
            e.preventDefault();
            if (emailInp) {
                emailInp.focus();
                triggerShake(emailInp);
            }
        }
    });

    function openEditPlantModal(btn) {
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('editPlantForm');
        form.action = `{{ url('admin/plants') }}/${id}`;
        document.getElementById('edit_plant_code').value = btn.getAttribute('data-code') || '';
        document.getElementById('edit_plant_name').value = btn.getAttribute('data-name') || '';
        document.getElementById('edit_plant_location').value = btn.getAttribute('data-location') || '';
        document.getElementById('edit_plant_email').value = btn.getAttribute('data-email') || '';
        document.getElementById('edit_plant_phone').value = btn.getAttribute('data-phone') || '';
        document.getElementById('edit_plant_description').value = btn.getAttribute('data-description') || '';
        document.getElementById('edit_plant_is_active').checked = btn.getAttribute('data-is-active') === '1';
        setTimeout(() => { validatePlantEmail('edit_plant_email'); }, 150);
    }
</script>
@endpush
