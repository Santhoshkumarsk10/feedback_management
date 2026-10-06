<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#06539d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Shibaura Machine — Plant Visit Feedback</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Typography: Inter, Poppins, Roboto, Noto Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --shibaura-blue: #06539d;
            --shibaura-dark: #032b53;
            --shibaura-light: #eff6ff;
            --font-family: 'Inter', 'Poppins', 'Segoe UI', Roboto, 'Noto Sans', sans-serif;
            --slate-800: #1e293b;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-family);
            background: #f4f7fb;
            color: var(--slate-800);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* App Mobile Shell Header */
        .mobile-header {
            background: #ffffff;
            border-bottom: 2px solid var(--shibaura-blue);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(6, 83, 157, 0.08);
        }

        .mobile-brand-logo {
            height: 38px;
            object-fit: contain;
        }

        .header-action-btn {
            background: #f8fafc;
            border: 1px solid var(--slate-200);
            border-radius: 9999px;
            padding: 0.4rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--slate-600);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .header-action-btn:hover, .header-action-btn:active {
            background: var(--shibaura-light);
            color: var(--shibaura-blue);
            border-color: var(--shibaura-blue);
        }

        /* Main Container */
        .app-container {
            max-width: 760px;
            margin: 0 auto;
            padding: 1.25rem 1rem 3rem 1rem;
        }

        /* Card Modern Mobile */
        .mobile-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .step-progress-wrapper {
            background: #ffffff;
            border-radius: 14px;
            padding: 0.85rem 1.25rem;
            border: 1px solid var(--slate-200);
            margin-bottom: 1.25rem;
        }

        .step-progress-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .step-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #06539d, #0284c7);
            border-radius: 9999px;
            transition: width 0.35s ease;
        }

        /* Section Title Pill */
        .section-badge-pill {
            background: var(--shibaura-light);
            color: var(--shibaura-blue);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border: 1px solid rgba(6, 83, 157, 0.2);
            margin-bottom: 0.75rem;
        }

        /* Question Box Card */
        .question-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            transition: all 0.2s ease;
        }

        .question-box:focus-within {
            border-color: var(--shibaura-blue);
            box-shadow: 0 4px 14px rgba(6, 83, 157, 0.1);
        }

        .question-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        /* Star Rating Modern Component */
        .star-rating-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .star-item-btn {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .star-item-btn:hover, .star-item-btn.selected {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
        }

        .star-label-desc {
            font-size: 0.85rem;
            font-weight: 600;
            color: #b45309;
            margin-left: 0.5rem;
        }

        /* Option Chip Radio/Checkbox */
        .choice-option-pill {
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.15s ease;
            background: #f8fafc;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .choice-option-pill:hover {
            border-color: var(--shibaura-blue);
            background: #ffffff;
        }

        .choice-option-pill.active {
            background: var(--shibaura-light);
            border-color: var(--shibaura-blue);
            color: var(--shibaura-blue);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(6, 83, 157, 0.12);
        }

        /* Question Inline Promotional Banner */
        .question-inline-banner {
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.2s ease;
            margin-top: 0.85rem;
        }
        .question-inline-banner:hover {
            box-shadow: 0 4px 12px rgba(6, 83, 157, 0.08);
            border-color: #cbd5e1;
        }
        .question-banner-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px;
            text-decoration: none;
            color: inherit;
        }
        .question-banner-img-wrap {
            width: 76px;
            height: 48px;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .question-banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .question-banner-meta {
            flex-grow: 1;
            overflow: hidden;
        }
        .question-banner-headline {
            font-size: 0.84rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        .question-banner-sub {
            font-size: 0.72rem;
            color: #64748b;
            line-height: 1.2;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        /* Buttons */
        .btn-shibaura {
            background: var(--shibaura-blue);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 0.85rem 1.75rem;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(6, 83, 157, 0.25);
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-shibaura:hover, .btn-shibaura:active {
            background: var(--shibaura-dark);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-shibaura-outline {
            background: #ffffff;
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            border-radius: 12px;
            font-weight: 600;
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.15s ease;
            width: 100%;
        }

        .btn-shibaura-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Floating APK Download Banner */
        .apk-download-bar {
            background: linear-gradient(90deg, #021e3a, #06539d);
            color: #ffffff;
            padding: 0.5rem 1rem;
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .apk-download-btn {
            background: #ffffff;
            color: #06539d;
            border-radius: 9999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .apk-download-btn:hover {
            background: #e0f2fe;
            color: #04386c;
        }

        .hidden-screen {
            display: none !important;
        }

        /* Animated Checkmark */
        .success-checkmark-circle {
            width: 80px;
            height: 80px;
            background: #ecfdf5;
            border: 3px solid #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: #10b981;
            margin: 0 auto 1.5rem auto;
            animation: bounceIn 0.5s ease;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Dynamic CMS Banner Carousel */
        .cms-banner-carousel-wrapper {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 24px -4px rgba(6, 83, 157, 0.18);
            border: 1px solid rgba(6, 83, 157, 0.12);
            background: #0f172a;
            margin-bottom: 1.25rem;
        }
        .cms-banner-carousel {
            position: relative;
            width: 100%;
            height: 175px;
            overflow: hidden;
        }
        @media (min-width: 576px) {
            .cms-banner-carousel {
                height: 200px;
            }
        }
        .cms-banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
            transform: scale(0.97);
        }
        .cms-banner-slide.active {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
            z-index: 2;
        }
        .cms-banner-link-wrapper {
            display: block;
            width: 100%;
            height: 100%;
            position: relative;
            text-decoration: none;
        }
        .cms-banner-bg-img {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 4s ease;
        }
        .cms-banner-slide.active .cms-banner-bg-img {
            transform: scale(1.05);
        }
        .cms-banner-glass-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(15,23,42,0.15) 0%, rgba(15,23,42,0.88) 95%);
        }
        .cms-banner-text-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 1rem 1.25rem;
            z-index: 3;
        }
        .cms-banner-pill-tag {
            background: rgba(2, 132, 199, 0.9);
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 7px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 5px;
        }
        .cms-banner-headline {
            color: #ffffff;
            font-size: 0.98rem;
            font-weight: 700;
            margin-bottom: 3px;
            line-height: 1.25;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6);
        }
        .cms-banner-subtext {
            color: #cbd5e1;
            font-size: 0.74rem;
            margin-bottom: 4px;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .cms-banner-cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #0284c7;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            margin-top: 2px;
        }
        .cms-carousel-controls {
            position: absolute;
            bottom: 8px;
            right: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
        }
        .cms-carousel-arrow {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            cursor: pointer;
            backdrop-filter: blur(4px);
        }
        .cms-carousel-dots {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .cms-carousel-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease;
        }
        .cms-carousel-dot.active {
            width: 14px;
            border-radius: 4px;
            background: #38bdf8;
        }
    </style>
</head>
<body>

    <!-- Android APK Notification Bar -->
    <div class="apk-download-bar">
        <span><i class="bi bi-android2 me-1"></i> Shibaura Machine Visitor & Organizer Android APK</span>
        <a href="/apk/shibaura-plant-feedback.apk" class="apk-download-btn">
            <i class="bi bi-download"></i> Download APK
        </a>
    </div>

    <!-- Header Navigation -->
    <header class="mobile-header">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ $company->logo_url ?? asset('images/shibaura-logo-cropped.webp') }}" alt="{{ $company->name ?? 'Shibaura Machine' }}" class="mobile-brand-logo">
            <div class="vr mx-1 text-muted d-none d-sm-block"></div>
            <span class="badge bg-light text-primary border border-primary-subtle fw-semibold d-none d-sm-inline-block" style="font-size: 0.75rem;">
                Technical Centre
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Mode Switch Button -->
            <button type="button" class="header-action-btn" id="btnSwitchMode" onclick="toggleOrganizerMode()">
                <i class="bi bi-shield-lock-fill"></i>
                <span id="txtModeLabel">Organizer Login</span>
            </button>
        </div>
    </header>

    <div class="app-container">

        <!-- ============================================================== -->
        <!-- SCREEN 1: VISITOR REGISTRATION / START (NO LOGIN REQUIRED)      -->
        <!-- ============================================================== -->
        <div id="screenVisitorRegister">
            @if(isset($banners) && $banners->isNotEmpty())
            <!-- Dynamic Promotional CMS Banners Slider -->
            <div class="cms-banner-carousel-wrapper" id="cmsBannerWrap">
                <div class="cms-banner-carousel" id="cmsBannerCarousel">
                    @foreach($banners as $index => $banner)
                    <div class="cms-banner-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" target="_blank" class="cms-banner-link-wrapper" title="{{ $banner->title }}">
                        @else
                            <div class="cms-banner-link-wrapper">
                        @endif
                            <div class="cms-banner-bg-img" style="background-image: url('{{ $banner->image_url }}');"></div>
                            <div class="cms-banner-glass-overlay"></div>
                            <div class="cms-banner-text-overlay">
                                <span class="cms-banner-pill-tag">
                                    <i class="bi bi-stars"></i> {{ strtoupper($banner->target === 'all' ? 'Featured' : $banner->target) }}
                                </span>
                                <h3 class="cms-banner-headline">{{ $banner->title }}</h3>
                                @if($banner->subtitle)
                                    <p class="cms-banner-subtext">{{ $banner->subtitle }}</p>
                                @endif
                                @if($banner->link_url)
                                    <span class="cms-banner-cta-btn">
                                        <span>Explore</span> <i class="bi bi-arrow-right"></i>
                                    </span>
                                @endif
                            </div>
                        @if($banner->link_url)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if($banners->count() > 1)
                <div class="cms-carousel-controls">
                    <button type="button" class="cms-carousel-arrow prev" onclick="moveCmsBanner(-1)" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="cms-carousel-dots">
                        @foreach($banners as $index => $banner)
                            <button type="button" class="cms-carousel-dot {{ $index === 0 ? 'active' : '' }}" onclick="goToCmsBanner({{ $index }})" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="cms-carousel-arrow next" onclick="moveCmsBanner(1)" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                @endif
            </div>
            @endif

            <div class="mobile-card">
                <div class="text-center mb-4">
                    <span class="section-badge-pill">
                        <i class="bi bi-qr-code-scan"></i> Customer Evaluation Form
                    </span>
                    <h4 class="fw-bold text-dark mb-1">Welcome to {{ $company->name ?? 'Shibaura Machine India' }}</h4>
                    <p class="text-muted small mb-0">We value your visit and feedback. Please take 2 minutes to evaluate your plant tour experience.</p>
                </div>

                <form id="formVisitorDetails" onsubmit="event.preventDefault(); startSurvey();">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                                <input type="text" id="inpVisitorName" class="form-control form-control-sm border-start-0" placeholder="e.g. Ramesh Kumar" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Company / Organization <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-buildings-fill"></i></span>
                                <input type="text" id="inpVisitorCompany" class="form-control form-control-sm border-start-0" placeholder="e.g. Tata Motors / Motherson Group" required>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone-fill"></i></span>
                                <input type="tel" id="inpVisitorMobile" class="form-control form-control-sm border-start-0" placeholder="9876543210">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" id="inpVisitorEmail" class="form-control form-control-sm border-start-0" placeholder="ramesh@company.com">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Designation / Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-briefcase-fill"></i></span>
                                <input type="text" id="inpVisitorDesignation" class="form-control form-control-sm border-start-0" placeholder="e.g. General Manager — Manufacturing">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Plant / Facility Visited <span class="text-danger">*</span></label>
                            <select id="selPlant" class="form-select form-select-sm" required>
                                @foreach($plants as $pl)
                                    <option value="{{ $pl->id }}" @selected(str_contains($pl->code, 'PLANT-04') || $loop->last)>
                                        {{ $pl->code }} — {{ $pl->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Shibaura Tour Guide / Engineer <span class="text-danger">*</span></label>
                            <select id="selOrganizer" class="form-select form-select-sm" required>
                                @foreach($organizers as $org)
                                    <option value="{{ $org->id }}" data-plant-id="{{ $org->plant_id }}">
                                        {{ $org->name }} ({{ $org->department ?: 'SMI Engineer' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Purpose of Visit</label>
                            <select id="selPurpose" class="form-select form-select-sm">
                                <option value="Customer Mold Trial & Demonstration">Customer Mold Trial & Demonstration</option>
                                <option value="Machine Inspection & Technical Verification">Machine Inspection & Technical Verification</option>
                                <option value="machiNETCloud & IoT Suite Demo">machiNETCloud & IoT Suite Demo</option>
                                <option value="Technical Centre & VR Zone Experience">Technical Centre & VR Zone Experience</option>
                                <option value="Training & Knowledge Sharing Session">Training & Knowledge Sharing Session</option>
                                <option value="New Equipment Purchase Evaluation">New Equipment Purchase Evaluation</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-shibaura">
                        <span>Begin Evaluation Questions</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SCREEN 2: 23 QUESTIONS ACROSS 4 SECTIONS (VISITOR SURVEY)        -->
        <!-- ============================================================== -->
        <div id="screenSurveySections" class="hidden-screen">
            <!-- Progress Tracker -->
            <div class="step-progress-wrapper">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted">Evaluation Progress</span>
                        <div class="fw-bold text-dark" id="txtProgressLabel">Section 1 of 4</div>
                    </div>
                    <span class="badge bg-primary text-white" id="txtProgressPct">25%</span>
                </div>
                <div class="step-progress-bar">
                    <div class="step-progress-fill" id="barProgressFill" style="width: 25%;"></div>
                </div>
            </div>

            <!-- Form Container -->
            <form id="formSurveyQuestions">
                @php $sectionIndex = 0; @endphp
                @foreach($sections as $secTitle => $secQuestions)
                    @php $sectionIndex++; @endphp
                    <div class="survey-section-card {{ $sectionIndex > 1 ? 'hidden-screen' : '' }}" id="surveySection{{ $sectionIndex }}">
                        <div class="mb-3">
                            <span class="section-badge-pill">
                                <i class="bi bi-ui-checks"></i> Part {{ $sectionIndex }}
                            </span>
                            <h5 class="fw-bold text-dark mb-1">{{ $secTitle }}</h5>
                            <p class="text-muted small mb-0">{{ $secQuestions->count() }} Questions in this section</p>
                        </div>

                        <!-- Question Items Loop -->
                        @foreach($secQuestions as $q)
                            <div class="question-box" data-question-id="{{ $q->id }}" data-required="{{ $q->is_required ? '1' : '0' }}">
                                <div class="question-title">
                                    <span class="text-primary me-1">Q{{ $loop->iteration }}.</span> {{ $q->question }}
                                    @if($q->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </div>

                                <!-- Rating (1-5 Stars) -->
                                @if($q->type === 'rating')
                                    <div class="star-rating-group" data-qid="{{ $q->id }}">
                                        @for($s = 1; $s <= 5; $s++)
                                            <div class="star-item-btn" onclick="selectStar({{ $q->id }}, {{ $s }})" data-val="{{ $s }}">
                                                ★
                                            </div>
                                        @endfor
                                        <input type="hidden" name="question_{{ $q->id }}" id="inp_q_{{ $q->id }}" value="">
                                        <span class="star-label-desc" id="star_label_{{ $q->id }}">Select 1 - 5</span>
                                    </div>

                                <!-- Single Choice MCQ -->
                                @elseif($q->type === 'mcq')
                                    <div class="choice-group">
                                        @foreach($q->options ?? [] as $opt)
                                            <div class="choice-option-pill" onclick="selectRadio({{ $q->id }}, '{{ addslashes($opt) }}', this)">
                                                <i class="bi bi-circle me-2"></i> {{ $opt }}
                                            </div>
                                        @endforeach
                                        <input type="hidden" name="question_{{ $q->id }}" id="inp_q_{{ $q->id }}" value="">
                                    </div>

                                <!-- Multiple Choice Checkboxes -->
                                @elseif($q->type === 'multiple')
                                    <div class="choice-group multiple-choice-group" data-qid="{{ $q->id }}">
                                        @foreach($q->options ?? [] as $opt)
                                            <div class="choice-option-pill" onclick="toggleCheckbox({{ $q->id }}, '{{ addslashes($opt) }}', this)">
                                                <i class="bi bi-square me-2"></i> {{ $opt }}
                                            </div>
                                        @endforeach
                                        <input type="hidden" name="question_{{ $q->id }}" id="inp_q_{{ $q->id }}" value="">
                                    </div>

                                <!-- Text Field / Comment -->
                                @elseif($q->type === 'text')
                                    <textarea name="question_{{ $q->id }}" id="inp_q_{{ $q->id }}" rows="2" class="form-control form-control-sm" placeholder="Please write your observations or suggestions here..."></textarea>
                                @endif

                                <!-- Promotional Banner Under Each Question -->
                                @if(isset($banners) && $banners->isNotEmpty())
                                    @php
                                        $qBanner = $banners[($loop->iteration - 1) % $banners->count()];
                                    @endphp
                                    <div class="question-inline-banner">
                                        @if($qBanner->link_url)
                                            <a href="{{ $qBanner->link_url }}" target="_blank" class="question-banner-link" title="{{ $qBanner->title }}">
                                        @else
                                            <div class="question-banner-link">
                                        @endif
                                            <div class="question-banner-img-wrap">
                                                <img src="{{ $qBanner->image_url }}" alt="{{ $qBanner->title }}" class="question-banner-img" onerror="this.src='{{ asset('images/shibaura-logo-cropped.webp') }}'">
                                            </div>
                                            <div class="question-banner-meta">
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <span class="badge bg-primary-subtle text-primary py-0 px-2 fw-semibold" style="font-size: 0.65rem;">
                                                        <i class="bi bi-megaphone-fill"></i> SHIBAURA SPOTLIGHT
                                                    </span>
                                                    @if($qBanner->link_url)
                                                        <span class="text-primary ms-auto" style="font-size: 0.7rem; font-weight: 600;">
                                                            Explore <i class="bi bi-arrow-right"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="question-banner-headline">{{ $qBanner->title }}</div>
                                                @if($qBanner->subtitle)
                                                    <div class="question-banner-sub">{{ $qBanner->subtitle }}</div>
                                                @endif
                                            </div>
                                        @if($qBanner->link_url)
                                            </a>
                                        @else
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Navigation Buttons between sections -->
                        <div class="row g-2 mt-4">
                            @if($sectionIndex > 1)
                                <div class="col-6">
                                    <button type="button" class="btn-shibaura-outline" onclick="goToSection({{ $sectionIndex - 1 }})">
                                        <i class="bi bi-arrow-left"></i> Previous
                                    </button>
                                </div>
                            @endif
                            <div class="{{ $sectionIndex > 1 ? 'col-6' : 'col-12' }}">
                                @if($sectionIndex < count($sections))
                                    <button type="button" class="btn-shibaura" onclick="goToSection({{ $sectionIndex + 1 }})">
                                        <span>Next Section</span> <i class="bi bi-arrow-right"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn-shibaura" style="background: #10b981;" onclick="submitSurveyForm()">
                                        <i class="bi bi-check-circle-fill"></i> Complete & Submit
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </form>
        </div>

        <!-- ============================================================== -->
        <!-- SCREEN 3: THANK YOU & CONFIRMATION SCREEN                      -->
        <!-- ============================================================== -->
        <div id="screenThankYou" class="hidden-screen">
            <div class="mobile-card text-center py-5">
                <div class="success-checkmark-circle">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Thank You for Your Feedback!</h3>
                <p class="text-muted mb-4 px-3">
                    Your valuable responses have been recorded directly into Shibaura Machine's Quality & Experience Management System. We appreciate your partnership with us.
                </p>

                <div class="p-3 bg-light rounded-3 mb-4 mx-auto text-start" style="max-width: 360px;">
                    <div class="small text-muted">Visitor:</div>
                    <div class="fw-bold text-dark mb-2" id="txtSummaryVisitor">—</div>
                    <div class="small text-muted">Company:</div>
                    <div class="fw-bold text-dark mb-2" id="txtSummaryCompany">—</div>
                    <div class="small text-muted">Overall Experience:</div>
                    <div class="text-warning fs-5 fw-bold" id="txtSummaryRating">★★★★★</div>
                </div>

                <div class="d-flex flex-column gap-2" style="max-width: 360px; margin: 0 auto;">
                    <button type="button" class="btn-shibaura" onclick="resetToNewVisitor()">
                        <i class="bi bi-person-plus-fill"></i> Register Next Visitor
                    </button>
                    <div class="small text-muted mt-2" id="txtResetCountdown">Auto-resetting in 15 seconds...</div>
                </div>

                <div class="mt-4 pt-3 border-top text-muted small text-center">
                    <span>Developed by <strong class="text-dark">Amoebatronix PVT LTD</strong></span>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SCREEN 4: ORGANIZER LOGIN (REQUIRED FOR ORGANIZER ACCESS)       -->
        <!-- ============================================================== -->
        <div id="screenOrganizerLogin" class="hidden-screen">
            <div class="mobile-card" style="max-width: 440px; margin: 0 auto;">
                <div class="text-center mb-4">
                    <span class="stat-icon-bubble shibaura mx-auto mb-2" style="width: 48px; height: 48px; font-size: 1.3rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <h5 class="fw-bold text-dark mb-1">Tour Organizer Portal</h5>
                    <p class="text-muted small">Login with your credentials to manage visits and reviews</p>
                </div>

                <form id="formOrganizerLogin" onsubmit="event.preventDefault(); handleOrganizerLogin();">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Mobile Number or Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-badge"></i></span>
                            <input type="text" id="inpOrgLogin" class="form-control form-control-sm border-start-0" placeholder="e.g. 9100000001 or ravi@plant.test" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">Password</label>
                            <a href="javascript:void(0)" onclick="openOrgForgotModal()" class="small text-decoration-none text-primary fw-medium" style="font-size: 0.78rem;">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                            <input type="password" id="inpOrgPassword" class="form-control form-control-sm border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn-shibaura" id="btnOrgSubmit">
                            <i class="bi bi-box-arrow-in-right"></i> Authenticate & Enter
                        </button>
                        <button type="button" class="btn-shibaura-outline" onclick="switchToVisitorMode()">
                            <i class="bi bi-arrow-left"></i> Return to Visitor Form
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SCREEN 5: ORGANIZER DASHBOARD (LOGGED IN)                      -->
        <!-- ============================================================== -->
        <div id="screenOrganizerDashboard" class="hidden-screen">
            <!-- Organizer Header Card -->
            <div class="mobile-card mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="user-avatar-chip" style="width: 44px; height: 44px; font-size: 1.1rem;" id="txtOrgAvatar">
                            OM
                        </span>
                        <div>
                            <h6 class="fw-bold text-dark mb-0" id="txtOrgName">Organizer Name</h6>
                            <span class="badge-modern badge-shibaura" id="txtOrgPlantBadge">PLANT-01</span>
                            <span class="badge-modern badge-slate" id="txtOrgRoleBadge">Tour Guide</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="openOrgChangePasswordModal()" title="Change Password">
                            <i class="bi bi-key-fill text-warning"></i> Password
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="handleOrganizerLogout()">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <div class="text-muted" style="font-size: 0.72rem;">Total Tours</div>
                            <div class="fs-5 fw-bold text-dark" id="statOrgTotalVisits">0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <div class="text-muted" style="font-size: 0.72rem;">Today's Tours</div>
                            <div class="fs-5 fw-bold text-primary" id="statOrgTodayVisits">0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <div class="text-muted" style="font-size: 0.72rem;">Avg Rating</div>
                            <div class="fs-5 fw-bold text-warning" id="statOrgAvgRating">5.0 ★</div>
                        </div>
                    </div>
                </div>

                <!-- Big Action: Launch Visitor Mode for Customer -->
                <button type="button" class="btn-shibaura" style="background: linear-gradient(90deg, #06539d, #0284c7);" onclick="launchSurveyForCustomer()">
                    <i class="bi bi-tablet-fill"></i>
                    <span>Hand Tablet to Visitor (Start Survey)</span>
                </button>
            </div>

            <!-- Recent Customer Feedback Section -->
            <div class="mobile-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history text-primary me-1"></i> Recent Customer Reviews
                    </h6>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="loadOrganizerVisits()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>

                <div id="containerOrgVisitsList" class="d-flex flex-column gap-2">
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>
                        Loading visit evaluations...
                    </div>
                </div>
            </div>
        </div>

        <!-- Corporate Brand & Contact Footer -->
        <footer class="mt-4 pt-4 border-top text-center text-muted">
            <div class="fw-bold text-dark mb-1 fs-6">{{ $company->name ?? 'Shibaura Machine India Private Limited' }}</div>
            <div class="mb-2 text-secondary small">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $company->full_address ?? 'Chembarambakkam, Chennai, Tamil Nadu' }}
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 small text-secondary">
                @if($company->phone)
                    <a href="tel:{{ $company->phone }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-telephone-fill text-success me-1"></i> {{ $company->phone }}
                    </a>
                @endif
                @if($company->alter_phone)
                    <span class="text-muted d-none d-sm-inline">|</span>
                    <a href="tel:{{ $company->alter_phone }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-phone text-primary me-1"></i> {{ $company->alter_phone }}
                    </a>
                @endif
                @if($company->email)
                    <span class="text-muted d-none d-sm-inline">|</span>
                    <a href="mailto:{{ $company->email }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-envelope-fill text-primary me-1"></i> {{ $company->email }}
                    </a>
                @endif
                @if($company->website)
                    <span class="text-muted d-none d-sm-inline">|</span>
                    <a href="{{ $company->website }}" target="_blank" class="text-decoration-none text-secondary">
                        <i class="bi bi-globe text-info me-1"></i> {{ parse_url($company->website, PHP_URL_HOST) ?? 'Website' }}
                    </a>
                @endif
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.72rem;">
                &copy; {{ date('Y') }} {{ $company->name ?? 'Shibaura Machine' }}. Precision Industrial Feedback Portal.
            </div>
        </footer>

    </div>

    <!-- Review Inspection Modal -->
    <div class="modal fade" id="modalVisitDetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark mb-0">Visitor Evaluation Breakdown</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalVisitBody">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Organizer Forgot Password Modal -->
    <div class="modal fade" id="modalOrgForgotPassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-shield-lock text-primary me-1"></i> Organizer Password Assistance
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="alertOrgForgot" class="alert d-none small"></div>
                    <p class="small text-muted mb-3">
                        Enter your registered mobile number or email address. We will dispatch password reset instructions to your verified account.
                    </p>
                    <form id="formOrgForgot" onsubmit="event.preventDefault(); submitOrgForgotPassword();">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Mobile Number or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope-at"></i></span>
                                <input type="text" id="inpOrgForgotLogin" class="form-control form-control-sm" placeholder="e.g. 9100000001 or ravi@plant.test" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnOrgForgotSubmit">
                                <i class="bi bi-send-fill me-1"></i> Send Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizer Change Password Modal -->
    <div class="modal fade" id="modalOrgChangePassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-key-fill text-primary me-1"></i> Change Organizer Password
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="alertOrgChangePass" class="alert d-none small"></div>
                    <form id="formOrgChangePass" onsubmit="event.preventDefault(); submitOrgChangePassword();">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Current Password</label>
                            <input type="password" id="inpOrgCurrentPass" class="form-control form-control-sm" placeholder="••••••••" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">New Password (min 8 chars)</label>
                            <input type="password" id="inpOrgNewPass" class="form-control form-control-sm" placeholder="••••••••" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                            <input type="password" id="inpOrgConfirmPass" class="form-control form-control-sm" placeholder="••••••••" minlength="8" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnOrgChangePassSubmit">
                                <i class="bi bi-check-circle-fill me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // CMS Banner Slider Controls
        let currentCmsSlide = 0;
        let cmsSlideInterval = null;

        function initCmsBannerSlider() {
            const slides = document.querySelectorAll('.cms-banner-slide');
            if (slides.length <= 1) return;

            cmsSlideInterval = setInterval(() => {
                moveCmsBanner(1);
            }, 5500);

            const wrap = document.getElementById('cmsBannerWrap');
            if (wrap) {
                wrap.addEventListener('mouseenter', () => clearInterval(cmsSlideInterval));
                wrap.addEventListener('mouseleave', () => {
                    clearInterval(cmsSlideInterval);
                    cmsSlideInterval = setInterval(() => moveCmsBanner(1), 5500);
                });
            }
        }

        function goToCmsBanner(index) {
            const slides = document.querySelectorAll('.cms-banner-slide');
            const dots = document.querySelectorAll('.cms-carousel-dot');
            if (!slides.length) return;

            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));

            currentCmsSlide = (index + slides.length) % slides.length;
            if (slides[currentCmsSlide]) slides[currentCmsSlide].classList.add('active');
            if (dots[currentCmsSlide]) dots[currentCmsSlide].classList.add('active');
        }

        function moveCmsBanner(delta) {
            goToCmsBanner(currentCmsSlide + delta);
        }

        document.addEventListener('DOMContentLoaded', initCmsBannerSlider);

        let currentSection = 1;
        const totalSections = {{ count($sections) }};
        let activeOrganizer = null;
        let countdownTimer = null;

        // Visitor data storage
        let visitorData = {};

        // Star rating labels
        const ratingLabels = {
            1: '1 Star — Unsatisfactory',
            2: '2 Stars — Needs Improvement',
            3: '3 Stars — Average / Met Expectations',
            4: '4 Stars — Good / Commendable',
            5: '5 Stars — Outstanding / World Class'
        };

        function selectStar(questionId, rating) {
            document.getElementById('inp_q_' + questionId).value = rating;
            const container = document.querySelector(`.star-rating-group[data-qid="${questionId}"]`);
            if (container) {
                const stars = container.querySelectorAll('.star-item-btn');
                stars.forEach(btn => {
                    const val = parseInt(btn.getAttribute('data-val'));
                    if (val <= rating) {
                        btn.classList.add('selected');
                    } else {
                        btn.classList.remove('selected');
                    }
                });
                const label = document.getElementById('star_label_' + questionId);
                if (label) {
                    label.innerText = ratingLabels[rating] || (rating + ' Stars');
                }
            }
        }

        function selectRadio(questionId, optionValue, element) {
            document.getElementById('inp_q_' + questionId).value = optionValue;
            const parent = element.closest('.choice-group');
            parent.querySelectorAll('.choice-option-pill').forEach(el => {
                el.classList.remove('active');
                const icon = el.querySelector('i');
                if (icon) icon.className = 'bi bi-circle me-2';
            });
            element.classList.add('active');
            const activeIcon = element.querySelector('i');
            if (activeIcon) activeIcon.className = 'bi bi-check-circle-fill me-2 text-primary';
        }

        function toggleCheckbox(questionId, optionValue, element) {
            element.classList.toggle('active');
            const icon = element.querySelector('i');
            if (element.classList.contains('active')) {
                if (icon) icon.className = 'bi bi-check-square-fill me-2 text-primary';
            } else {
                if (icon) icon.className = 'bi bi-square me-2';
            }

            const parent = element.closest('.choice-group');
            const selected = [];
            parent.querySelectorAll('.choice-option-pill.active').forEach(el => {
                selected.push(el.innerText.trim());
            });
            document.getElementById('inp_q_' + questionId).value = selected.join(', ');
        }

        function startSurvey() {
            visitorData = {
                visitor_name: document.getElementById('inpVisitorName').value.trim(),
                visitor_company: document.getElementById('inpVisitorCompany').value.trim(),
                visitor_mobile: document.getElementById('inpVisitorMobile').value.trim(),
                visitor_email: document.getElementById('inpVisitorEmail').value.trim(),
                visitor_designation: document.getElementById('inpVisitorDesignation').value.trim(),
                plant_id: document.getElementById('selPlant').value,
                organizer_id: document.getElementById('selOrganizer').value,
                purpose: document.getElementById('selPurpose').value,
            };

            document.getElementById('screenVisitorRegister').classList.add('hidden-screen');
            document.getElementById('screenSurveySections').classList.remove('hidden-screen');
            goToSection(1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goToSection(sectionNum) {
            // Validate required questions in current section before moving forward
            if (sectionNum > currentSection) {
                const currentCard = document.getElementById('surveySection' + currentSection);
                if (currentCard) {
                    const requiredBoxes = currentCard.querySelectorAll('.question-box[data-required="1"]');
                    for (const box of requiredBoxes) {
                        const qid = box.getAttribute('data-question-id');
                        const input = document.getElementById('inp_q_' + qid);
                        if (!input || !input.value.trim()) {
                            alert('Please complete the mandatory questions before proceeding to the next part.');
                            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        }
                    }
                }
            }

            for (let i = 1; i <= totalSections; i++) {
                const el = document.getElementById('surveySection' + i);
                if (el) el.classList.add('hidden-screen');
            }

            currentSection = sectionNum;
            const target = document.getElementById('surveySection' + currentSection);
            if (target) target.classList.remove('hidden-screen');

            const pct = Math.round((currentSection / totalSections) * 100);
            document.getElementById('txtProgressLabel').innerText = `Section ${currentSection} of ${totalSections}`;
            document.getElementById('txtProgressPct').innerText = `${pct}%`;
            document.getElementById('barProgressFill').style.width = `${pct}%`;

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function submitSurveyForm() {
            // Validate required questions in last section
            const currentCard = document.getElementById('surveySection' + currentSection);
            if (currentCard) {
                const requiredBoxes = currentCard.querySelectorAll('.question-box[data-required="1"]');
                for (const box of requiredBoxes) {
                    const qid = box.getAttribute('data-question-id');
                    const input = document.getElementById('inp_q_' + qid);
                    if (!input || !input.value.trim()) {
                        alert('Please complete the mandatory questions before submitting.');
                        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                }
            }

            // Gather all answers
            const answers = [];
            let overallRating = 5;
            let comments = '';

            document.querySelectorAll('.question-box').forEach(box => {
                const qid = box.getAttribute('data-question-id');
                const inp = document.getElementById('inp_q_' + qid);
                if (inp) {
                    answers.push({
                        question_id: parseInt(qid),
                        answer: inp.value.trim()
                    });

                    // Check if question asks for overall rating
                    const titleText = box.querySelector('.question-title')?.innerText || '';
                    if (titleText.toLowerCase().includes('overall') && inp.value) {
                        overallRating = parseInt(inp.value) || 5;
                    }
                    if (titleText.toLowerCase().includes('suggestions') || titleText.toLowerCase().includes('comments')) {
                        comments = inp.value;
                    }
                }
            });

            const payload = {
                ...visitorData,
                overall_rating: overallRating,
                comments: comments,
                answers: answers
            };

            try {
                const res = await fetch("{{ route('mobile.feedback') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showThankYouScreen(payload);
                } else {
                    alert(data.message || 'Error saving feedback. Please try again.');
                }
            } catch (err) {
                console.error(err);
                alert('Network issue encountered. Submitting locally...');
                showThankYouScreen(payload);
            }
        }

        function showThankYouScreen(payload) {
            document.getElementById('screenSurveySections').classList.add('hidden-screen');
            document.getElementById('screenThankYou').classList.remove('hidden-screen');

            document.getElementById('txtSummaryVisitor').innerText = payload.visitor_name;
            document.getElementById('txtSummaryCompany').innerText = payload.visitor_company;
            
            const stars = '★'.repeat(payload.overall_rating || 5);
            document.getElementById('txtSummaryRating').innerText = stars;

            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Auto-reset countdown
            let sec = 15;
            clearInterval(countdownTimer);
            countdownTimer = setInterval(() => {
                sec--;
                const el = document.getElementById('txtResetCountdown');
                if (el) el.innerText = `Auto-resetting in ${sec} seconds...`;
                if (sec <= 0) {
                    clearInterval(countdownTimer);
                    resetToNewVisitor();
                }
            }, 1000);
        }

        function resetToNewVisitor() {
            clearInterval(countdownTimer);
            document.getElementById('formVisitorDetails').reset();
            document.getElementById('formSurveyQuestions').reset();
            
            // Clear choices & stars
            document.querySelectorAll('.star-item-btn').forEach(b => b.classList.remove('selected'));
            document.querySelectorAll('.star-label-desc').forEach(l => l.innerText = 'Select 1 - 5');
            document.querySelectorAll('.choice-option-pill').forEach(c => {
                c.classList.remove('active');
                const i = c.querySelector('i');
                if (i) i.className = c.closest('.multiple-choice-group') ? 'bi bi-square me-2' : 'bi bi-circle me-2';
            });

            // If active organizer is present, keep them selected
            if (activeOrganizer) {
                const selOrg = document.getElementById('selOrganizer');
                if (selOrg) selOrg.value = activeOrganizer.id;
                const selPlant = document.getElementById('selPlant');
                if (selPlant && activeOrganizer.plant_id) selPlant.value = activeOrganizer.plant_id;
            }

            document.getElementById('screenThankYou').classList.add('hidden-screen');
            document.getElementById('screenSurveySections').classList.add('hidden-screen');
            document.getElementById('screenVisitorRegister').classList.remove('hidden-screen');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /* -------------------------------------------------------------
           ORGANIZER ACCESS & LOGIN
           ------------------------------------------------------------- */
        function toggleOrganizerMode() {
            if (activeOrganizer) {
                // Already logged in -> go to dashboard
                showScreen('screenOrganizerDashboard');
            } else {
                showScreen('screenOrganizerLogin');
            }
        }

        function switchToVisitorMode() {
            showScreen('screenVisitorRegister');
        }

        function showScreen(screenId) {
            ['screenVisitorRegister', 'screenSurveySections', 'screenThankYou', 'screenOrganizerLogin', 'screenOrganizerDashboard'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden-screen');
            });
            const target = document.getElementById(screenId);
            if (target) target.classList.remove('hidden-screen');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function handleOrganizerLogin() {
            const btn = document.getElementById('btnOrgSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

            const login = document.getElementById('inpOrgLogin').value.trim();
            const password = document.getElementById('inpOrgPassword').value;

            try {
                const res = await fetch("{{ route('mobile.organizer.login') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ login, password })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    activeOrganizer = data.user;
                    setupOrganizerDashboard(data);
                    showScreen('screenOrganizerDashboard');
                } else {
                    alert(data.message || 'Invalid credentials');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Authenticate & Enter';
            }
        }

        function setupOrganizerDashboard(data) {
            const u = data.user;
            document.getElementById('txtOrgName').innerText = u.name;
            document.getElementById('txtOrgPlantBadge').innerText = u.plant_code + ' — ' + (u.plant_name || 'HQ');
            document.getElementById('txtOrgRoleBadge').innerText = u.role;
            document.getElementById('txtOrgAvatar').innerText = u.name.substring(0, 2).toUpperCase();

            document.getElementById('statOrgTotalVisits').innerText = data.stats.total_visits;
            document.getElementById('statOrgTodayVisits').innerText = data.stats.today_visits;
            document.getElementById('statOrgAvgRating').innerText = data.stats.avg_rating + ' ★';

            document.getElementById('txtModeLabel').innerText = 'Organizer Console';

            loadOrganizerVisits();
        }

        async function loadOrganizerVisits() {
            const listEl = document.getElementById('containerOrgVisitsList');
            try {
                const res = await fetch("{{ route('mobile.organizer.visits') }}");
                const data = await res.json();
                if (data.success && data.visits) {
                    if (data.visits.length === 0) {
                        listEl.innerHTML = '<div class="text-center text-muted py-3 small">No visits recorded yet for this tour guide.</div>';
                        return;
                    }

                    listEl.innerHTML = data.visits.map(v => `
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark small">${v.visitor_name}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">${v.visitor_company || 'Corporate Visitor'} • ${v.visit_date}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark fw-bold">
                                    ★ ${v.feedback ? v.feedback.overall_rating : '—'}
                                </span>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function launchSurveyForCustomer() {
            // Pre-select organizer in the form
            if (activeOrganizer) {
                const selOrg = document.getElementById('selOrganizer');
                if (selOrg) selOrg.value = activeOrganizer.id;

                const selPlant = document.getElementById('selPlant');
                if (selPlant && activeOrganizer.plant_id) selPlant.value = activeOrganizer.plant_id;
            }

            resetToNewVisitor();
        }

        async function handleOrganizerLogout() {
            try {
                await fetch("{{ route('mobile.organizer.logout') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
            } catch (e) {}

            activeOrganizer = null;
            document.getElementById('txtModeLabel').innerText = 'Organizer Login';
            switchToVisitorMode();
        }

        function openOrgForgotModal() {
            const modal = new bootstrap.Modal(document.getElementById('modalOrgForgotPassword'));
            const alertBox = document.getElementById('alertOrgForgot');
            alertBox.className = 'alert d-none small';
            alertBox.innerText = '';
            document.getElementById('formOrgForgot').reset();
            modal.show();
        }

        async function submitOrgForgotPassword() {
            const btn = document.getElementById('btnOrgForgotSubmit');
            const alertBox = document.getElementById('alertOrgForgot');
            const login = document.getElementById('inpOrgForgotLogin').value.trim();

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
            alertBox.className = 'alert d-none small';

            try {
                const res = await fetch("{{ route('mobile.organizer.forgot_password') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ login })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    alertBox.className = 'alert alert-success small d-block';
                    let msg = data.message;
                    if (data.reset_url) {
                        msg += `<div class="mt-2"><a href="${data.reset_url}" target="_blank" class="fw-bold text-success text-decoration-underline">Click here to Reset Password Now &rarr;</a></div>`;
                    }
                    alertBox.innerHTML = msg;
                    document.getElementById('formOrgForgot').reset();
                } else {
                    alertBox.className = 'alert alert-danger small d-block';
                    alertBox.innerText = data.message || 'Error processing request.';
                }
            } catch (err) {
                alertBox.className = 'alert alert-danger small d-block';
                alertBox.innerText = 'Network error occurred. Please try again.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Reset Link';
            }
        }

        function openOrgChangePasswordModal() {
            const modal = new bootstrap.Modal(document.getElementById('modalOrgChangePassword'));
            const alertBox = document.getElementById('alertOrgChangePass');
            alertBox.className = 'alert d-none small';
            alertBox.innerText = '';
            document.getElementById('formOrgChangePass').reset();
            modal.show();
        }

        async function submitOrgChangePassword() {
            const btn = document.getElementById('btnOrgChangePassSubmit');
            const alertBox = document.getElementById('alertOrgChangePass');
            const current_password = document.getElementById('inpOrgCurrentPass').value;
            const password = document.getElementById('inpOrgNewPass').value;
            const password_confirmation = document.getElementById('inpOrgConfirmPass').value;

            if (password !== password_confirmation) {
                alertBox.className = 'alert alert-danger small d-block';
                alertBox.innerText = 'New password and confirmation do not match.';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            alertBox.className = 'alert d-none small';

            try {
                const res = await fetch("{{ route('mobile.organizer.change_password') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        current_password,
                        password,
                        password_confirmation
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    alertBox.className = 'alert alert-success small d-block';
                    alertBox.innerText = data.message || 'Password updated successfully!';
                    document.getElementById('formOrgChangePass').reset();
                    setTimeout(() => {
                        const modalEl = document.getElementById('modalOrgChangePassword');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }, 2000);
                } else {
                    alertBox.className = 'alert alert-danger small d-block';
                    alertBox.innerText = data.message || 'Error updating password.';
                }
            } catch (err) {
                alertBox.className = 'alert alert-danger small d-block';
                alertBox.innerText = 'Network error occurred.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Update Password';
            }
        }
    </script>
</body>
</html>
