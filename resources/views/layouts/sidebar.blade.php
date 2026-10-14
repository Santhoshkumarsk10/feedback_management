<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Enterprise Modern Clean SaaS Sidebar -->
<aside class="app-sidebar" id="appSidebar">
    <!-- Brand Header -->
    <a href="{{ route('dashboard') }}" class="sidebar-brand-clean text-decoration-none">
        <div class="sidebar-brand-icon">
            <i class="bi bi-star-fill text-white"></i>
        </div>
        <div class="sidebar-brand-text">
            <span class="brand-title">Visitor Feedback</span>
            <span class="brand-subtitle">Management System</span>
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <a class="nav-item-clean {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        @php
            $currentUser = auth()->user();
            $isAdminOrSuper = $currentUser && ($currentUser->hasAnyRole(['superadmin', 'admin']) || in_array($currentUser->role, ['superadmin', 'admin'], true));
            $isSupervisor = $currentUser && ($currentUser->hasRole('supervisor') || $currentUser->role === 'supervisor');
            $canViewReports = $isAdminOrSuper || $isSupervisor;
        @endphp

        <a class="nav-item-clean {{ request()->routeIs('feedbacks.*') ? 'active' : '' }}" href="{{ route('feedbacks.index') }}">
            <i class="bi bi-chat-left-text-fill"></i>
            <span>Visitor Feedback</span>
        </a>

        @if($canViewReports)
            <a class="nav-item-clean {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Feedback Reports</span>
            </a>
        @endif

        <a class="nav-item-clean {{ request()->routeIs('visits.*') ? 'active' : '' }}" href="{{ route('visits.index') }}">
            <i class="bi bi-people-fill"></i>
            <span>Visitors</span>
        </a>

        <a class="nav-item-clean {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-person-badge-fill"></i>
            <span>Hosts</span>
        </a>

        @if($isAdminOrSuper)
            <a class="nav-item-clean {{ request()->routeIs('plants.*') ? 'active' : '' }}" href="{{ route('plants.index') }}">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Locations</span>
            </a>

            <a class="nav-item-clean {{ request()->routeIs('questions.*') ? 'active' : '' }}" href="{{ route('questions.index') }}">
                <i class="bi bi-card-checklist"></i>
                <span>Feedback Categories</span>
            </a>
        @endif

        <a class="nav-item-clean {{ request()->routeIs('shift_queue*') ? 'active' : '' }}" href="{{ route('shift_queue') }}">
            <i class="bi bi-bell-fill"></i>
            <span>Notifications</span>
            @if(($todayCounts['pending'] ?? 0) > 0)
                <span class="badge rounded-pill bg-danger ms-auto" style="font-size: 0.65rem;">{{ $todayCounts['pending'] }}</span>
            @endif
        </a>

        @if($isAdminOrSuper)
            <a class="nav-item-clean {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Users & Roles</span>
            </a>
            <a class="d-none" href="{{ route('permissions.index') }}">Permission Master</a>

            <a class="nav-item-clean {{ request()->routeIs('company.*') || request()->routeIs('banners.*') || request()->routeIs('shifts.*') || request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('company.edit') }}">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
        @else
            <a class="nav-item-clean {{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
        @endif
    </div>

    <!-- Promo / Bottom Illustration Card matching Reference UI -->
    <div class="sidebar-promo-container px-3 pb-3">
        <div class="sidebar-promo-card">
            <div class="promo-illustration mb-2">
                <svg width="68" height="52" viewBox="0 0 68 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Chat bubbles illustration -->
                    <rect x="2" y="4" width="40" height="26" rx="8" fill="#3b82f6" />
                    <path d="M12 30L8 36L18 30H12Z" fill="#3b82f6" />
                    <circle cx="15" cy="17" r="2" fill="white" />
                    <circle cx="22" cy="17" r="2" fill="white" />
                    <circle cx="29" cy="17" r="2" fill="white" />
                    
                    <rect x="28" y="18" width="38" height="24" rx="8" fill="#60a5fa" fill-opacity="0.9" />
                    <path d="M54 42L58 48L48 42H54Z" fill="#60a5fa" fill-opacity="0.9" />
                    <line x1="36" y1="28" x2="58" y2="28" stroke="white" stroke-width="2" stroke-linecap="round" />
                    <line x1="36" y1="34" x2="52" y2="34" stroke="white" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
            <div class="promo-title">Better Visitors<br>Better Experiences</div>
            <div class="promo-subtitle">Collect • Understand • Improve</div>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
            <a href="{{ route('password.change') }}" class="small text-muted text-decoration-none fw-semibold d-flex align-items-center gap-1">
                <i class="bi bi-person-circle"></i>
                <span>{{ \Illuminate\Support\Str::limit(auth()->user()->name ?? 'User', 14) }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Log Out" style="font-size: 0.85rem;">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
