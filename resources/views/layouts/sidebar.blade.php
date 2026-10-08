<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Enterprise Sidebar -->
<aside class="app-sidebar" id="appSidebar">
    <!-- Brand Header with Shibaura Logo -->
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-logo-card">
            <img src="{{ $currentCompany?->logo_url ?? asset('images/shibaura-logo-cropped.webp') }}" alt="{{ $currentCompany?->name ?? 'Shibaura Machine' }}" class="sidebar-logo-img">
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <div class="menu-category">Analytics & Overview</div>
        <a class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        @php
            $currentUser = auth()->user();
            $isAdminOrSuper = $currentUser && ($currentUser->hasAnyRole(['superadmin', 'admin']) || in_array($currentUser->role, ['superadmin', 'admin'], true));
            $isSupervisor = $currentUser && ($currentUser->hasRole('supervisor') || $currentUser->role === 'supervisor');
            $canViewReports = $isAdminOrSuper || $isSupervisor;
            $isStaff = $currentUser && ($currentUser->hasAnyRole(['staff', 'organizer']) || in_array($currentUser->role, ['staff', 'organizer'], true));
        @endphp

        {{-- Masters & Configuration: SuperAdmin & Admin Only --}}
        @if($isAdminOrSuper)
            <div class="menu-category">System Masters</div>
            <a class="nav-item-link {{ request()->routeIs('plants.*') ? 'active' : '' }}" href="{{ route('plants.index') }}">
                <i class="bi bi-buildings-fill"></i>
                <span>Plant Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Role Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                <i class="bi bi-key-fill"></i>
                <span>Permission Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}" href="{{ route('shifts.index') }}">
                <i class="bi bi-clock-history"></i>
                <span>Shift Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-people-fill"></i>
                <span>User Directory</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('questions.*') ? 'active' : '' }}" href="{{ route('questions.index') }}">
                <i class="bi bi-patch-question-fill"></i>
                <span>Feedback Form</span>
            </a>
        @endif

        {{-- Operations: All Authenticated Roles --}}
        <div class="menu-category">Operations & Visits</div>
        <a class="nav-item-link {{ request()->routeIs('visits.*') ? 'active' : '' }}" href="{{ route('visits.index') }}">
            <i class="bi bi-person-badge-fill"></i>
            <span>Plant Visitors</span>
        </a>

        {{-- Brand & CMS: SuperAdmin & Admin Only --}}
        @if($isAdminOrSuper)
            <div class="menu-category">Brand & Content CMS</div>
            <a class="nav-item-link {{ request()->routeIs('company.*') ? 'active' : '' }}" href="{{ route('company.edit') }}">
                <i class="bi bi-building-gear"></i>
                <span>Company Profile</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('banners.*') ? 'active' : '' }}" href="{{ route('banners.index') }}">
                <i class="bi bi-images"></i>
                <span>Banner CMS</span>
            </a>
        @endif

        {{-- Insights & Logs --}}
        <div class="menu-category">Insights & Logs</div>
        <a class="nav-item-link {{ request()->routeIs('feedbacks.*') ? 'active' : '' }}" href="{{ route('feedbacks.index') }}">
            <i class="bi bi-chat-square-heart-fill"></i>
            <span>Feedback Logs</span>
        </a>

        @if($canViewReports)
            <a class="nav-item-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reports & Exports</span>
            </a>
        @endif

        @if($isAdminOrSuper)
            <a class="nav-item-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                <i class="bi bi-clock-history"></i>
                <span>Audit Logs</span>
            </a>
        @endif

        <div class="menu-category">Account Security</div>
        <a class="nav-item-link {{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
            <i class="bi bi-shield-lock-fill text-info"></i>
            <span>Change Password</span>
        </a>
    </div>

    <!-- Sidebar Footer / Auth Profile -->
    <div class="sidebar-footer">
        <a href="{{ route('password.change') }}" class="user-profile-badge text-decoration-none" title="Change Password / Account Settings">
            <div class="user-avatar-initials">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="user-info-text">
                <div class="user-name-title">{{ auth()->user()->name ?? 'User' }}</div>
                <span class="user-role-badge">
                    {{ auth()->user()->roleModel?->display_name ?? (auth()->user()->roles->first()?->display_name ?? ucfirst(auth()->user()->role ?? 'Staff')) }}
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
