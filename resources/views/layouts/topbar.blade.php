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
        <a href="{{ route('password.change') }}" class="btn-modern-secondary btn-sm" title="Account Security & Change Password">
            <i class="bi bi-shield-lock-fill text-info"></i>
            <span class="d-none d-lg-inline">Security</span>
        </a>
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
