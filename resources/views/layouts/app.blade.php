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
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}?v={{ file_exists(public_path('css/app-theme.css')) ? filemtime(public_path('css/app-theme.css')) : time() }}">
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    <!-- Enterprise Sidebar Partial -->
    @include('layouts.sidebar')

    <!-- Main Content Layout -->
    <div class="app-main">
        <!-- Top Navigation Bar Partial -->
        @include('layouts.topbar')

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
