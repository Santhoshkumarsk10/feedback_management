<!-- Modern Clean Dynamic Topbar -->
@php
    $currentRangeKey = request('range', $selectedRange ?? 'month');
    $displayRangeLabel = $rangeLabel ?? null;
    if (!$displayRangeLabel) {
        $now = now();
        switch ($currentRangeKey) {
            case 'today':
                $displayRangeLabel = $now->format('d M Y');
                break;
            case '7days':
                $displayRangeLabel = $now->subDays(6)->format('d M') . ' - ' . now()->format('d M Y');
                break;
            case '30days':
                $displayRangeLabel = $now->subDays(29)->format('d M') . ' - ' . now()->format('d M Y');
                break;
            case 'all':
                $displayRangeLabel = 'All Time Records';
                break;
            case 'month':
            default:
                $displayRangeLabel = now()->startOfMonth()->format('d M Y') . ' - ' . now()->endOfMonth()->format('d M Y');
                break;
        }
    }

    $pendingFeedbackCount = $topbarPendingCount ?? ($todayCounts['pending'] ?? 0);
    $escalatedFeedbackCount = $topbarEscalatedCount ?? 0;
    $hasNotifications = $pendingFeedbackCount > 0 || $escalatedFeedbackCount > 0;
@endphp

<header class="app-topbar-clean">
    <div class="topbar-left-clean">
        <button type="button" class="mobile-nav-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title-wrapper">
            <h1 class="topbar-main-title">@yield('page_title', View::getSection('title', 'Dashboard'))</h1>
            <p class="topbar-sub-title">@yield('page_subtitle', "Welcome back! Here's an overview of your visitor feedback.")</p>
        </div>
    </div>

    <div class="topbar-right-clean">
        @if(View::hasSection('topbar_actions'))
            <div class="topbar-actions-group d-flex align-items-center gap-2 me-1">
                @yield('topbar_actions')
            </div>
        @endif

        <!-- 1. Dynamic Date Range Filter Pill (Dashboard & Analytics) -->
        @if(request()->routeIs('dashboard*') || request()->routeIs('reports*'))
            <div class="dropdown flex-shrink-0">
                <button class="topbar-pill-btn dropdown-toggle" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Filter analytics date range">
                    <i class="bi bi-calendar3 text-primary"></i>
                    <span id="currentDateRangeLabel" class="fw-semibold">{{ $displayRangeLabel }}</span>
                    <i class="bi bi-chevron-down small text-muted ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1" aria-labelledby="dateRangeDropdown" style="font-size: 0.85rem; min-width: 220px;">
                    <li class="px-3 py-1 text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Quick Presets</li>
                    <li>
                        <a class="dropdown-item py-2 fw-medium d-flex align-items-center justify-content-between {{ $currentRangeKey === 'today' ? 'active' : '' }}" 
                           href="{{ route('dashboard', array_merge(request()->except(['page', 'from', 'to']), ['range' => 'today'])) }}">
                            <span><i class="bi bi-clock me-2 text-muted"></i>Today</span>
                            @if($currentRangeKey === 'today') <i class="bi bi-check2 text-primary fw-bold"></i> @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-medium d-flex align-items-center justify-content-between {{ $currentRangeKey === '7days' ? 'active' : '' }}" 
                           href="{{ route('dashboard', array_merge(request()->except(['page', 'from', 'to']), ['range' => '7days'])) }}">
                            <span><i class="bi bi-calendar-week me-2 text-muted"></i>Last 7 Days</span>
                            @if($currentRangeKey === '7days') <i class="bi bi-check2 text-primary fw-bold"></i> @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-medium d-flex align-items-center justify-content-between {{ $currentRangeKey === 'month' ? 'active' : '' }}" 
                           href="{{ route('dashboard', array_merge(request()->except(['page', 'from', 'to']), ['range' => 'month'])) }}">
                            <span><i class="bi bi-calendar-month me-2 text-muted"></i>This Month ({{ now()->format('M Y') }})</span>
                            @if($currentRangeKey === 'month') <i class="bi bi-check2 text-primary fw-bold"></i> @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-medium d-flex align-items-center justify-content-between {{ $currentRangeKey === '30days' ? 'active' : '' }}" 
                           href="{{ route('dashboard', array_merge(request()->except(['page', 'from', 'to']), ['range' => '30days'])) }}">
                            <span><i class="bi bi-calendar3-range me-2 text-muted"></i>Last 30 Days</span>
                            @if($currentRangeKey === '30days') <i class="bi bi-check2 text-primary fw-bold"></i> @endif
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 fw-medium d-flex align-items-center justify-content-between {{ $currentRangeKey === 'all' ? 'active' : '' }}" 
                           href="{{ route('dashboard', array_merge(request()->except(['page', 'from', 'to']), ['range' => 'all'])) }}">
                            <span><i class="bi bi-database me-2 text-muted"></i>All Time Records</span>
                            @if($currentRangeKey === 'all') <i class="bi bi-check2 text-primary fw-bold"></i> @endif
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- 2. Global Live Search Pill -->
        <div class="topbar-search-wrapper position-relative">
            <i class="bi bi-search search-icon text-muted"></i>
            <input type="text" 
                   class="topbar-search-input" 
                   id="globalTopSearch" 
                   placeholder="Search visitors, coordinators or feedback..." 
                   autocomplete="off">
            <span id="globalSearchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute" style="right: 14px; display: none;" role="status"></span>
            
            <!-- Live Autocomplete Floating Panel -->
            <div id="globalSearchDropdown" class="topbar-search-dropdown shadow-lg" style="display: none;">
                <div id="globalSearchResults"></div>
                <div class="p-2 border-top text-center bg-light rounded-bottom small" style="font-size: 0.75rem;">
                    <span class="text-muted">Press <kbd class="bg-white border text-dark px-1 rounded">Enter</kbd> to search in Visitors &amp; Plant Visits</span>
                </div>
            </div>
        </div>

        <!-- 3. Dynamic Notification Bell Dropdown -->
        <div class="dropdown">
            <button class="topbar-icon-btn position-relative" type="button" id="topbarNotificationsBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="bi bi-bell text-secondary fs-5"></i>
                @if($hasNotifications)
                    <span class="notification-indicator-dot animate-pulse"></span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end topbar-notification-menu shadow-lg p-0 mt-2" aria-labelledby="topbarNotificationsBtn" style="width: 350px; max-width: 90vw;">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-primary"></i>
                        <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                    </div>
                    @if($pendingFeedbackCount > 0)
                        <span class="badge bg-danger text-white rounded-pill px-2 py-1">{{ $pendingFeedbackCount }} Pending</span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">All Clear</span>
                    @endif
                </div>

                <div class="py-1" style="max-height: 360px; overflow-y: auto;">
                    <!-- Pending Visitors Today Notification -->
                    @if($pendingFeedbackCount > 0)
                        <a href="{{ route('shift_queue', ['today_tab' => 'pending']) }}" class="notification-row-item">
                            <div class="notification-icon-wrap bg-primary-subtle text-primary">
                                <i class="bi bi-clipboard2-check-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold text-dark small">Pending Feedback Queue</div>
                                    <span class="text-muted" style="font-size: 0.7rem;">Today</span>
                                </div>
                                <div class="small text-secondary mt-1">
                                    <strong>{{ $pendingFeedbackCount }} visitor(s)</strong> have completed plant visit and are awaiting feedback collection.
                                </div>
                            </div>
                        </a>
                    @endif

                    <!-- Low Feedback Quality Alert (if any) -->
                    @if($escalatedFeedbackCount > 0)
                        <a href="{{ route('feedbacks.index') }}" class="notification-row-item">
                            <div class="notification-icon-wrap bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold text-dark small">Quality Escalate Alert</div>
                                    <span class="text-muted" style="font-size: 0.7rem;">Past 7d</span>
                                </div>
                                <div class="small text-secondary mt-1">
                                    <strong>{{ $escalatedFeedbackCount }} low rating review(s)</strong> (≤ 2 Stars) logged requiring management attention.
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(!$hasNotifications)
                        <div class="text-center py-4 px-3 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            <div class="fw-semibold text-dark small">All caught up!</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">No overdue feedback right now.</div>
                        </div>
                    @endif
                </div>

                <div class="p-2 border-top bg-light">
                    <a href="{{ route('shift_queue') }}" class="btn btn-sm btn-outline-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                        <span>Open Today's Visitor Queue</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. User Profile Pill -->
        <div class="dropdown">
            <a href="javascript:void(0)" class="topbar-user-badge text-decoration-none dropdown-toggle" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @php
                    $userName = auth()->user()->name ?? 'User';
                    $parts = explode(' ', trim($userName));
                    $userInitials = count($parts) >= 2 
                        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                        : strtoupper(substr($userName, 0, 2));
                    $userRole = auth()->user()->roleModel?->display_name ?? (auth()->user()->roles->first()?->display_name ?? ucfirst(auth()->user()->role ?? 'Staff'));
                @endphp
                <div class="user-avatar-circle">
                    {{ $userInitials }}
                </div>
                <div class="user-text-info d-none d-sm-flex flex-column text-start">
                    <span class="user-name-label">{{ $userName }}</span>
                    <span class="user-role-label">{{ $userRole }}</span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 200px; font-size: 0.85rem;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">{{ $userName }}</div>
                    <div class="small text-muted text-truncate">{{ auth()->user()->email ?? '' }}</div>
                    @if(auth()->user() && auth()->user()->department)
                        <div class="badge bg-secondary-subtle text-secondary border mt-1" style="font-size: 0.7rem;">{{ auth()->user()->department }}</div>
                    @endif
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('profile') }}"><i class="bi bi-person-circle me-2 text-primary"></i> My Profile</a></li>
                @if(auth()->user() && auth()->user()->hasRole('superadmin'))
                    <li><a class="dropdown-item py-2" href="{{ route('company.edit') }}"><i class="bi bi-gear me-2 text-secondary"></i> System Settings</a></li>
                @endif
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Global Topbar Live Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalTopSearch');
    const searchDropdown = document.getElementById('globalSearchDropdown');
    const searchResults = document.getElementById('globalSearchResults');
    const searchSpinner = document.getElementById('globalSearchSpinner');

    if (!searchInput || !searchDropdown || !searchResults) return;

    let debounceTimer = null;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            searchDropdown.style.display = 'none';
            if (searchSpinner) searchSpinner.style.display = 'none';
            return;
        }

        if (searchSpinner) searchSpinner.style.display = 'inline-block';

        debounceTimer = setTimeout(function() {
            fetch(`{{ route('dashboard.global_search') }}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (searchSpinner) searchSpinner.style.display = 'none';
                renderSearchResults(data, query);
            })
            .catch(err => {
                if (searchSpinner) searchSpinner.style.display = 'none';
                console.error('Search error:', err);
            });
        }, 250);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query.length > 0) {
                window.location.href = `{{ route('visits.index') }}?q=${encodeURIComponent(query)}`;
            }
        } else if (e.key === 'Escape') {
            searchDropdown.style.display = 'none';
        }
    });

    function renderSearchResults(data, query) {
        if (!data || !data.results || data.total === 0) {
            searchResults.innerHTML = `
                <div class="text-center py-4 px-3 text-muted">
                    <i class="bi bi-search fs-3 text-secondary opacity-50 d-block mb-2"></i>
                    <div class="fw-semibold small text-dark">No records found regarding your search</div>
                    <div class="small text-muted mt-1" style="font-size: 0.76rem;">No matching results found for "<strong>${escapeHtml(query)}</strong>" in visitors list, coordinator list, or feedback list.</div>
                </div>
            `;
            searchDropdown.style.display = 'block';
            return;
        }

        let html = '';

        // 1. Visitors List
        if (data.results.visitors && data.results.visitors.length > 0) {
            html += `<div class="search-section-header"><i class="bi bi-person-badge me-1 text-primary"></i> Visitors List</div>`;
            data.results.visitors.forEach(v => {
                html += `
                    <a href="${v.url}" class="search-result-item">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="search-item-icon bg-primary-subtle text-primary">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${escapeHtml(v.title)}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                                    ${escapeHtml(v.company)} • <span class="badge bg-light text-secondary border">${escapeHtml(v.code || 'Visit')}</span>
                                </div>
                            </div>
                        </div>
                        <span class="badge ${v.has_feedback ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'} rounded-pill ms-2 flex-shrink-0" style="font-size: 0.7rem;">
                            ${escapeHtml(v.action_label)}
                        </span>
                    </a>
                `;
            });
        }

        // 2. Feedback List
        if (data.results.feedbacks && data.results.feedbacks.length > 0) {
            html += `<div class="search-section-header mt-2"><i class="bi bi-chat-quote me-1 text-success"></i> Feedback List</div>`;
            data.results.feedbacks.forEach(f => {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += `<i class="bi ${i <= f.rating ? 'bi-star-fill text-warning' : 'bi-star text-muted'}" style="font-size: 0.65rem;"></i>`;
                }
                html += `
                    <a href="${f.url}" class="search-result-item">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="search-item-icon bg-success-subtle text-success">
                                <i class="bi bi-chat-left-dots-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark small text-truncate">${escapeHtml(f.title)}</span>
                                    <span>${stars}</span>
                                </div>
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">${escapeHtml(f.comments)}</div>
                            </div>
                        </div>
                        <span class="text-muted small ms-2 flex-shrink-0" style="font-size: 0.7rem;">${escapeHtml(f.date)}</span>
                    </a>
                `;
            });
        }

        // 3. Coordinator List
        if (data.results.coordinators && data.results.coordinators.length > 0) {
            html += `<div class="search-section-header mt-2"><i class="bi bi-person-badge-fill me-1 text-info"></i> Coordinator List</div>`;
            data.results.coordinators.forEach(s => {
                html += `
                    <a href="${s.url}" class="search-result-item">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="search-item-icon bg-info-subtle text-info">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${escapeHtml(s.title)}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">${escapeHtml(s.department)} • Coordinator</div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted small ms-2"></i>
                    </a>
                `;
            });
        }

        searchResults.innerHTML = html;
        searchDropdown.style.display = 'block';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.style.display = 'none';
        }
    });
});
</script>
