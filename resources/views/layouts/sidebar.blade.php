<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Enterprise Sidebar -->
<aside class="app-sidebar" id="appSidebar">
    <!-- Brand Header with Shibaura Logo -->
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-logo-card">
            <img src="{{ $currentCompany?->logo_url ?? asset('images/shibaura-logo-cropped.webp') }}"
                alt="{{ $currentCompany?->name ?? 'Shibaura Machine' }}" class="sidebar-logo-img">
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        {{-- <div class="menu-category">Analytics & Overview</div> --}}
        <a class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        @php
            $currentUser = auth()->user();

            // Module permissions driven strictly by standard Laravel Gates / Roles & Permissions
            $canPlantMaster = $currentUser && $currentUser->can('plantmaster.view');
            $canRoleMaster = $currentUser && $currentUser->can('rolemaster.view');
            $canPermissionMaster = $currentUser && $currentUser->can('permissionmaster.view');
            $canUserDirectory = $currentUser && $currentUser->can('userdirectory.view');
            $canFeedbackForm = $currentUser && $currentUser->can('feedbackform.view');
            $hasSystemMasters =
                $canPlantMaster || $canRoleMaster || $canPermissionMaster || $canUserDirectory || $canFeedbackForm;

            $canCompany = $currentUser && $currentUser->can('companyprofile.view');
            $canBanner = $currentUser && $currentUser->can('bannercms.view');
            $hasBrandCMS = $canCompany || $canBanner;

            $canFeedbackLogs = $currentUser && $currentUser->can('feedbacklogs.view');
            $canViewReports = $currentUser && $currentUser->can('reportsexports.view');
            $canAuditLogs = $currentUser && $currentUser->can('auditlogs.view');
            $canDatabaseCleanup = $currentUser && $currentUser->can('tabledatacleanup.view');
            $canSystemConsole =
                $currentUser &&
                ($currentUser->can('tabledatacleanup.view') ||
                    (method_exists($currentUser, 'hasAnyRole') && $currentUser->hasAnyRole(['superadmin', 'admin'])) ||
                    in_array($currentUser->role ?? '', ['superadmin', 'admin'], true));
        @endphp

        {{-- Operations: All Authenticated Roles --}}
        <div class="menu-category">Operations & Visits</div>
        <a class="nav-item-link {{ request()->routeIs('shift_queue*') ? 'active' : '' }}"
            href="{{ route('shift_queue') }}">
            <i class="bi bi-calendar2-day-fill"></i>
            <span>Today's Visitor Queue</span>
        </a>
        <a class="nav-item-link {{ request()->routeIs('visits.*') ? 'active' : '' }}"
            href="{{ route('visits.index') }}">
            <i class="bi bi-person-badge-fill"></i>
            <span>Visitors</span>
        </a>
        {{-- Masters & Configuration --}}
        @if ($hasSystemMasters)
            <div class="menu-category">System Masters</div>
            @if ($canPlantMaster)
                <a class="nav-item-link {{ request()->routeIs('plants.*') ? 'active' : '' }}"
                    href="{{ route('plants.index') }}">
                    <i class="bi bi-buildings-fill"></i>
                    <span>Plant Master</span>
                </a>
            @endif
            @if ($canRoleMaster)
                <a class="nav-item-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                    href="{{ route('roles.index') }}">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Role Master</span>
                </a>
            @endif
            @if ($canPermissionMaster)
                <a class="nav-item-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                    href="{{ route('permissions.index') }}">
                    <i class="bi bi-key-fill"></i>
                    <span>Permission Master</span>
                </a>
            @endif
            @if ($canUserDirectory)
                <a class="nav-item-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>User Directory</span>
                </a>
            @endif
            @if ($canFeedbackForm)
                <a class="nav-item-link {{ request()->routeIs('questions.*') ? 'active' : '' }}"
                    href="{{ route('questions.index') }}">
                    <i class="bi bi-patch-question-fill"></i>
                    <span>Feedback Form</span>
                </a>
            @endif
        @endif

        {{-- Brand & CMS --}}
        @if ($hasBrandCMS)
            <div class="menu-category">Brand & Content CMS</div>
            @if ($canCompany)
                <a class="nav-item-link {{ request()->routeIs('company.*') ? 'active' : '' }}"
                    href="{{ route('company.edit') }}">
                    <i class="bi bi-building-gear"></i>
                    <span>Company Profile</span>
                </a>
            @endif
            @if ($canBanner)
                <a class="nav-item-link {{ request()->routeIs('banners.*') ? 'active' : '' }}"
                    href="{{ route('banners.index') }}">
                    <i class="bi bi-images"></i>
                    <span>Banner CMS</span>
                </a>
            @endif
        @endif

        {{-- Insights & Logs --}}
        <div class="menu-category">Insights & Logs</div>
        @if ($canFeedbackLogs)
            <a class="nav-item-link {{ request()->routeIs('feedbacks.*') ? 'active' : '' }}"
                href="{{ route('feedbacks.index') }}">
                <i class="bi bi-chat-square-heart-fill"></i>
                <span>Feedback Logs</span>
            </a>
        @endif

        @if ($canViewReports)
            <a class="nav-item-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                href="{{ route('reports.index') }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reports & Exports</span>
            </a>
        @endif

        @if ($canAuditLogs)
            <a class="nav-item-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"
                id="sidebarAuditLogsLink"
                href="{{ route('audit-logs.index') }}"
                onclick="window.scrollTo({top: 0, left: 0, behavior: 'instant'}); document.documentElement.scrollTop = 0; document.body.scrollTop = 0; const mainEl = document.querySelector('.app-main'); if (mainEl) mainEl.scrollTop = 0;">
                <i class="bi bi-clock-history"></i>
                <span>Audit Logs</span>
            </a>
        @endif

        @if ($canDatabaseCleanup)
            <a class="nav-item-link {{ request()->routeIs('database_cleanup.*') ? 'active' : '' }}"
                href="{{ route('database_cleanup.index') }}">
                <i class="bi bi-database-fill-gear"></i>
                <span>Database Maintenance</span>
            </a>
        @endif

        @if ($canSystemConsole)
            <a class="nav-item-link {{ request()->routeIs('system_console.*') ? 'active' : '' }}"
                href="{{ route('system_console.index') }}">
                <i class="bi bi-terminal-fill text-warning"></i>
                <span>System Console & Tools</span>
            </a>
        @endif
        
    </div>

    <!-- Sidebar Footer / Auth Profile -->
    <div class="sidebar-footer">
        <a href="{{ route('profile') }}" class="user-profile-badge text-decoration-none"
            title="My Profile & Account Settings">
            <div class="user-avatar-initials">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="user-info-text">
                <div class="user-name-title">{{ auth()->user()->name ?? 'User' }}</div>
                <span class="user-role-badge">
                    {{ auth()->user()?->roleModel?->display_name ?? (auth()->user()?->role ? ucfirst(auth()->user()->role) : 'Staff') }}
                </span>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn-sidebar-logout" title="Sign out of panel">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
