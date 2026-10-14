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
        @if(!empty($topbarShiftAlerts))
            @foreach($topbarShiftAlerts as $tbAlert)
                <a href="{{ route('shift_queue', ['today_tab' => 'pending', 'shift_id' => $tbAlert['shift_id']]) }}" 
                   class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 shadow-xs border-danger-subtle fw-bold animate-pulse-subtle" 
                   title="URGENT: {{ $tbAlert['shift_name'] }} closing soon ({{ $tbAlert['remaining_text'] }}) with {{ $tbAlert['pending_count'] }} visitor feedback pending!">
                    <i class="bi bi-alarm-fill text-danger"></i>
                    <span class="d-none d-sm-inline">{{ $tbAlert['shift_name'] }} Alert:</span>
                    <span class="badge bg-danger text-white rounded-pill px-2 py-0">{{ $tbAlert['pending_count'] }} Pending</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-none d-md-inline" style="font-size: 0.72rem;">{{ $tbAlert['remaining_text'] }}</span>
                </a>
            @endforeach
        @endif
        @if(!request()->routeIs('password.change'))
            <a href="{{ route('password.change') }}" class="btn-modern-secondary btn-sm" title="User Profile & Settings">
                <i class="bi bi-person-circle text-info"></i>
                <span class="d-none d-lg-inline">Profile</span>
            </a>
        @endif
        @yield('topbar_actions')
    </div>
</header>
