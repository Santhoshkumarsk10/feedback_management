<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Error') | Shibaura Machine Feedback</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --brand-primary: #0047ba;
            --brand-navy: #003366;
            --brand-cyan: #0284c7;
            --brand-gradient: linear-gradient(135deg, #003366 0%, #0047ba 60%, #0284c7 100%);
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --bg-canvas: #f8fafc;
            --card-bg: #ffffff;
            --shadow-card: 0 20px 45px -10px rgba(15, 23, 42, 0.12), 0 0 1px 1px rgba(15, 23, 42, 0.05);
            --font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-canvas);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 71, 186, 0.06) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(241, 245, 249, 0.5) 0px, transparent 100%);
        }

        /* ── Top Header Bar ── */
        .error-topbar {
            width: 100%;
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .error-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .error-brand-logo {
            height: 34px;
            width: auto;
            object-fit: contain;
        }

        .error-brand-title {
            display: flex;
            flex-direction: column;
        }

        .error-brand-title span.main-title {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--brand-navy);
            text-transform: uppercase;
        }

        .error-brand-title span.sub-title {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
        }

        .system-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 0.76rem;
            font-weight: 600;
            background: #f1f5f9;
            color: var(--text-muted);
            border: 1px solid #e2e8f0;
        }

        .system-status-pill .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        /* ── Main Error Viewport ── */
        .error-main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .error-card {
            width: 100%;
            max-width: 580px;
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: var(--shadow-card);
            padding: 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: cardFadeUp 0.35s ease-out;
        }

        @keyframes cardFadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-accent-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: @yield('accent_color', 'var(--brand-gradient)');
        }

        /* ── Code Badge ── */
        .error-code-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 24px;
            background: @yield('badge_bg', '#eff6ff');
            color: @yield('badge_color', '#0047ba');
            border: 1px solid @yield('badge_border', '#bfdbfe');
        }

        /* ── Visual Icon Graphic ── */
        .error-icon-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 28px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.75rem;
            background: @yield('icon_bg', 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)');
            color: @yield('icon_color', '#0047ba');
            border: 1px solid @yield('icon_border', '#bfdbfe');
            box-shadow: 0 10px 25px -5px @yield('icon_glow', 'rgba(0, 71, 186, 0.15)');
            position: relative;
        }

        .error-icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 32px;
            border: 1.5px dashed @yield('badge_border', '#bfdbfe');
            opacity: 0.6;
            animation: spinBorder 25s linear infinite;
        }

        @keyframes spinBorder {
            100% { transform: rotate(360deg); }
        }

        /* ── Text Copy ── */
        .error-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            margin-bottom: 12px;
            line-height: 1.25;
        }

        .error-subtitle-tamil {
            font-family: 'Noto Sans Tamil', var(--font-family);
            font-size: 0.95rem;
            font-weight: 600;
            color: #0284c7;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .error-desc {
            font-size: 0.95rem;
            color: var(--text-body);
            line-height: 1.6;
            margin-bottom: 24px;
            max-width: 460px;
            margin-left: auto;
            margin-right: auto;
        }

        .error-tips-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 18px;
            text-align: left;
            margin-bottom: 28px;
            font-size: 0.84rem;
            color: #475569;
            line-height: 1.55;
        }

        .error-tips-box strong {
            color: var(--text-heading);
            display: block;
            margin-bottom: 4px;
            font-size: 0.86rem;
        }

        /* ── Action Buttons ── */
        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 50px;
            padding: 0 24px;
            background: var(--brand-gradient);
            color: #ffffff;
            font-size: 0.96rem;
            font-weight: 700;
            border-radius: 14px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(0, 71, 186, 0.35);
            transition: all 0.2s ease;
        }

        .btn-action-primary:hover, .btn-action-primary:focus {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -4px rgba(0, 71, 186, 0.45);
            color: #ffffff;
        }

        .btn-action-primary:active {
            transform: translateY(0);
        }

        .error-actions-secondary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-action-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 16px;
            background: #ffffff;
            color: var(--text-heading);
            font-size: 0.86rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: var(--brand-navy);
        }

        /* ── Diagnostic Accordion ── */
        .error-diagnostic-details {
            margin-top: 24px;
            text-align: left;
            border-top: 1px dashed #e2e8f0;
            padding-top: 16px;
        }

        .error-diagnostic-details summary {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
            outline: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .error-diagnostic-details summary:hover {
            color: var(--brand-navy);
        }

        .diagnostic-content {
            margin-top: 10px;
            padding: 12px;
            background: #0f172a;
            border-radius: 10px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.74rem;
            color: #94a3b8;
            word-break: break-all;
            line-height: 1.5;
        }

        .diagnostic-content span.highlight {
            color: #38bdf8;
            font-weight: 700;
        }

        /* ── Footer ── */
        .error-footer {
            padding: 20px 24px;
            text-align: center;
            font-size: 0.76rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-light);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-logo {
            height: 18px;
            opacity: 0.8;
        }

        /* ── Responsive Tablet / Mobile Adjustments ── */
        @media (max-width: 640px) {
            .error-card {
                padding: 30px 20px;
                border-radius: 20px;
            }
            .error-title {
                font-size: 1.35rem;
            }
            .error-actions-secondary {
                grid-template-columns: 1fr;
            }
            .error-footer {
                flex-direction: column;
                justify-content: center;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <!-- ── Header Navigation ── -->
    <header class="error-topbar">
        <a href="/visitor" class="error-brand-wrapper">
            <img src="/images/shibaura-logo-crisp.webp" alt="Shibaura Machine" class="error-brand-logo" onerror="this.src='/images/logo.png'">
            <div class="error-brand-title">
                <span class="main-title">Shibaura Machine</span>
                <span class="sub-title">Plant Feedback Operations</span>
            </div>
        </a>

        <div class="system-status-pill">
            <span class="pulse-dot"></span>
            <span>@yield('status_pill', 'Shibaura Portal')</span>
        </div>
    </header>

    <!-- ── Main Error Card ── -->
    <main class="error-main-container">
        <div class="error-card">
            <div class="error-accent-top"></div>

            <div class="error-code-badge">
                <i class="@yield('badge_icon', 'fa-solid fa-triangle-exclamation')"></i>
                <span>@yield('code_badge', 'HTTP Status')</span>
            </div>

            <div class="error-icon-wrapper">
                <i class="@yield('main_icon', 'fa-solid fa-triangle-exclamation')"></i>
            </div>

            <h1 class="error-title">@yield('heading', 'An Error Occurred')</h1>

            @hasSection('heading_tamil')
                <div class="error-subtitle-tamil">@yield('heading_tamil')</div>
            @endif

            <p class="error-desc">@yield('description', 'The requested action could not be processed at this time.')</p>

            @hasSection('tips')
                <div class="error-tips-box">
                    @yield('tips')
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="error-actions">
                <a href="@yield('primary_url', '/visitor')" class="btn-action-primary">
                    <i class="@yield('primary_icon', 'fa-solid fa-house')"></i>
                    <span>@yield('primary_text', 'Return to Feedback Form')</span>
                </a>

                <div class="error-actions-secondary">
                    <button type="button" onclick="@yield('secondary_action', 'window.location.reload()')" class="btn-action-secondary">
                        <i class="@yield('secondary_icon', 'fa-solid fa-arrow-rotate-right')"></i>
                        <span>@yield('secondary_text', 'Reload Page')</span>
                    </button>

                    <a href="@yield('tertiary_url', '/organizer/login')" class="btn-action-secondary">
                        <i class="@yield('tertiary_icon', 'fa-solid fa-user-shield')"></i>
                        <span>@yield('tertiary_text', 'Organizer Login')</span>
                    </a>
                </div>
            </div>

            <!-- Diagnostics Accordion -->
            <div class="error-diagnostic-details">
                <details>
                    <summary>
                        <i class="fa-solid fa-circle-info"></i>
                        <span>System Diagnostics & Reference</span>
                    </summary>
                    <div class="diagnostic-content">
                        <div><strong>Status:</strong> <span class="highlight">@yield('code', '500')</span></div>
                        <div><strong>Path:</strong> {{ request()->path() }}</div>
                        <div><strong>Timestamp:</strong> {{ now()->toIso8601String() }}</div>
                        <div><strong>Client IP:</strong> {{ request()->ip() }}</div>
                        @if(isset($exception) && $exception->getMessage())
                            <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <strong>Message:</strong> {{ $exception->getMessage() }}
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        </div>
    </main>

    <!-- ── Enterprise Footer ── -->
    <footer class="error-footer">
        <div class="footer-left">
            <span>Powered by</span>
            <img src="/images/o9.png" alt="Amoebatronix" class="footer-logo" onerror="this.src='/images/amoebatronix-logo.png'">
            <span>Enterprise Plant Management</span>
        </div>
        <div>
            &copy; {{ date('Y') }} Shibaura Machine Manufacturing India. All rights reserved.
        </div>
    </footer>

</body>
</html>
