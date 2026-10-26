<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visitor Feedback Home — {{ $company->name ?? 'Shibaura Machine India' }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --shibaura-blue: #0047ba;
            --shibaura-dark: #002b70;
            --shibaura-gradient: linear-gradient(135deg, #002b70 0%, #0047ba 100%);
            --shibaura-accent: #00a3e0;
            --bg-page: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        /* Top Bar */
        .portal-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .portal-brand-logo {
            height: 42px;
            object-fit: contain;
        }

        .hero-banner-wrap {
            background: var(--shibaura-gradient);
            color: #ffffff;
            border-radius: 20px;
            padding: 40px 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(0, 43, 112, 0.18);
        }

        .hero-banner-wrap::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(0, 163, 224, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .search-container-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--card-border);
            margin-top: -30px;
            position: relative;
            z-index: 10;
        }

        .visitor-pass-input {
            border: 2px solid #cbd5e1;
            border-radius: 12px;
            font-size: 1.15rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            transition: all 0.2s ease;
        }

        .visitor-pass-input:focus {
            border-color: var(--shibaura-blue);
            box-shadow: 0 0 0 4px rgba(0, 71, 186, 0.15);
        }

        .btn-portal-primary {
            background: var(--shibaura-gradient);
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-size: 1.05rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-portal-primary:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 71, 186, 0.3);
        }

        .btn-organizer-link {
            background: rgba(255, 255, 255, 0.9);
            color: var(--shibaura-dark);
            border: 1px solid #cbd5e1;
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.88rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-organizer-link:hover {
            background: #f1f5f9;
            color: var(--shibaura-blue);
            border-color: #94a3b8;
        }

        .visitor-card-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .visitor-card-item:hover {
            border-color: var(--shibaura-blue);
            box-shadow: 0 6px 18px rgba(0, 71, 186, 0.08);
            transform: translateY(-2px);
        }

        .badge-portal-awaiting {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            font-weight: 600;
            font-size: 0.78rem;
            border-radius: 6px;
            padding: 4px 10px;
        }

        .badge-portal-done {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            font-size: 0.78rem;
            border-radius: 6px;
            padding: 4px 10px;
        }

        .carousel-banner-img {
            max-height: 220px;
            width: 100%;
            object-fit: cover;
            border-radius: 16px;
        }

        .live-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-ring 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
            display: inline-block;
        }

        @keyframes pulse-ring {
            to {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
        }
    </style>
</head>
<body>

    <!-- ── Portal Header ── -->
    <header class="portal-header">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('visitor.home') }}" class="text-decoration-none d-flex align-items-center gap-3">
                    <img src="{{ asset('images/shibaura-logo-crisp.webp') }}" 
                         alt="{{ $company->name ?? 'Shibaura Machine' }}" 
                         class="portal-brand-logo"
                         onerror="this.src='{{ asset('images/logo.png') }}'">
                    <div>
                        <div class="fw-bold text-dark fs-5 brand-font mb-0" style="letter-spacing: -0.3px;">
                            {{ $company->name ?? 'Shibaura Machine India' }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.76rem; font-weight: 500;">
                            Plant Experience & Visitor Feedback Terminal
                        </div>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border small">
                    <span class="live-pulse"></span>
                    <span class="fw-semibold text-secondary">{{ now()->format('l, d M Y') }}</span>
                </div>

                <a href="{{ route('organizer.login') }}" class="btn-organizer-link" id="btnOrganizerPortal">
                    <i class="fa-solid fa-user-shield text-primary"></i>
                    <span>Organizer Portal</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ── Main Content Area ── -->
    <main class="container my-4 flex-grow-1">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Submission Successful</h6>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error') || isset($searchError))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Notice</h6>
                        <div>{{ session('error') ?? $searchError }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-info-circle-fill fs-3 text-info"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Feedback Information</h6>
                        <div>{{ session('info') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Search Result Context Card (If already submitted or expired) -->
        @if(isset($searchResult))
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-4" style="border-left: 5px solid {{ $searchResult->feedback ? '#22c55e' : '#f59e0b' }} !important;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge {{ $searchResult->feedback ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-1">
                                {{ $searchResult->feedback ? '✓ Feedback Already Submitted' : 'Feedback Expired' }}
                            </span>
                            @if($searchResult->visitor_code)
                                <span class="badge bg-light text-dark border"><i class="bi bi-qr-code me-1"></i>{{ $searchResult->visitor_code }}</span>
                            @endif
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ $searchResult->visitor_name }}</h4>
                        <div class="text-secondary small">{{ $searchResult->visitor_designation ?: 'Visitor' }} • <strong>{{ $searchResult->visitor_company }}</strong></div>
                        <div class="text-muted small mt-1">
                            Plant: <strong>{{ $searchResult->plant?->name ?? 'General Plant' }}</strong> | 
                            Host Organizer: <strong>{{ $searchResult->organizer?->name ?? 'Staff Escort' }}</strong>
                        </div>
                    </div>
                    <div>
                        @if($searchResult->feedback)
                            <div class="text-end">
                                <div class="text-success fw-bold mb-1"><i class="bi bi-check2-circle me-1"></i>Recorded at {{ $searchResult->feedback->submitted_at ? $searchResult->feedback->submitted_at->format('h:i A') : 'Today' }}</div>
                                <div class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                                    Overall Rating: {{ $searchResult->feedback->overall_rating }} / 5 ★
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- ── Hero Banner Section ── -->
        <div class="hero-banner-wrap mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-1 mb-3 rounded-pill fw-semibold">
                        <i class="fa-solid fa-industry me-1"></i> Japanese Engineering Excellence
                    </span>
                    <h1 class="display-6 fw-bold mb-2">Welcome to Your Plant Tour Experience</h1>
                    <p class="lead opacity-90 mb-0" style="font-size: 1.05rem;">
                        Thank you for visiting Shibaura Machine India. Please share your valuable tour feedback to help us maintain the highest manufacturing and hospitality standards.
                    </p>
                </div>
                <div class="col-lg-5 text-center text-lg-end">
                    <img src="{{ asset('images/shibaura-logo-crisp.webp') }}" 
                         alt="Shibaura Machine" 
                         style="max-height: 85px; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.25));"
                         onerror="this.src='{{ asset('images/logo.png') }}'">
                </div>
            </div>
        </div>

        <!-- ── Visitor Lookup Bar ── -->
        <div class="search-container-card mb-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <label class="form-label fw-bold text-dark mb-1" for="visitor_code_search">
                        <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i> Find Your Visit & Start Feedback
                    </label>
                    <form action="{{ route('visitor.lookup') }}" method="GET" class="d-flex flex-wrap flex-md-nowrap gap-2">
                        <input type="text" 
                               name="code" 
                               id="visitor_code_search"
                               class="form-control visitor-pass-input" 
                               placeholder="Enter Visitor Pass Code (e.g. VIS-2026-0091) or Phone..." 
                               value="{{ $searchQuery ?? '' }}"
                               minlength="2"
                               maxlength="50"
                               required
                               autocomplete="off">
                        <button type="submit" class="btn btn-portal-primary px-4 text-nowrap">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Start Feedback</span>
                        </button>
                    </form>
                    <div class="small text-muted mt-2">
                        <i class="bi bi-info-circle me-1"></i> Scan or enter the visitor pass code provided by your tour escort, or tap your name below.
                    </div>
                </div>

                <div class="col-lg-4 border-start-lg ps-lg-4 text-center text-lg-start">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark small mb-1">
                            <i class="fa-solid fa-headset text-primary me-1"></i> Staff / Escort Access
                        </div>
                        <div class="text-muted small mb-2" style="font-size: 0.8rem;">
                            Authorized tour organizers can log in to submit evaluations on behalf of visitors.
                        </div>
                        <a href="{{ route('organizer.login') }}" class="btn btn-sm btn-outline-primary w-100 fw-bold">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Organizer Login
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Today's Scheduled Visits Roster (Direct Kiosk Selection) ── -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </span>
                <div>
                    <h4 class="fw-bold text-dark mb-0">Today's Scheduled Visitor Experiences</h4>
                    <div class="text-muted small">Select your name to directly launch your feedback questionnaire</div>
                </div>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                {{ $todayVisits->count() }} Registered Visits Today
            </span>
        </div>

        @if($todayVisits->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3">
                <i class="fa-solid fa-calendar-day text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold text-dark">No Registered Visits for Today Yet</h5>
                <p class="text-secondary small mb-3">Today's guest registration roster is currently empty. Tour organizers can register new visitors through the Organizer Portal.</p>
                <div>
                    <a href="{{ route('organizer.login') }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
                        <i class="fa-solid fa-user-shield me-1"></i> Go to Organizer Portal
                    </a>
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach($todayVisits as $visit)
                    @php
                        $hasFeedback = !is_null($visit->feedback);
                        $isExpired = $visit->is_feedback_expired;
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="visitor-card-item h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    @if($hasFeedback)
                                        <span class="badge-portal-done">
                                            <i class="fa-solid fa-circle-check me-1"></i> Feedback Done
                                        </span>
                                    @elseif($isExpired)
                                        <span class="badge bg-secondary-subtle text-secondary border small">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Expired
                                        </span>
                                    @else
                                        <span class="badge-portal-awaiting">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> Awaiting Feedback
                                        </span>
                                    @endif

                                    @if($visit->visitor_code)
                                        <span class="badge bg-light text-primary border small font-monospace">
                                            <i class="bi bi-qr-code me-1"></i>{{ $visit->visitor_code }}
                                        </span>
                                    @endif
                                </div>

                                <h5 class="fw-bold text-dark mb-1">{{ $visit->visitor_name }}</h5>
                                <div class="text-secondary fw-semibold small mb-2">
                                    {{ $visit->visitor_designation ?: 'Visitor' }} • <span class="text-dark">{{ $visit->visitor_company }}</span>
                                </div>

                                <div class="bg-light p-2 rounded-3 border small mb-3" style="font-size: 0.78rem;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Host Organizer:</span>
                                        <span class="fw-bold text-dark">{{ $visit->organizer?->name ?? 'Assigned Staff' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Plant / Unit:</span>
                                        <span class="fw-bold text-dark">{{ $visit->plant?->code ?? ($visit->plant?->name ?? 'Plant Facility') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                @if(!$hasFeedback && !$isExpired)
                                    <a href="{{ route('visits.feedback.create', $visit) }}" 
                                       class="btn btn-portal-primary w-100 py-2 fs-6">
                                        <span>Submit Feedback</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                @elseif($hasFeedback)
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-success-subtle rounded-3 text-success small border border-success-subtle">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i> {{ $visit->feedback->overall_rating }} / 5 Stars</span>
                                        <span class="fw-bold">Feedback Recorded</span>
                                    </div>
                                @else
                                    <button class="btn btn-secondary w-100 py-2 fs-6" disabled>
                                        Visit Day Expired
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- ── Portal Footer ── -->
    <footer class="mt-auto py-4 bg-white border-top">
        <div class="container text-center text-md-start">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <div class="fw-bold text-dark small">{{ $company->name ?? 'Shibaura Machine India Private Limited' }}</div>
                    <div class="text-muted small">{{ $company->address ?? 'Chennai-Bangalore Highway, Chembarambakkam, Chennai, Tamil Nadu' }}</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-3 small">
                        <a href="{{ route('visitor.home') }}" class="text-decoration-none text-secondary">Visitor Feedback</a>
                        <span class="text-muted">•</span>
                        <a href="{{ route('organizer.login') }}" class="text-decoration-none text-secondary fw-semibold">Organizer Portal</a>
                        <span class="text-muted">•</span>
                        <a href="{{ route('login') }}" class="text-decoration-none text-secondary">System Login</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
