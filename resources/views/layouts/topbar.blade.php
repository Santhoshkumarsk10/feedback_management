<!-- Modern Clean Topbar -->
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
        <!-- Date Range Filter Pill -->
        <div class="dropdown">
            <button class="topbar-pill-btn dropdown-toggle" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-calendar3 text-muted"></i>
                <span id="currentDateRangeLabel">{{ now()->startOfMonth()->format('d M Y') }} - {{ now()->endOfMonth()->format('d M Y') }}</span>
                <i class="bi bi-chevron-down small text-muted ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1" aria-labelledby="dateRangeDropdown" style="font-size: 0.85rem;">
                <li><a class="dropdown-item py-2 fw-medium" href="javascript:void(0)" onclick="setDateRange('today')">Today</a></li>
                <li><a class="dropdown-item py-2 fw-medium" href="javascript:void(0)" onclick="setDateRange('7days')">Last 7 Days</a></li>
                <li><a class="dropdown-item py-2 fw-medium active" href="javascript:void(0)" onclick="setDateRange('month')">This Month ({{ now()->format('M Y') }})</a></li>
                <li><a class="dropdown-item py-2 fw-medium" href="javascript:void(0)" onclick="setDateRange('30days')">Last 30 Days</a></li>
            </ul>
        </div>

        <!-- Global Search Pill -->
        <div class="topbar-search-wrapper">
            <i class="bi bi-search search-icon text-muted"></i>
            <input type="text" class="topbar-search-input" id="globalTopSearch" placeholder="Search visitors, hosts or feedback..." autocomplete="off">
        </div>

        <!-- Notification Bell Icon with Badge -->
        <a href="{{ route('shift_queue') }}" class="topbar-icon-btn position-relative" title="Notifications & Shift Queue">
            <i class="bi bi-bell text-secondary fs-5"></i>
            @if(($todayCounts['pending'] ?? 0) > 0)
                <span class="notification-indicator-dot"></span>
            @endif
        </a>

        <!-- User Profile Pill -->
        <div class="dropdown">
            <a href="javascript:void(0)" class="topbar-user-badge text-decoration-none dropdown-toggle" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @php
                    $userName = auth()->user()->name ?? 'Varun K';
                    $parts = explode(' ', trim($userName));
                    $userInitials = count($parts) >= 2 
                        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                        : strtoupper(substr($userName, 0, 2));
                    $userRole = auth()->user()->roleModel?->display_name ?? (auth()->user()->roles->first()?->display_name ?? ucfirst(auth()->user()->role ?? 'Super Admin'));
                @endphp
                <div class="user-avatar-circle">
                    {{ $userInitials }}
                </div>
                <div class="user-text-info d-none d-sm-flex flex-column text-start">
                    <span class="user-name-label">{{ $userName }}</span>
                    <span class="user-role-label">{{ $userRole }}</span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 180px; font-size: 0.85rem;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">{{ $userName }}</div>
                    <div class="small text-muted">{{ auth()->user()->email ?? '' }}</div>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('password.change') }}"><i class="bi bi-person me-2 text-primary"></i> Profile & Password</a></li>
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
