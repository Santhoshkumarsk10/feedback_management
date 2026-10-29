<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Shibaura Machine Plant Feedback</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand-navy: #0b305b;
            --brand-blue: #0b4d9c;
            --brand-blue-hover: #083c7a;
            --slate-muted: #9ab3ce;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-light: #d1d5db;
            --bg-page: #edf2f7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-page);
            min-height: 100vh;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            margin: 0;
        }

        .login-card {
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 16px 36px -8px rgba(11, 48, 91, 0.16), 0 2px 8px rgba(11, 48, 91, 0.06);
            overflow: hidden;
            display: flex;
            min-height: 520px;
        }

        /* Left Hero Column */
        .login-hero {
            background: linear-gradient(165deg, #0b305b 0%, #082447 60%, #061c38 100%);
            width: 44%;
            padding: 2.5rem 2.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            user-select: none;
            overflow: hidden;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, rgba(56, 189, 248, 0) 70%);
            pointer-events: none;
        }

        .brand-header {
            margin-bottom: 0.5rem;
        }

        .brand-logo-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 7px 16px;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
        }

        .brand-logo-card img {
            height: 28px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        /* Hero Graphic Illustration */
        .hero-illustration-wrapper {
            margin: 1.15rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-illustration-svg {
            width: 100%;
            max-width: 320px;
            height: auto;
            display: block;
            filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.25));
        }

        /* Hero Feature Highlights */
        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            margin: 0.25rem 0 1.25rem 0;
        }

        .hero-feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
        }

        .hero-feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(56, 189, 248, 0.35);
            transform: translateX(2px);
        }

        .hero-feature-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .hero-feature-icon.icon-cyan {
            background: rgba(56, 189, 248, 0.16);
            color: #38bdf8;
        }

        .hero-feature-icon.icon-amber {
            background: rgba(245, 158, 11, 0.16);
            color: #fbbf24;
        }

        .hero-feature-icon.icon-emerald {
            background: rgba(16, 185, 129, 0.16);
            color: #34d399;
        }

        .hero-feature-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            min-width: 0;
        }

        .hero-feature-text strong {
            color: #f8fafc;
            font-size: 0.79rem;
            font-weight: 600;
        }

        .hero-feature-text span {
            color: var(--slate-muted);
            font-size: 0.71rem;
        }

        .hero-footer .accent-bar {
            width: 32px;
            height: 3px;
            background-color: #38bdf8;
            border-radius: 2px;
            margin-bottom: 0.75rem;
        }

        .hero-footer .portal-title {
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.3;
            margin: 0 0 4px 0;
            letter-spacing: -0.01em;
        }

        .hero-footer .portal-desc {
            color: var(--slate-muted);
            font-size: 0.79rem;
            line-height: 1.45;
            margin: 0 0 0.75rem 0;
        }

        .hero-footer-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.71rem;
            color: #94a3b8;
            padding: 3px 9px;
            background: rgba(0, 0, 0, 0.28);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .status-dot-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: statusPulse 2s infinite;
        }

        @keyframes statusPulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Right Form Column */
        .login-form-side {
            width: 56%;
            padding: 3.25rem 3.25rem;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header .signin-title {
            color: var(--brand-navy);
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0 0 6px 0;
        }

        .form-header .signin-desc {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin: 0 0 1.75rem 0;
        }

        .form-field-group {
            margin-bottom: 1.25rem;
        }

        .field-label {
            font-size: 0.825rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.45rem;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 0.7rem 0.9rem;
            font-size: 0.89rem;
            color: var(--text-dark);
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--brand-navy);
            box-shadow: 0 0 0 3px rgba(11, 48, 91, 0.12);
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper input {
            padding-right: 2.75rem;
        }

        .toggle-password-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            padding: 4px;
            font-size: 1.05rem;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password-btn:hover {
            color: var(--brand-navy);
        }

        .forgot-link {
            font-size: 0.825rem;
            font-weight: 500;
            color: var(--brand-blue);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: var(--brand-blue-hover);
            text-decoration: underline;
        }

        .form-check-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .form-check-custom input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border: 1.5px solid #94a3b8;
            border-radius: 4px;
            accent-color: var(--brand-blue);
            cursor: pointer;
            margin: 0;
        }

        .form-check-custom label {
            font-size: 0.84rem;
            color: #475569;
            cursor: pointer;
            user-select: none;
            margin: 0;
        }

        .btn-signin {
            background-color: var(--brand-blue);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.05s ease;
            box-shadow: 0 2px 4px rgba(11, 77, 156, 0.2);
        }

        .btn-signin:hover {
            background-color: var(--brand-blue-hover);
        }

        .btn-signin:active {
            transform: scale(0.99);
        }

        /* Quick Demo Login Box */
        .quick-demo-box {
            margin-top: 1.5rem;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
        }

        .quick-demo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .quick-demo-title {
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-demo-title i {
            color: #f59e0b;
        }

        .badge-access {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #bae6fd;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .demo-roles-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .demo-role-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--brand-navy);
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            user-select: none;
        }

        .demo-role-btn:hover {
            background: #f0f7ff;
            border-color: var(--brand-blue);
            color: var(--brand-blue);
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(11, 77, 156, 0.12);
        }

        .demo-role-btn:active {
            transform: translateY(0);
        }

        .demo-footer-note {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.72rem;
            color: #64748b;
        }

        .demo-footer-note strong {
            color: var(--brand-navy);
        }

        /* Live error display */
        .custom-live-err {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 5px;
        }

        .form-control-custom.is-invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important;
        }

        /* Responsive Breakpoints */
        @media (max-width: 860px) {
            .login-card {
                flex-direction: column;
                max-width: 500px;
            }
            .login-hero {
                width: 100%;
                padding: 2.25rem 2rem;
            }
            .login-form-side {
                width: 100%;
                padding: 2.25rem 2rem;
            }
            .hero-illustration-wrapper {
                margin: 1.25rem 0;
            }
            .hero-illustration-svg {
                max-width: 260px;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Left Hero Column (Navy Blue) -->
    <div class="login-hero">
        <div class="brand-header">
            <div class="brand-logo-card">
                <img src="{{ asset('images/shibaura-logo-cropped.webp') }}" alt="Shibaura Machine" onerror="this.src='{{ asset('images/shibaura-logo.webp') }}'">
            </div>
        </div>

        <div class="hero-illustration-wrapper">
            <svg class="hero-illustration-svg" viewBox="0 0 320 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Shibaura Machine Factory and Feedback illustration">
                <defs>
                    <filter id="cardShadow" x="135" y="46" width="166" height="118" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.32" />
                    </filter>
                    <linearGradient id="factoryGrad" x1="28" y1="65" x2="175" y2="184" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#1e4e82" />
                        <stop offset="100%" stop-color="#123761" />
                    </linearGradient>
                    <linearGradient id="groundGrad" x1="20" y1="184" x2="300" y2="184" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.2" />
                        <stop offset="50%" stop-color="#38bdf8" stop-opacity="0.9" />
                        <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.2" />
                    </linearGradient>
                    <!-- Star Symbol -->
                    <g id="star-shape">
                        <polygon points="0,-5 1.5,-1.8 5,-1.5 2.5,1 3.2,4.5 0,2.8 -3.2,4.5 -2.5,1 -5,-1.5 -1.5,-1.8" />
                    </g>
                </defs>

                <!-- Factory Ground Baseline with glowing gradient line -->
                <line x1="20" y1="184" x2="300" y2="184" stroke="url(#groundGrad)" stroke-width="2.5" stroke-linecap="round" />

                <!-- Smokestack Steam Elements -->
                <circle cx="41.5" cy="54" r="5" fill="#38bdf8" fill-opacity="0.25" />
                <circle cx="44" cy="45" r="7" fill="#38bdf8" fill-opacity="0.15" />
                <circle cx="65.5" cy="69" r="4.5" fill="#38bdf8" fill-opacity="0.2" />

                <!-- Factory Chimneys / Smokestacks -->
                <!-- Chimney 1 -->
                <rect x="34" y="65" width="15" height="70" fill="url(#factoryGrad)" />
                <rect x="32" y="63" width="19" height="5" rx="1.5" fill="#38bdf8" fill-opacity="0.85" />

                <!-- Chimney 2 -->
                <rect x="58" y="80" width="15" height="55" fill="url(#factoryGrad)" />
                <rect x="56" y="78" width="19" height="5" rx="1.5" fill="#38bdf8" fill-opacity="0.85" />

                <!-- Chimney 3 -->
                <rect x="82" y="94" width="15" height="41" fill="url(#factoryGrad)" />
                <rect x="80" y="92" width="19" height="5" rx="1.5" fill="#2563eb" />

                <!-- Main Factory Building Body -->
                <path d="M 28 135 L 88 135 L 110 115 L 138 115 L 138 184 L 28 184 Z" fill="url(#factoryGrad)" stroke="#1e5187" stroke-width="1" />
                <path d="M 138 130 L 175 130 L 175 184 L 138 184 Z" fill="#143e6d" />

                <!-- Factory Windows (illuminated cyan/ice-blue squares) -->
                <rect x="42" y="146" width="13" height="11" rx="2" fill="#38bdf8" />
                <rect x="63" y="146" width="13" height="11" rx="2" fill="#38bdf8" />
                <rect x="42" y="163" width="13" height="11" rx="2" fill="#38bdf8" />
                <rect x="63" y="163" width="13" height="11" rx="2" fill="#38bdf8" />

                <!-- Factory Entrance Doorway -->
                <rect x="94" y="150" width="18" height="34" rx="2" fill="#082344" stroke="#38bdf8" stroke-width="1.5" />

                <!-- Cogwheel / Gear Icon on Factory -->
                <g transform="translate(150, 134)" fill="#60a5fa">
                    <circle cx="0" cy="0" r="13" fill="#3b82f6" />
                    <!-- Gear Teeth -->
                    <rect x="-2.5" y="-16" width="5" height="32" rx="1.5" fill="#60a5fa" />
                    <rect x="-2.5" y="-16" width="5" height="32" rx="1.5" fill="#60a5fa" transform="rotate(45)" />
                    <rect x="-2.5" y="-16" width="5" height="32" rx="1.5" fill="#60a5fa" transform="rotate(90)" />
                    <rect x="-2.5" y="-16" width="5" height="32" rx="1.5" fill="#60a5fa" transform="rotate(135)" />
                    <!-- Inner Cutout -->
                    <circle cx="0" cy="0" r="6" fill="#143e6d" />
                </g>

                <!-- Worker Avatar (Operator with Yellow Hardhat) -->
                <!-- Body / Uniform -->
                <path d="M 230 184 C 230 166 241 158 256 158 C 271 158 282 166 282 184 Z" fill="#2563eb" />
                <!-- Worker Face -->
                <circle cx="256" cy="153" r="9" fill="#fcd34d" />
                <!-- Worker Yellow Hardhat / Helmet -->
                <path d="M 243 151 C 243 139 269 139 269 151 Z" fill="#f59e0b" />
                <rect x="240" y="149" width="32" height="3.5" rx="1.75" fill="#d97706" />

                <!-- Floating Feedback Card (White Dialog Box) -->
                <!-- Speech bubble tail pointer pointing to worker -->
                <polygon points="256,134 266,134 260,147" fill="#ffffff" />
                <!-- Card Container with Shadow -->
                <rect x="144" y="54" width="150" height="90" rx="9" fill="#ffffff" filter="url(#cardShadow)" stroke="#e2e8f0" stroke-width="1" />

                <!-- Card Header: Blue Circular Badge with Checkmark -->
                <circle cx="163" cy="71" r="9" fill="#0052cc" />
                <path d="M 159.5 71 L 162 73.5 L 167 68.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />

                <!-- Card Header: Title -->
                <text x="178" y="75" fill="#0b305b" font-size="10.5" font-weight="700" font-family="'Inter', sans-serif">Visitor Review</text>

                <!-- Rating Stars Row (All 5 Gold Stars) -->
                <g fill="#f59e0b">
                    <use href="#star-shape" x="162" y="90" />
                    <use href="#star-shape" x="175" y="90" />
                    <use href="#star-shape" x="188" y="90" />
                    <use href="#star-shape" x="201" y="90" />
                    <use href="#star-shape" x="214" y="90" />
                </g>

                <!-- Rating Score Chip -->
                <rect x="234" y="81" width="48" height="17" rx="4" fill="#ecfdf5" stroke="#a7f3d0" stroke-width="0.8" />
                <text x="258" y="93" fill="#059669" font-size="9.5" font-weight="700" text-anchor="middle" font-family="'Inter', sans-serif">5.0 ★</text>

                <!-- Filled Feedback Content (No skeleton placeholder lines) -->
                <text x="156" y="112" fill="#0f172a" font-size="9" font-weight="600" font-family="'Inter', sans-serif">"Excellent plant tour!"</text>
                <text x="156" y="125" fill="#64748b" font-size="7.5" font-weight="500" font-family="'Inter', sans-serif">Precision Moulding Unit • Verified</text>
                <circle cx="276" cy="122" r="3" fill="#10b981" />
            </svg>
        </div>

        <!-- Left Hero Feature Highlights (Filled Content & Icons) -->
        <div class="hero-features">
            <div class="hero-feature-item">
                <div class="hero-feature-icon icon-cyan">
                    <i class="bi bi-chat-heart-fill"></i>
                </div>
                <div class="hero-feature-text">
                    <strong>Live Visitor Feedback</strong>
                    <span>Real-time ratings, comments & surveys</span>
                </div>
            </div>

            <div class="hero-feature-item">
                <div class="hero-feature-icon icon-amber">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>
                <div class="hero-feature-text">
                    <strong>Operations & KPI Insights</strong>
                    <span>Automated facility metrics & reports</span>
                </div>
            </div>

            <div class="hero-feature-item">
                <div class="hero-feature-icon icon-emerald">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="hero-feature-text">
                    <strong>Enterprise Access</strong>
                    <span>Multi-role plant security & audits</span>
                </div>
            </div>
        </div>

        <div class="hero-footer">
            <div class="accent-bar"></div>
            <h2 class="portal-title">Plant Feedback & Visitor Insights</h2>
            <p class="portal-desc">Operations dashboard for authorised plant personnel.</p>
            <div class="hero-footer-status">
                <span class="status-dot-pulse"></span>
                <span>System Active • Multi-Plant Connected</span>
            </div>
        </div>
    </div>

    <!-- Right Form Column (White) -->
    <div class="login-form-side">
        <div class="form-header">
            <h2 class="signin-title">Sign in</h2>
            <p class="signin-desc">Enter your credentials to continue.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success py-2 px-3 rounded-2 small mb-3 border-0 text-white d-flex align-items-center gap-2" style="background-color: #059669;">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 rounded-2 small mb-3 border-0 text-white d-flex align-items-center gap-2" style="background-color: #dc2626;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
            @csrf

            <!-- Email or Mobile -->
            <div class="form-field-group">
                <label class="field-label" for="loginInput">Email or Mobile</label>
                <input
                    type="text"
                    class="form-control-custom"
                    id="loginInput"
                    name="login"
                    value="{{ old('login') }}"
                    placeholder="Enter email or mobile number"
                    minlength="3"
                    maxlength="100"
                    required
                    autofocus
                    autocomplete="username"
                >
                <div class="invalid-feedback d-none custom-live-err" id="live_err_loginInput"></div>
            </div>

            <!-- Password -->
            <div class="form-field-group">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="field-label mb-0" for="passwordInput">Password</label>
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                </div>
                <div class="password-input-wrapper">
                    <input
                        type="password"
                        class="form-control-custom"
                        id="passwordInput"
                        name="password"
                        placeholder="Enter password"
                        minlength="4"
                        maxlength="64"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePassVisibility()" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback d-none custom-live-err" id="live_err_passwordInput"></div>
            </div>

            <!-- Keep me signed in -->
            <div class="form-check-custom">
                <input type="checkbox" name="remember" id="remember" checked>
                <label for="remember">Keep me signed in for 30 days</label>
            </div>

            <!-- Sign In Submit Button -->
            <button type="submit" class="btn-signin" id="submitBtn">
                Sign In
            </button>
        </form>

        <!-- Quick Demo Login Section -->
        <div class="quick-demo-box">
            <div class="quick-demo-header">
                <div class="quick-demo-title">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Quick Demo Login (Click to Login):</span>
                </div>
                <span class="badge-access">1-Click Access</span>
            </div>
            <div class="demo-roles-grid">
                <button type="button" class="demo-role-btn" onclick="quickLogin('superadmin@plant.test', 'password', 'Super Admin')" title="Super Admin: All 14 modules & 70 capabilities">
                    <span>👑</span>
                    <span>Super Admin</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickLogin('admin@plant.test', 'password', 'Admin')" title="Admin: Plant masters, User directory, Audits & Reports">
                    <span>🛡️</span>
                    <span>Admin</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickLogin('coordinator@plant.test', 'password', 'Coordinator')" title="Coordinator: Visitors, Feedback forms & Analytics">
                    <span>📋</span>
                    <span>Coordinator</span>
                </button>
            </div>
            <div class="demo-footer-note">
                <i class="bi bi-info-circle me-1 text-primary"></i> Click any role button above for instant 1-click login. Default password: <strong>password</strong>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassVisibility() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function quickLogin(login, password, label) {
    const loginInp = document.getElementById('loginInput');
    const passInp = document.getElementById('passwordInput');
    const submitBtn = document.getElementById('submitBtn');

    if (loginInp) loginInp.value = login;
    if (passInp) passInp.value = password;
    clearLoginLiveErr('loginInput');
    clearLoginLiveErr('passwordInput');

    if (submitBtn) {
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Signing in as ${label}...`;
        submitBtn.disabled = true;
    }

    const form = document.getElementById('loginForm');
    if (form) {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }
}

function showLoginLiveErr(id, msg, persistent = false) {
    const errEl = document.getElementById('live_err_' + id);
    const input = document.getElementById(id);
    if (input) input.classList.add('is-invalid');
    if (errEl) {
        errEl.textContent = msg;
        errEl.classList.remove('d-none');
        errEl.classList.add('d-block');
        clearTimeout(errEl._timer);
        if (!persistent) {
            errEl._timer = setTimeout(() => {
                errEl.classList.remove('d-block');
                errEl.classList.add('d-none');
            }, 2500);
        }
    }
}

function clearLoginLiveErr(id) {
    const errEl = document.getElementById('live_err_' + id);
    const input = document.getElementById(id);
    if (input) input.classList.remove('is-invalid');
    if (errEl) {
        errEl.classList.remove('d-block');
        errEl.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const loginInp = document.getElementById('loginInput');
    const passInp = document.getElementById('passwordInput');

    if (loginInp) {
        const allowedRegex = /^[a-zA-Z0-9@._\-]$/;
        const disallowed = /[^a-zA-Z0-9@._\-]/g;
        const emailRegex = /^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/;

        // 1. Prevent typing disallowed characters on keystroke
        loginInp.addEventListener('keydown', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (!allowedRegex.test(e.key)) {
                    e.preventDefault();
                    showLoginLiveErr('loginInput', 'Special characters other than @, ., _, - are not allowed.');
                }
            }
        });

        // 2. Prevent disallowed input on virtual keyboards / mobile
        loginInp.addEventListener('beforeinput', function(e) {
            if (e.data) {
                for (let i = 0; i < e.data.length; i++) {
                    if (!allowedRegex.test(e.data[i])) {
                        e.preventDefault();
                        showLoginLiveErr('loginInput', 'Special characters other than @, ., _, - are not allowed.');
                        return;
                    }
                }
            }
        });

        // 3. Clean paste
        loginInp.addEventListener('paste', function(e) {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (text && disallowed.test(text)) {
                e.preventDefault();
                const cleaned = text.replace(disallowed, '');
                document.execCommand('insertText', false, cleaned);
            }
        });

        // 4. Input fallback
        loginInp.addEventListener('input', function() {
            if (disallowed.test(this.value)) {
                this.value = this.value.replace(disallowed, '');
                showLoginLiveErr('loginInput', 'Special characters other than @, ., _, - are not allowed.');
            } else if (this.value.trim().length >= 3) {
                clearLoginLiveErr('loginInput');
            }
        });

        // 5. Blur validation
        loginInp.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val.includes('@') && !emailRegex.test(val)) {
                showLoginLiveErr('loginInput', 'Please enter a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).', true);
            }
        });
    }

    if (passInp) {
        passInp.addEventListener('input', function() {
            if (this.value.trim().length >= 4) {
                clearLoginLiveErr('passwordInput');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            let firstInvalid = null;

            const loginVal = loginInp ? loginInp.value.trim() : '';
            const passVal = passInp ? passInp.value : '';
            const emailRegex = /^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/;

            if (!loginVal) {
                hasError = true;
                showLoginLiveErr('loginInput', 'Email or Mobile number is required.', true);
                if (!firstInvalid) firstInvalid = loginInp;
            } else if (loginVal.length < 3) {
                hasError = true;
                showLoginLiveErr('loginInput', 'Must be at least 3 characters.', true);
                if (!firstInvalid) firstInvalid = loginInp;
            } else if (loginVal.includes('@') && !emailRegex.test(loginVal)) {
                hasError = true;
                showLoginLiveErr('loginInput', 'Please enter a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).', true);
                if (!firstInvalid) firstInvalid = loginInp;
            }

            if (!passVal) {
                hasError = true;
                showLoginLiveErr('passwordInput', 'Password is required.', true);
                if (!firstInvalid) firstInvalid = passInp;
            } else if (passVal.length < 4) {
                hasError = true;
                showLoginLiveErr('passwordInput', 'Password must be at least 4 characters.', true);
                if (!firstInvalid) firstInvalid = passInp;
            }

            if (hasError) {
                e.preventDefault();
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    }
});
</script>

</body>
</html>
