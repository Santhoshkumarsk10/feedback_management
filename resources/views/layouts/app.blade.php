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

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
            <!-- Optional Summary Cards Section -->
            @if(View::hasSection('summary_cards'))
                <div class="summary-cards-section mb-4">
                    @yield('summary_cards')
                </div>
            @endif

            <!-- Main Yield -->
            @yield('content')
        </main>

        <!-- Enterprise Admin Panel Footer -->
        @include('layouts.footer')
    </div>
</div>

<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    }

    // Global popup registry: ensures only ONE popup/calendar/dropdown is open at a time across the site
    window.closeAllPopups = function(exceptEl) {
        document.querySelectorAll('[data-custom-select-container].is-open').forEach(el => {
            if (el !== exceptEl && el._closeCustomSelect) el._closeCustomSelect();
        });
        document.querySelectorAll('[data-custom-datepicker-container].is-open').forEach(el => {
            if (el !== exceptEl && el._closeDatePicker) el._closeDatePicker();
        });
        document.querySelectorAll('[data-custom-daterangepicker-container].is-open').forEach(el => {
            if (el !== exceptEl && el._closeDateRangePicker) el._closeDateRangePicker();
        });
        document.querySelectorAll('[data-search-suggest-container]').forEach(el => {
            if (el !== exceptEl && el._closeSearchSuggest) el._closeSearchSuggest();
        });
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (!exceptEl || !exceptEl.contains(menu)) {
                const toggle = menu.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle && window.bootstrap && bootstrap.Dropdown) {
                    const inst = bootstrap.Dropdown.getInstance(toggle);
                    if (inst) inst.hide();
                }
            }
        });
    };

    document.addEventListener('click', function(e) {
        if (!e.target || !e.target.isConnected) return;
        if (!e.target.closest('[data-custom-select-container]') &&
            !e.target.closest('[data-custom-datepicker-container]') &&
            !e.target.closest('[data-custom-daterangepicker-container]') &&
            !e.target.closest('[data-search-suggest-container]') &&
            !e.target.closest('.dropdown')) {
            window.closeAllPopups();
        }
    });

    // Configure SweetAlert2 Global Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    window.Toast = Toast;

    // SweetAlert2 Flash Notifications (Single source of truth - no duplicate alerts)
    @if(session('success'))
        Toast.fire({
            icon: 'success',
            title: {!! json_encode(session('success')) !!}
        });
    @endif

    @if(session('info'))
        Toast.fire({
            icon: 'info',
            title: {!! json_encode(session('info')) !!}
        });
    @endif

    @if(session('warning'))
        Toast.fire({
            icon: 'warning',
            title: {!! json_encode(session('warning')) !!}
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Action Failed',
            text: {!! json_encode(session('error')) !!},
            confirmButtonColor: '#e11d48'
        });
    @endif

    @if($errors->any() && !View::hasSection('hide_top_errors'))
        Swal.fire({
            icon: 'error',
            title: 'Please check the form inputs',
            html: '<ul class="text-start mb-0 ps-3">@foreach($errors->all() as $e)<li>{!! addslashes(e($e)) !!}</li>@endforeach</ul>',
            confirmButtonColor: '#e11d48'
        });
    @endif

    // Global SweetAlert2 Confirmation for Form Actions & Deletions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.dataset.swalBypassed === 'true') {
            return;
        }

        const explicitMsg = form.getAttribute('data-confirm') || form.getAttribute('data-swal-confirm');
        const onsubmitAttr = form.getAttribute('onsubmit') || '';
        let confirmText = explicitMsg;

        if (!confirmText && onsubmitAttr.includes('confirm(')) {
            const match = onsubmitAttr.match(/confirm\((?:'|")?(.*?)(?:'|")?\)/);
            if (match && match[1]) {
                confirmText = match[1].replace(/\\'/g, "'").replace(/\\"/g, '"');
            }
        }

        if (confirmText) {
            e.preventDefault();
            e.stopPropagation();

            const isDelete = form.querySelector('input[name="_method"][value="DELETE"]') !== null ||
                             form.action.includes('destroy') ||
                             form.action.includes('delete');

            Swal.fire({
                title: isDelete ? 'Confirm Deletion' : 'Are you sure?',
                text: confirmText,
                icon: isDelete ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: isDelete ? '#e11d48' : '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: isDelete ? '<i class="bi bi-trash3-fill me-1"></i> Yes, Proceed' : '<i class="bi bi-check2 me-1"></i> Confirm',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.swalBypassed = 'true';
                    form.removeAttribute('onsubmit');
                    form.submit();
                }
            });
        }
    }, true);
</script>
@stack('scripts')
</body>
</html>
