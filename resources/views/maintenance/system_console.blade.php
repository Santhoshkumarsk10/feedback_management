@extends($isStandalone ? 'layouts.empty' : 'layouts.app')

@section('title', 'System Maintenance & Artisan Console')
@section('page_title', 'System Maintenance')
@section('page_subtitle', 'System Maintenance & Artisan Console — Direct developer diagnostics, performance caching, database schema synchronization, and system tools.')

@push('styles')
<style>
    /* System Maintenance & Artisan Console Dedicated Styles */
    .console-page-wrapper {
        background-color: #f8fafc;
        background-image: radial-gradient(rgba(16, 185, 129, 0.12) 1.5px, transparent 1.5px);
        background-size: 20px 20px;
        min-height: 100vh;
        padding-bottom: 3rem;
    }

    /* Top Console Banner */
    .console-header-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        margin-bottom: 1.5rem;
    }

    .badge-amber-subtle {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-emerald-subtle {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
    }

    .pill-link-btn {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.15s ease-in-out;
    }

    .pill-link-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
    }

    .pill-link-btn.btn-outline-logs {
        border-color: #fca5a5;
        color: #dc2626;
        background: #fff5f5;
    }
    .pill-link-btn.btn-outline-logs:hover {
        background: #fee2e2;
        border-color: #f87171;
    }

    .pill-link-btn.btn-outline-live {
        border-color: #6ee7b7;
        color: #059669;
        background: #f0fdf4;
    }
    .pill-link-btn.btn-outline-live:hover {
        background: #dcfce7;
        border-color: #34d399;
    }

    /* Diagnostics KPI Grid */
    .health-section-header {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }

    .health-icon-radar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #d1fae5;
        color: #059669;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .health-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        height: 100%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .health-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px -2px rgba(15, 23, 42, 0.08);
    }

    .health-kpi-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .health-kpi-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        word-break: break-all;
    }

    /* Action Command Cards */
    .action-cmd-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .action-cmd-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.08);
    }

    .action-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.6rem;
    }

    .action-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .action-card-desc {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.45;
        margin-bottom: 1rem;
        min-height: 42px;
    }

    /* Command CLI Code Box */
    .cli-code-box {
        background: #090d16;
        border: 1px solid #1e293b;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.15rem;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        position: relative;
    }

    .cli-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .cli-badge {
        background: #1e293b;
        color: #94a3b8;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        font-size: 0.6rem;
    }

    .cli-cmd-text {
        font-size: 0.86rem;
        font-weight: 600;
        color: #38bdf8;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
    }

    .cli-cmd-prompt {
        color: #10b981;
    }

    .cli-direct-url {
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        word-break: break-all;
    }

    .cli-direct-url a {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .cli-direct-url a:hover {
        color: #38bdf8;
        text-decoration: underline;
    }

    /* Action Buttons */
    .btn-action-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-amber:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45);
    }

    .btn-action-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-emerald:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    }

    .btn-action-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-blue:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
    }

    .btn-action-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(109, 40, 217, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-purple:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(109, 40, 217, 0.45);
    }

    .btn-action-cyan {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(8, 145, 178, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-cyan:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(8, 145, 178, 0.45);
    }

    .btn-action-slate {
        background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(30, 41, 59, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-slate:hover {
        background: linear-gradient(135deg, #334155 0%, #0f172a 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(30, 41, 59, 0.45);
    }

    .btn-action-rose {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .btn-action-rose:hover {
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(225, 29, 72, 0.45);
    }

    /* Terminal Output Box */
    .terminal-window {
        background: #090d16;
        border: 1px solid #1e293b;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        margin-top: 2rem;
    }

    .terminal-titlebar {
        background: #111827;
        padding: 0.65rem 1rem;
        border-bottom: 1px solid #1f2937;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .terminal-dots {
        display: flex;
        gap: 0.4rem;
    }

    .terminal-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
    }
    .terminal-dot.red { background: #ef4444; }
    .terminal-dot.yellow { background: #f59e0b; }
    .terminal-dot.green { background: #10b981; }

    .terminal-title-text {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 0.78rem;
        color: #94a3b8;
    }

    .terminal-body {
        padding: 1.25rem;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.85rem;
        color: #f1f5f9;
        line-height: 1.6;
        max-height: 420px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .terminal-body .cmd-prompt {
        color: #10b981;
        font-weight: 700;
    }
    .terminal-body .cmd-name {
        color: #38bdf8;
        font-weight: 700;
    }
    .terminal-body .cmd-meta {
        color: #64748b;
        font-size: 0.75rem;
    }

    /* Custom Confirmation Modal */
    .modal-confirm-icon-bubble {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        margin-bottom: 1.25rem;
    }
    .modal-confirm-icon-bubble.amber {
        background: #fef3c7;
        color: #d97706;
        border: 3px solid #fde68a;
    }
    .modal-confirm-icon-bubble.emerald {
        background: #d1fae5;
        color: #059669;
        border: 3px solid #a7f3d0;
    }
    .modal-confirm-icon-bubble.blue {
        background: #dbeafe;
        color: #2563eb;
        border: 3px solid #bfdbfe;
    }
    .modal-confirm-icon-bubble.rose,
    .modal-confirm-icon-bubble.danger {
        background: #ffe4e6;
        color: #e11d48;
        border: 3px solid #fecdd3;
    }
    .modal-confirm-icon-bubble.purple {
        background: #f3e8ff;
        color: #7e22ce;
        border: 3px solid #e9d5ff;
    }
    .modal-confirm-icon-bubble.cyan {
        background: #cffafe;
        color: #0891b2;
        border: 3px solid #a5f3fc;
    }

    /* Custom Floating Toasts */
    #customToastContainer {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        pointer-events: none;
        max-width: 400px;
    }

    .custom-toast {
        pointer-events: auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.95rem 1.2rem;
        box-shadow: 0 12px 28px -5px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        transform: translateY(-20px);
        opacity: 0;
        transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .custom-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .custom-toast-icon {
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .custom-toast.success .custom-toast-icon { color: #10b981; }
    .custom-toast.danger .custom-toast-icon { color: #f43f5e; }
    .custom-toast.warning .custom-toast-icon { color: #f59e0b; }
    .custom-toast.info .custom-toast-icon { color: #0284c7; }

    .custom-toast-body {
        flex-grow: 1;
        font-size: 0.88rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.4;
    }

    .custom-toast-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.15rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: color 0.15s ease;
    }
    .custom-toast-close:hover {
        color: #334155;
    }
</style>
@endpush

@section('content')
<div class="console-page-wrapper">
    <div class="container-fluid px-3 px-lg-4 py-3">

        <!-- Top Header Card (SMM Console / Direct Tool Access) -->
        <div class="console-header-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fs-4">⚙️</span>
                    <h2 class="h5 mb-0 fw-bold text-dark">{{ config('app.name', 'Shibaura Plant Feedback') }}</h2>
                    <span class="badge-amber-subtle">System Console</span>
                    <span class="badge-emerald-subtle">Direct Tool Access</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold" style="font-size: 0.8rem;">
                        PHP {{ $metrics['php_version'] }} · Laravel v{{ $metrics['laravel_version'] }}
                    </span>
                    <button type="button" class="pill-link-btn btn-outline-logs" onclick="openRuntimeLogsModal()">
                        <i class="bi bi-file-earmark-text"></i> Runtime Logs
                    </button>
                    <a href="{{ url('/') }}" target="_blank" class="pill-link-btn btn-outline-live">
                        <i class="bi bi-box-arrow-up-right"></i> Live Feedback Portal
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="pill-link-btn">
                            <i class="bi bi-shield-check text-primary"></i> Admin Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="pill-link-btn">
                            <i class="bi bi-shield-lock text-primary"></i> Admin Login
                        </a>
                    @endauth
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                <div>
                    <h1 class="h4 mb-1 fw-extrabold text-dark d-flex align-items-center gap-2">
                        <span class="text-success font-monospace">&gt;_</span> System Maintenance &amp; Artisan Console
                    </h1>
                    <p class="text-muted small mb-0">
                        Direct developer diagnostics, performance caching, database schema synchronization, and system tools.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="pill-link-btn btn-outline-logs px-3 py-2" onclick="openRuntimeLogsModal()">
                        <i class="bi bi-file-earmark-code"></i> View System Logs ({{ $metrics['log_size'] }})
                    </button>
                    <a href="{{ url('/') }}" target="_blank" class="pill-link-btn btn-outline-live px-3 py-2">
                        <i class="bi bi-globe"></i> View Live Website
                    </a>
                </div>
            </div>
        </div>

        <!-- Section: Server Environment & Application Health -->
        <div class="mb-4">
            <div class="health-section-header">
                <div class="health-icon-radar">
                    <i class="bi bi-bullseye"></i>
                </div>
                <span>Server Environment &amp; Application Health</span>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">PHP VERSION</div>
                        <div class="health-kpi-value">{{ $metrics['php_version'] }}</div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">LARAVEL FRAMEWORK</div>
                        <div class="health-kpi-value text-slate-700">v{{ $metrics['laravel_version'] }}</div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">APP ENVIRONMENT</div>
                        <div class="health-kpi-value text-primary">{{ $metrics['app_env'] }}</div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">DEBUG MODE</div>
                        <div class="health-kpi-value {{ $metrics['app_debug'] ? 'text-warning' : 'text-success' }}">
                            {{ $metrics['app_debug'] ? 'Enabled (ON)' : 'Disabled (OFF)' }}
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">DATABASE DRIVER</div>
                        <div class="health-kpi-value text-dark">{{ $metrics['db_connection'] }} <small class="text-muted fs-6 fw-normal">({{ $metrics['db_name'] }})</small></div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">CACHE / SESSION</div>
                        <div class="health-kpi-value text-dark">{{ $metrics['cache_driver'] }} / {{ $metrics['session_driver'] }}</div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">STORAGE PERMISSIONS</div>
                        <div class="health-kpi-value {{ $metrics['storage_writable'] ? 'text-success' : 'text-danger' }}">
                            {{ $metrics['storage_permissions'] }}
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <div class="health-kpi-card">
                        <div class="health-kpi-label">CURRENT LOG SIZE</div>
                        <div class="health-kpi-value text-dark">{{ $metrics['log_size'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Interactive Artisan Command Cards -->
        <div class="row g-4 mb-4">

            <!-- 1. Clear All Caches -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🗑️</span> 1. Clear All Caches
                            </h3>
                            <span class="badge-amber-subtle">Fast Reset</span>
                        </div>
                        <p class="action-card-desc">
                            Clears all compiled Blade templates, configuration cache, application cache keys, compiled events, and route cache files.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan optimize:clear
                            </div>
                            <div class="cli-direct-url">
                                <span>Direct URL:</span>
                                <a href="{{ route('system_console.direct_clear_cache') }}" target="_blank">
                                    {{ route('system_console.direct_clear_cache') }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-amber" onclick="triggerCommand('optimize:clear', 'Clear All Caches', this)">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>Clear All Caches Now</span>
                    </button>
                </div>
            </div>

            <!-- 2. Optimize & Cache Application -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🌱</span> 2. Optimize &amp; Cache Application
                            </h3>
                            <span class="badge-emerald-subtle">Production Speed</span>
                        </div>
                        <p class="action-card-desc">
                            Pre-compiles routes, configuration files, and blade templates into static PHP files for maximum production speed and fastest response times.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan optimize
                            </div>
                            <div class="cli-direct-url">
                                <span>Direct URL:</span>
                                <a href="{{ route('system_console.direct_optimize') }}" target="_blank">
                                    {{ route('system_console.direct_optimize') }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-emerald" onclick="triggerCommand('optimize', 'Optimize Application', this)">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        <span>Optimize &amp; Cache Now</span>
                    </button>
                </div>
            </div>

            <!-- 3. Run Database Migrations -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🗄️</span> 3. Run Database Migrations
                            </h3>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Schema Sync</span>
                        </div>
                        <p class="action-card-desc">
                            Executes any pending database schema migrations safely using <code class="text-danger">--force</code> mode without requiring terminal SSH access.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan migrate --force
                            </div>
                            <div class="cli-direct-url">
                                <span>Direct URL:</span>
                                <a href="{{ route('system_console.direct_migrate') }}" target="_blank">
                                    {{ route('system_console.direct_migrate') }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-blue" onclick="triggerCommand('migrate', 'Execute Database Migrations', this)">
                        <i class="bi bi-database-fill-gear"></i>
                        <span>Execute Migrations Now</span>
                    </button>
                </div>
            </div>

            <!-- 4. Run Database Seeders -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>⚙️</span> 4. Run Database Seeders
                            </h3>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Data Population</span>
                        </div>
                        <p class="action-card-desc">
                            Seeds default plant masters, questionnaire items, roles, RBAC permissions, and demo credentials into the database.
                        </p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted mb-1">Choose Seeder Class:</label>
                            <select id="seederSelector" class="form-select form-select-sm border-secondary-subtle">
                                @foreach($seeders as $val => $info)
                                    <option value="{{ $val }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan db:seed --force
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-amber" onclick="triggerSeederCommand(this)">
                        <i class="bi bi-database-fill-add"></i>
                        <span>Run Database Seeders</span>
                    </button>
                </div>
            </div>

            <!-- 5. Seed Today's Plant Visits (Special Project Feature) -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>👥</span> 5. Seed Today's Plant Visits
                            </h3>
                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem; background: #f3e8ff; color: #7e22ce;">Daily Queue</span>
                        </div>
                        <p class="action-card-desc">
                            Seeds exactly 10 realistic, unique plant visitor records for today with unassigned host organizers into the shift queue.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan plant:seed-daily-visits
                            </div>
                            <div class="cli-direct-url">
                                <span>Direct URL:</span>
                                <a href="{{ route('system_console.direct_seed_daily') }}" target="_blank">
                                    {{ route('system_console.direct_seed_daily') }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-purple" onclick="triggerCommand('plant:seed-daily-visits', 'Seed Daily Plant Visits', this)">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Seed Today's Visits Now</span>
                    </button>
                </div>
            </div>

            <!-- 6. Sync External 3rd-Party Visitors (Special Project Feature) -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🔄</span> 6. Sync External 3rd-Party Visitors
                            </h3>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">API Integration</span>
                        </div>
                        <p class="action-card-desc">
                            Poll &amp; synchronizes plant visitor data from external 3rd-party API into visitor records and today's shift queue.
                        </p>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Target Date (Optional):</label>
                                <input type="date" id="syncDateInput" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Default Organizer:</label>
                                <select id="syncOrganizerSelect" class="form-select form-select-sm">
                                    <option value="">Auto / Unassigned</option>
                                    @foreach($coordinators as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan visitors:sync-external
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-cyan" onclick="triggerSyncCommand(this)">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                        <span>Sync External Visitors Now</span>
                    </button>
                </div>
            </div>

            <!-- 7. Create Storage Symlink -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🔗</span> 7. Link Public Storage
                            </h3>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Asset Access</span>
                        </div>
                        <p class="action-card-desc">
                            Creates or fixes the <code class="text-dark">storage/app/public</code> to <code class="text-dark">public/storage</code> symbolic link for company logos and promotional banners.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>CONSOLE COMMAND</span>
                                <span class="cli-badge">CLI</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">$</span> php artisan storage:link
                            </div>
                            <div class="cli-direct-url">
                                <span>Direct URL:</span>
                                <a href="{{ route('system_console.direct_storage_link') }}" target="_blank">
                                    {{ route('system_console.direct_storage_link') }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action-slate" onclick="triggerCommand('storage:link', 'Storage Symlink', this)">
                        <i class="bi bi-link-45deg"></i>
                        <span>Create Storage Symlink</span>
                    </button>
                </div>
            </div>

            <!-- 8. Database Table Data Cleanup -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>🧹</span> 8. Database Table Data Cleanup
                            </h3>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Selective Wipe</span>
                        </div>
                        <p class="action-card-desc">
                            Safely clear unshielded operational data tables (visits, feedbacks, answers) with strict protection on auth, users, roles, and audit schemas.
                        </p>

                        <div class="cli-code-box">
                            <div class="cli-box-header">
                                <span>DIRECT ACCESS</span>
                                <span class="cli-badge text-warning">RESTRICTED</span>
                            </div>
                            <div class="cli-cmd-text">
                                <span class="cli-cmd-prompt">&gt;</span> /admin/database-cleanup
                            </div>
                            <div class="cli-direct-url">
                                <span>Destination:</span>
                                <a href="{{ route('database_cleanup.index') }}">
                                    Database Maintenance Manager <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('database_cleanup.index') }}" class="btn-action-rose text-decoration-none">
                        <i class="bi bi-shield-slash-fill"></i>
                        <span>Open Table Cleanup Manager</span>
                    </a>
                </div>
            </div>

            <!-- 9. Granular Cache Controls -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>⚡</span> 9. Component Cache Controls
                            </h3>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Granular Reset</span>
                        </div>
                        <p class="action-card-desc">
                            Clear or rebuild individual application caches independently for precise troubleshooting.
                        </p>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-sm fw-semibold" onclick="triggerCommand('config:cache', 'Config Cache', this)">
                                    <i class="bi bi-gear-fill me-1"></i> Config:Cache
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-danger w-100 btn-sm fw-semibold" onclick="triggerCommand('config:clear', 'Config Clear', this)">
                                    <i class="bi bi-x-circle me-1"></i> Config:Clear
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-sm fw-semibold" onclick="triggerCommand('route:cache', 'Route Cache', this)">
                                    <i class="bi bi-signpost-split-fill me-1"></i> Route:Cache
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-danger w-100 btn-sm fw-semibold" onclick="triggerCommand('route:clear', 'Route Clear', this)">
                                    <i class="bi bi-x-circle me-1"></i> Route:Clear
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-sm fw-semibold" onclick="triggerCommand('view:cache', 'View Cache', this)">
                                    <i class="bi bi-file-earmark-code me-1"></i> View:Cache
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-danger w-100 btn-sm fw-semibold" onclick="triggerCommand('view:clear', 'View Clear', this)">
                                    <i class="bi bi-x-circle me-1"></i> View:Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-warning w-100 fw-bold py-2 rounded-3" onclick="triggerCommand('cache:clear', 'Flush Cache Store', this)">
                        <i class="bi bi-trash3-fill me-1"></i> Flush App Key-Value Cache
                    </button>
                </div>
            </div>

            <!-- 10. Runtime System Logs & Maintenance Mode -->
            <div class="col-12 col-lg-6">
                <div class="action-cmd-card">
                    <div>
                        <div class="action-card-header">
                            <h3 class="action-card-title">
                                <span>📜</span> 10. System Logs &amp; Site Mode
                            </h3>
                            <span class="badge {{ $metrics['is_maintenance'] ? 'bg-danger text-white' : 'bg-success-subtle text-success' }} border rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                                {{ $metrics['is_maintenance'] ? 'MAINTENANCE MODE' : 'APP LIVE' }}
                            </span>
                        </div>
                        <p class="action-card-desc">
                            View or clear runtime log records (<code class="text-dark">{{ $metrics['log_file_name'] }}</code> - {{ $metrics['log_size'] }}) and toggle maintenance mode.
                        </p>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm fw-semibold flex-grow-1" onclick="openRuntimeLogsModal()">
                                <i class="bi bi-eye-fill me-1"></i> Inspect Logs
                            </button>
                            <a href="{{ route('system_console.download_logs') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="confirmClearLogs()">
                                <i class="bi bi-trash-fill me-1"></i> Clear Log
                            </button>
                        </div>
                    </div>

                    <form id="maintenanceToggleForm" method="POST" action="{{ route('system_console.maintenance_toggle') }}" class="m-0" onsubmit="event.preventDefault(); confirmMaintenanceToggle();">
                        @csrf
                        @if($metrics['is_maintenance'])
                            <button type="submit" class="btn-action-emerald">
                                <i class="bi bi-play-circle-fill"></i>
                                <span>Bring Application LIVE (Artisan Up)</span>
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-danger w-100 fw-bold py-2 rounded-3">
                                <i class="bi bi-pause-circle-fill me-1"></i>
                                <span>Place into Maintenance Mode (Artisan Down)</span>
                            </button>
                        @endif
                    </form>
                </div>
            </div>

        </div>

        <!-- Section: Interactive Live Execution Terminal -->
        <div class="terminal-window" id="terminalSection">
            <div class="terminal-titlebar">
                <div class="terminal-dots">
                    <span class="terminal-dot red"></span>
                    <span class="terminal-dot yellow"></span>
                    <span class="terminal-dot green"></span>
                </div>
                <div class="terminal-title-text" id="terminalStatusTitle">
                    bash — shibaura-console: ~ [Ready]
                </div>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-secondary text-light py-0 px-2 rounded" onclick="copyTerminalOutput()" title="Copy output">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary text-light py-0 px-2 rounded ms-1" onclick="clearTerminalOutput()" title="Clear terminal">
                        <i class="bi bi-slash-circle"></i> Clear
                    </button>
                </div>
            </div>
            <div class="terminal-body" id="terminalBody">
@if(session('command_output'))
<span class="cmd-prompt">shibaura-console:~$</span> <span class="cmd-name">{{ session('command_name', 'php artisan') }}</span>
<span class="cmd-meta">Execution finished in {{ session('command_duration', '0') }}ms</span>

{{ session('command_output') }}
@else
<span class="cmd-prompt">shibaura-console:~$</span> <span class="cmd-name">system --diagnostics</span>
<span class="cmd-meta">System ready. Click any command action card above to execute Artisan commands live.</span>

[System Status]
  PHP Version:         {{ $metrics['php_version'] }}
  Laravel Version:     v{{ $metrics['laravel_version'] }}
  Environment:         {{ $metrics['app_env'] }}
  Database Connected:  {{ $metrics['db_connected'] ? 'YES (' . $metrics['db_connection'] . ')' : 'NO' }}
  Total Visitors:      {{ number_format($metrics['counts']['visits']) }}
  Total Feedbacks:     {{ number_format($metrics['counts']['feedbacks']) }}
@endif
            </div>
        </div>

    </div>
</div>

<!-- Modal: Runtime Logs Viewer -->
<div class="modal fade" id="runtimeLogsModal" tabindex="-1" aria-labelledby="runtimeLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title h6 fw-bold mb-0 d-flex align-items-center gap-2" id="runtimeLogsModalLabel">
                    <i class="bi bi-file-earmark-text text-danger"></i>
                    <span>Runtime System Logs Viewer</span>
                    <span class="badge bg-secondary text-white font-monospace ms-2" id="logModalFileSize">0 KB</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark">
                <div class="p-2 bg-black border-bottom border-secondary d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                        <input type="text" id="logSearchInput" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Filter log text..." onkeyup="filterLogLines()">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="fetchLatestLogs()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <a href="{{ route('system_console.download_logs') }}" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmClearLogs()">
                            <i class="bi bi-trash-fill"></i> Clear Logs
                        </button>
                    </div>
                </div>
                <div id="logContentPre" class="p-3 text-light font-monospace small" style="max-height: 550px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;">
                    Loading system logs...
                </div>
            </div>
            <div class="modal-footer bg-dark border-top border-secondary py-2">
                <span class="small text-muted me-auto font-monospace" id="logModalFilePath">storage/logs/laravel.log</span>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-labelledby="customConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-body text-center p-4 p-md-5">
                <div id="confirmModalIconWrapper" class="modal-confirm-icon-bubble emerald">
                    <i id="confirmModalIcon" class="bi bi-terminal-fill"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2" id="confirmModalTitle">Execute Command</h4>
                <p class="text-muted small mb-3" id="confirmModalMessage">
                    Are you sure you want to run this Artisan command? System operations will execute immediately.
                </p>

                <div class="cli-code-box text-start mb-4 py-2 px-3" id="confirmModalCmdBox" style="background: #090d16; border-radius: 10px;">
                    <div class="cli-box-header text-muted mb-1" style="font-size: 0.65rem;">CONSOLE TARGET</div>
                    <div class="cli-cmd-text font-monospace fw-bold text-info small" id="confirmModalCmdText">
                        $ php artisan optimize:clear
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="confirmModalProceedBtn" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm">
                        Yes, Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Floating Toast Container -->
<div id="customToastContainer"></div>

@push('scripts')
<script>
    const executeUrl = "{{ route('system_console.execute') }}";
    const logsUrl = "{{ route('system_console.logs') }}";
    const clearLogsUrl = "{{ route('system_console.clear_logs') }}";
    const csrfToken = "{{ csrf_token() }}";

    // Custom Confirmation Dialog using Promise
    let confirmResolve = null;
    function showCustomConfirm({ title, message, command, confirmBtnText = 'Yes, Proceed', theme = 'emerald', icon = 'bi-terminal-fill' }) {
        return new Promise((resolve) => {
            confirmResolve = resolve;

            document.getElementById('confirmModalTitle').innerText = title;
            document.getElementById('confirmModalMessage').innerText = message;
            document.getElementById('confirmModalCmdText').innerText = command ? `$ ${command}` : '';
            document.getElementById('confirmModalCmdBox').style.display = command ? 'block' : 'none';

            const proceedBtn = document.getElementById('confirmModalProceedBtn');
            proceedBtn.innerText = confirmBtnText;

            // Apply theme classes
            proceedBtn.className = 'btn px-4 py-2 rounded-pill fw-bold shadow-sm ';
            if (theme === 'amber') {
                proceedBtn.className += 'btn-warning text-dark';
            } else if (theme === 'rose' || theme === 'danger') {
                proceedBtn.className += 'btn-danger text-white';
            } else if (theme === 'blue') {
                proceedBtn.className += 'btn-primary text-white';
            } else if (theme === 'purple') {
                proceedBtn.className += 'btn-primary text-white';
                proceedBtn.style.background = '#7e22ce';
                proceedBtn.style.borderColor = '#7e22ce';
            } else if (theme === 'cyan') {
                proceedBtn.className += 'btn-info text-white';
            } else {
                proceedBtn.className += 'btn-success text-white';
                proceedBtn.style.background = '';
                proceedBtn.style.borderColor = '';
            }

            const iconWrapper = document.getElementById('confirmModalIconWrapper');
            iconWrapper.className = `modal-confirm-icon-bubble ${theme}`;
            document.getElementById('confirmModalIcon').className = `bi ${icon}`;

            const modalEl = document.getElementById('customConfirmModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    }

    document.getElementById('confirmModalProceedBtn').addEventListener('click', function() {
        const modalEl = document.getElementById('customConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        if (confirmResolve) {
            confirmResolve(true);
            confirmResolve = null;
        }
    });

    document.getElementById('customConfirmModal').addEventListener('hidden.bs.modal', function() {
        if (confirmResolve) {
            confirmResolve(false);
            confirmResolve = null;
        }
    });

    // Custom Toast Notification System
    function showCustomToast(message, type = 'success', icon = null) {
        const container = document.getElementById('customToastContainer');
        if (!container) return;

        if (!icon) {
            if (type === 'success') icon = 'bi-check-circle-fill';
            else if (type === 'danger') icon = 'bi-x-circle-fill';
            else if (type === 'warning') icon = 'bi-exclamation-triangle-fill';
            else icon = 'bi-info-circle-fill';
        }

        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `
            <i class="bi ${icon} custom-toast-icon"></i>
            <div class="custom-toast-body">${escapeHtml(message)}</div>
            <button type="button" class="custom-toast-close" aria-label="Close">&times;</button>
        `;

        toast.querySelector('.custom-toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 250);
        });

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        setTimeout(() => {
            if (toast.isConnected) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 250);
            }
        }, 3500);
    }

    // Trigger an artisan command via AJAX with custom confirmation
    async function triggerCommand(commandName, displayName, btnElement, extraParams = {}) {
        let theme = 'emerald';
        let icon = 'bi-terminal-fill';

        if (commandName.includes('clear')) {
            theme = 'amber';
            icon = 'bi-lightning-charge-fill';
        } else if (commandName.includes('migrate')) {
            theme = 'blue';
            icon = 'bi-database-fill-gear';
        } else if (commandName.includes('seed')) {
            theme = commandName.includes('daily') ? 'purple' : 'amber';
            icon = commandName.includes('daily') ? 'bi-person-plus-fill' : 'bi-database-fill-add';
        } else if (commandName.includes('sync')) {
            theme = 'cyan';
            icon = 'bi-cloud-arrow-down-fill';
        } else if (commandName.includes('storage')) {
            theme = 'blue';
            icon = 'bi-link-45deg';
        }

        const confirmed = await showCustomConfirm({
            title: `Execute: ${displayName}`,
            message: 'Are you sure you want to run this Artisan command? System operations will execute immediately.',
            command: `php artisan ${commandName}`,
            confirmBtnText: 'Execute Command',
            theme: theme,
            icon: icon
        });

        if (!confirmed) {
            return;
        }

        const terminalTitle = document.getElementById('terminalStatusTitle');
        const terminalBody = document.getElementById('terminalBody');
        const originalBtnHtml = btnElement ? btnElement.innerHTML : '';

        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Executing ${displayName}...`;
        }

        terminalTitle.innerHTML = `<span class="spinner-border spinner-border-sm text-warning me-1"></span> Executing: php artisan ${commandName}...`;

        const payload = Object.assign({
            command: commandName,
            _token: csrfToken
        }, extraParams);

        fetch(executeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            const timeStr = new Date().toLocaleTimeString();
            const exitBadge = data.exit_code === 0 ? '<span class="text-success">[SUCCESS: 0]</span>' : '<span class="text-danger">[FAILED: ' + data.exit_code + ']</span>';

            const outputBlock = `\n<span class="cmd-prompt">shibaura-console:~$</span> <span class="cmd-name">${data.command}</span>\n<span class="cmd-meta">[${timeStr}] ${exitBadge} Executed in ${data.duration_ms}ms</span>\n\n${escapeHtml(data.output)}\n`;

            terminalBody.innerHTML += outputBlock;
            terminalBody.scrollTop = terminalBody.scrollHeight;
            terminalTitle.innerHTML = `bash — shibaura-console: ~ [Finished ${data.command} in ${data.duration_ms}ms]`;

            // Scroll terminal into view
            document.getElementById('terminalSection').scrollIntoView({ behavior: 'smooth' });

            if (data.success) {
                showCustomToast(`Command '${data.command}' executed successfully! (${data.duration_ms}ms)`, 'success', 'bi-check-circle-fill');
            } else {
                showCustomToast(`Command '${data.command}' failed with exit code ${data.exit_code}.`, 'danger', 'bi-x-circle-fill');
            }
        })
        .catch(err => {
            terminalBody.innerHTML += `\n<span class="text-danger">[ERROR] Request failed: ${escapeHtml(err.message)}</span>\n`;
            terminalTitle.innerHTML = `bash — shibaura-console: ~ [Failed]`;
            showCustomToast('Execution request failed: ' + err.message, 'danger', 'bi-exclamation-triangle-fill');
        })
        .finally(() => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalBtnHtml;
            }
        });
    }

    // Trigger Seeder
    function triggerSeederCommand(btnElement) {
        const seeder = document.getElementById('seederSelector').value;
        triggerCommand('db:seed', 'Database Seeder', btnElement, { seeder: seeder });
    }

    // Trigger External Visitor Sync
    function triggerSyncCommand(btnElement) {
        const syncDate = document.getElementById('syncDateInput').value;
        const organizerId = document.getElementById('syncOrganizerSelect').value;
        triggerCommand('visitors:sync-external', 'Sync External Visitors', btnElement, {
            sync_date: syncDate,
            organizer_id: organizerId
        });
    }

    // Copy Terminal Output with custom toast
    function copyTerminalOutput() {
        const text = document.getElementById('terminalBody').innerText;
        navigator.clipboard.writeText(text).then(() => {
            showCustomToast('Terminal output copied to clipboard!', 'success', 'bi-clipboard-check');
        }).catch(() => {
            showCustomToast('Could not copy output to clipboard.', 'warning', 'bi-exclamation-circle');
        });
    }

    // Clear Terminal Output
    function clearTerminalOutput() {
        document.getElementById('terminalBody').innerHTML = '<span class="cmd-prompt">shibaura-console:~$</span> <span class="cmd-meta">Terminal cleared. Ready for next command.</span>\n';
        document.getElementById('terminalStatusTitle').innerText = 'bash — shibaura-console: ~ [Ready]';
        showCustomToast('Terminal cleared', 'info', 'bi-slash-circle');
    }

    // Confirm Maintenance Toggle
    async function confirmMaintenanceToggle() {
        const isMaintenance = {{ $metrics['is_maintenance'] ? 'true' : 'false' }};
        const actionTitle = isMaintenance ? 'Bring Application LIVE' : 'Enable Maintenance Mode';
        const actionMessage = isMaintenance
            ? 'Are you sure you want to bring the application live for all public visitors and users?'
            : 'Are you sure you want to place the site into maintenance mode? Regular visitors will see a 503 maintenance screen.';

        const confirmed = await showCustomConfirm({
            title: actionTitle,
            message: actionMessage,
            command: isMaintenance ? 'php artisan up' : 'php artisan down',
            confirmBtnText: isMaintenance ? 'Bring Live Now' : 'Place Down Now',
            theme: isMaintenance ? 'emerald' : 'rose',
            icon: isMaintenance ? 'bi-play-circle-fill' : 'bi-pause-circle-fill'
        });

        if (confirmed) {
            document.getElementById('maintenanceToggleForm').submit();
        }
    }

    // Open Runtime Logs Modal
    let fullLogText = '';
    function openRuntimeLogsModal() {
        const modal = new bootstrap.Modal(document.getElementById('runtimeLogsModal'));
        modal.show();
        fetchLatestLogs();
    }

    function fetchLatestLogs() {
        const logPre = document.getElementById('logContentPre');
        logPre.innerText = 'Fetching latest system log lines...';

        fetch(logsUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(d => {
            fullLogText = d.content || 'Log file is currently empty.';
            logPre.innerText = fullLogText;
            document.getElementById('logModalFileSize').innerText = d.size;
            document.getElementById('logModalFilePath').innerText = d.path;
            logPre.scrollTop = logPre.scrollHeight;
        })
        .catch(err => {
            logPre.innerText = 'Failed to load logs: ' + err.message;
            showCustomToast('Failed to load logs: ' + err.message, 'danger');
        });
    }

    function filterLogLines() {
        const q = document.getElementById('logSearchInput').value.toLowerCase();
        if (!q) {
            document.getElementById('logContentPre').innerText = fullLogText;
            return;
        }

        const lines = fullLogText.split('\n');
        const filtered = lines.filter(l => l.toLowerCase().includes(q));
        document.getElementById('logContentPre').innerText = filtered.join('\n');
    }

    // Clear logs with custom confirm & toast
    async function confirmClearLogs() {
        const confirmed = await showCustomConfirm({
            title: 'Flush System Log File',
            message: 'Are you sure you want to completely empty the active system log file? This cannot be undone.',
            command: 'rm storage/logs/laravel.log',
            confirmBtnText: 'Flush Log File',
            theme: 'rose',
            icon: 'bi-trash3-fill'
        });

        if (!confirmed) {
            return;
        }

        fetch(clearLogsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(d => {
            showCustomToast(d.message || 'Log file emptied successfully.', 'success', 'bi-check-circle-fill');
            fetchLatestLogs();
        })
        .catch(err => {
            showCustomToast('Failed to clear logs: ' + err.message, 'danger');
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
</script>
@endpush
@endsection
