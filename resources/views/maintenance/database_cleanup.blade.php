@extends('layouts.app')
@section('title', 'Database Maintenance - Table Cleanup')
@section('page_title', 'Database Maintenance')
@section('page_subtitle', 'Database Table Cleanup — Safely clear unshielded table data with strict protection on authentication, roles, sessions, and audit schemas')

@section('topbar_actions')
    <div class="d-flex align-items-center gap-2">
        <form method="POST" action="{{ route('database_cleanup.reseed') }}" onsubmit="return confirm('Restore & Re-seed Default Data?\n\nThis will re-run database seeders to populate default plants, shifts, feedback questions, and demo credentials if tables were cleared.');">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm fw-semibold shadow-xs">
                <i class="bi bi-arrow-clockwise me-1"></i> Re-seed Default Data
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- KPI Summary Overview -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card-widget cyan h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">TOTAL TABLES</span>
                    <div class="stat-icon-bubble cyan">
                        <i class="bi bi-database-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ count($unshieldedTables) + count($protectedTables) }}</div>
                <span class="stat-pill-trend neutral">
                    <i class="bi bi-layers"></i> Schema Tables
                </span>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="stat-card-widget emerald h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">UNSHIELDED (CLEARABLE)</span>
                    <div class="stat-icon-bubble emerald">
                        <i class="bi bi-unlock-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ count($unshieldedTables) }}</div>
                <span class="stat-pill-trend up">
                    <i class="bi bi-table"></i> {{ number_format($totalUnshieldedRows) }} Total Rows
                </span>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="stat-card-widget rose h-100">
                <div class="stat-widget-header">
                    <span class="stat-label-text">PROTECTED (SHIELDED)</span>
                    <div class="stat-icon-bubble rose">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
                <div class="stat-metric-number">{{ count($protectedTables) }}</div>
                <span class="stat-pill-trend down">
                    <i class="bi bi-lock-fill"></i> {{ number_format($totalProtectedRows) }} Rows Locked
                </span>
            </div>
        </div>
    </div>

    <!-- Main Clearance Form -->
    <form id="clearTablesForm" method="POST" action="{{ route('database_cleanup.clear') }}">
        @csrf

        <!-- SECTION 1: UNSHIELDED / CLEARABLE TABLES -->
        <div class="card-modern mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="card-title">
                    <i class="bi bi-database-dash text-warning"></i>
                    <span>Unshielded Tables (Select to Clear)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1" onclick="toggleSelectAll(true)">
                        <i class="bi bi-check-all me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1" onclick="toggleSelectAll(false)">
                        <i class="bi bi-x me-1"></i> Deselect All
                    </button>
                </div>
            </div>

            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 400px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="tableFilterInput" class="form-control border-start-0" placeholder="Filter tables by name or category..." onkeyup="filterTables()">
                    </div>
                </div>
                <div class="small text-muted">
                    Selected for clearance: <strong id="selectedCountBadge" class="text-danger fs-6">0</strong> table(s)
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-modern align-middle mb-0" id="unshieldedTable">
                    <thead>
                        <tr>
                            <th style="width: 48px;" class="text-center">
                                <input type="checkbox" id="masterCheckbox" class="form-check-input" title="Select / Deselect All Clearable Tables" onchange="toggleSelectAll(this.checked)">
                            </th>
                            <th style="min-width: 180px;">Table Name</th>
                            <th style="min-width: 130px;">Category</th>
                            <th style="min-width: 120px;" class="text-end">Record Count</th>
                            <th style="min-width: 250px;">Description</th>
                            <th style="min-width: 200px;">Migration Source</th>
                            <th style="min-width: 120px;" class="text-center">Access Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unshieldedTables as $tbl)
                            <tr class="unshielded-row" data-name="{{ strtolower($tbl['name']) }}" data-category="{{ strtolower($tbl['category']) }}">
                                <td class="text-center">
                                    <input type="checkbox" name="tables[]" value="{{ $tbl['name'] }}" class="form-check-input table-select-cb" data-rows="{{ $tbl['rows'] }}" onchange="updateSelectedCount()">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-table text-primary"></i>
                                        <code class="fw-bold text-dark fs-6">{{ $tbl['name'] }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-modern badge-indigo">{{ $tbl['category'] }}</span>
                                </td>
                                <td class="text-end">
                                    @if($tbl['rows'] > 0)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 fw-bold font-monospace">
                                            {{ number_format($tbl['rows']) }} rows
                                        </span>
                                    @else
                                        <span class="text-muted small font-monospace">0 rows</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small text-secondary">{{ $tbl['description'] }}</span>
                                </td>
                                <td>
                                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                        <i class="bi bi-file-earmark-code me-1"></i>{{ $tbl['migration'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-unlock-fill me-1"></i> Clearable
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No unshielded tables available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Action Bar -->
            <div class="card-footer p-3 bg-white d-flex flex-wrap align-items-center justify-content-between gap-3 border-top">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                    <span class="small text-muted">
                        Truncating will reset auto-increment counters and clear all records from chosen tables. Foreign keys will be safely handled.
                    </span>
                </div>
                <div>
                    <button type="button" id="openClearModalBtn" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm" onclick="promptClearConfirmation()" disabled>
                        <i class="bi bi-trash3-fill me-1"></i> Clear Selected Tables (<span id="clearBtnCount">0</span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-danger text-white p-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            <h5 class="modal-title fw-bold mb-0" id="confirmClearModalLabel">Confirm Data Truncation</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-shield-exclamation fs-5 flex-shrink-0"></i>
                            <div class="small">
                                <strong>Warning: High-impact database action!</strong><br>
                                All records in the selected tables will be permanently wiped.
                            </div>
                        </div>

                        <p class="small text-muted mb-2">You have selected the following <strong id="modalTableCount">0</strong> table(s) to clear:</p>
                        <div id="modalSelectedList" class="p-2 bg-light rounded-3 border mb-3 font-monospace small" style="max-height: 180px; overflow-y: auto;">
                            <!-- Populated dynamically via JS -->
                        </div>

                        <div class="form-check p-0 mb-3">
                            <label class="form-check-label small fw-semibold text-dark">
                                <input type="checkbox" id="modalAckCheckbox" class="form-check-input me-2" onchange="checkModalAck()">
                                I understand this action permanently deletes data from the selected tables.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="modalFinalSubmitBtn" class="btn btn-danger btn-sm px-3 fw-bold" disabled>
                            <i class="bi bi-trash-fill me-1"></i> Yes, Permanently Clear Selected Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- SECTION 2: PROTECTED / SHIELDED TABLES -->
    <div class="card-modern border-danger-subtle mb-4">
        <div class="card-header bg-danger-subtle py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-danger-subtle">
            <div class="card-title text-danger mb-0">
                <i class="bi bi-shield-lock-fill text-danger me-1"></i>
                <span>Strictly Protected Tables (Security & Auth Shielded)</span>
            </div>
            <span class="badge bg-danger text-white px-2 py-1 fw-bold">
                {{ count($protectedTables) }} Tables Locked
            </span>
        </div>

        <div class="p-3 bg-white border-bottom border-danger-subtle">
            <div class="d-flex align-items-center gap-2 small text-slate-700">
                <i class="bi bi-lock-fill text-danger fs-5"></i>
                <div>
                    <strong>Protection Policy Active:</strong> The tables listed below store user credentials, password tokens, role-permission matrices, system session tokens, and audit compliance trails. They are <strong>hard-coded as protected</strong> and cannot be cleared or truncated from this console to prevent total administrative lockout.
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 48px;" class="text-center">
                            <i class="bi bi-lock-fill text-danger"></i>
                        </th>
                        <th style="min-width: 180px;">Table Name</th>
                        <th style="min-width: 120px;" class="text-end">Record Count</th>
                        <th style="min-width: 250px;">Protection Reason</th>
                        <th style="min-width: 220px;">Schema Description</th>
                        <th style="min-width: 120px;" class="text-center">Security Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($protectedTables as $tbl)
                        <tr style="background: rgba(254, 242, 242, 0.35);">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" disabled checked="false" title="Locked table: Cannot be cleared">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-fill-check text-danger"></i>
                                    <code class="fw-bold text-danger fs-6">{{ $tbl['name'] }}</code>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-secondary-subtle text-dark border px-2 py-1 font-monospace">
                                    {{ number_format($tbl['rows']) }} rows
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                                    <i class="bi bi-shield-slash-fill me-1"></i> {{ $tbl['protected_reason'] }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $tbl['description'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger text-white px-2 py-1">
                                    <i class="bi bi-lock-fill me-1"></i> Shielded
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.table-select-cb:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCountBadge').textContent = count;
        document.getElementById('clearBtnCount').textContent = count;
        
        const openBtn = document.getElementById('openClearModalBtn');
        if (openBtn) {
            openBtn.disabled = count === 0;
        }

        const masterCb = document.getElementById('masterCheckbox');
        const allCheckboxes = document.querySelectorAll('.table-select-cb');
        if (masterCb && allCheckboxes.length > 0) {
            masterCb.checked = count === allCheckboxes.length;
            masterCb.indeterminate = count > 0 && count < allCheckboxes.length;
        }
    }

    function toggleSelectAll(select) {
        document.querySelectorAll('.table-select-cb').forEach(cb => {
            // Only toggle visible rows in case user filtered
            const row = cb.closest('tr');
            if (row && row.style.display !== 'none') {
                cb.checked = select;
            }
        });
        updateSelectedCount();
    }

    function filterTables() {
        const query = (document.getElementById('tableFilterInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.unshielded-row').forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const category = row.getAttribute('data-category') || '';
            const matches = name.includes(query) || category.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    }

    function promptClearConfirmation() {
        const checkboxes = document.querySelectorAll('.table-select-cb:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one unshielded table to clear.');
            return;
        }

        const listContainer = document.getElementById('modalSelectedList');
        listContainer.innerHTML = '';
        let listHtml = '<ul class="mb-0 ps-3">';

        checkboxes.forEach(cb => {
            const rows = cb.getAttribute('data-rows') || '0';
            listHtml += `<li class="py-1"><strong>${cb.value}</strong> <span class="text-muted">(${rows} records)</span></li>`;
        });
        listHtml += '</ul>';
        listContainer.innerHTML = listHtml;

        document.getElementById('modalTableCount').textContent = checkboxes.length;
        document.getElementById('modalAckCheckbox').checked = false;
        document.getElementById('modalFinalSubmitBtn').disabled = true;

        const modalEl = document.getElementById('confirmClearModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function checkModalAck() {
        const ack = document.getElementById('modalAckCheckbox').checked;
        document.getElementById('modalFinalSubmitBtn').disabled = !ack;
    }
</script>
@endpush
