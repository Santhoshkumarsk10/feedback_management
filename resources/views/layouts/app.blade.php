<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>@yield('title', 'Dashboard') — Plant Feedback Operations</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Typography: Inter, Poppins, Roboto, Noto Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Enterprise Theme -->
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Enterprise Sidebar -->
    <aside class="app-sidebar" id="appSidebar">
        <!-- Brand Header with Shibaura Logo -->
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-logo-card">
                <img src="{{ asset('images/shibaura-logo-cropped.webp') }}" alt="Shibaura Machine" class="sidebar-logo-img">
            </div>
            <div class="sidebar-brand-badge">
                <span class="d-flex align-items-center gap-1">
                    <span class="status-dot active"></span>
                    <span>Plant Operations</span>
                </span>
                <span class="badge-tag">Feedback</span>
            </div>
        </a>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <div class="menu-category">Analytics & Overview</div>
            <a class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-category">System Masters</div>
            <a class="nav-item-link {{ request()->routeIs('plants.*') ? 'active' : '' }}" href="{{ route('plants.index') }}">
                <i class="bi bi-buildings-fill"></i>
                <span>Plant Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Role Master</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-people-fill"></i>
                <span>User Directory</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('questions.*') ? 'active' : '' }}" href="{{ route('questions.index') }}">
                <i class="bi bi-patch-question-fill"></i>
                <span>Feedback Form</span>
            </a>

            <div class="menu-category">Operations & Visits</div>
            <a class="nav-item-link {{ request()->routeIs('visits.*') ? 'active' : '' }}" href="{{ route('visits.index') }}">
                <i class="bi bi-person-badge-fill"></i>
                <span>Plant Visitors</span>
            </a>

            <div class="menu-category">Insights & Logs</div>
            <a class="nav-item-link {{ request()->routeIs('feedbacks.*') ? 'active' : '' }}" href="{{ route('feedbacks.index') }}">
                <i class="bi bi-chat-square-heart-fill"></i>
                <span>Feedback Logs</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reports & Exports</span>
            </a>
            <a class="nav-item-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                <i class="bi bi-clock-history"></i>
                <span>Audit Logs</span>
            </a>
        </div>

        <!-- Sidebar Footer / Auth Profile -->
        <div class="sidebar-footer">
            <div class="user-profile-badge">
                <div class="user-avatar-initials">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="user-info-text">
                    <div class="user-name-title">{{ auth()->user()->name }}</div>
                    <span class="user-role-badge">{{ auth()->user()->role }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn-sidebar-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Layout -->
    <div class="app-main">
        <!-- Top Navigation Bar -->
        <header class="app-topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-nav-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <div class="breadcrumb-container">
                    <h1 class="page-main-title">@yield('page_title', View::getSection('title', 'Overview'))</h1>
                    <p class="page-sub-title">@yield('page_subtitle', 'Plant Feedback Management Portal')</p>
                </div>
            </div>

            <div class="topbar-right">
                <a href="/apk/shibaura-plant-feedback.apk" class="btn-modern-secondary btn-sm" title="Download Android Tablet / Mobile APK">
                    <i class="bi bi-android2 text-success"></i>
                    <span class="d-none d-lg-inline">Tablet APK</span>
                </a>
                <a href="{{ route('mobile.app') }}" target="_blank" class="btn-modern-secondary btn-sm" title="Launch Visitor & Organizer Mobile View">
                    <i class="bi bi-phone-fill text-primary"></i>
                    <span class="d-none d-lg-inline">Mobile App</span>
                </a>
                @yield('topbar_actions')
            </div>
        </header>

        <!-- Page Container -->
        <main class="page-container">
            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="alert-modern success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div class="flex-grow-1 font-medium">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-modern danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold mb-1">Please check the errors below:</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Yield -->
            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    }
</script>
@stack('scripts')
</body>
</html>
