<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — Shibaura Plant Pulse</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <!-- Typography: Inter, Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-heading: 'Inter', 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background: radial-gradient(circle at 10% 20%, #0f172a 0%, #020617 90%);
            min-height: 100vh;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            opacity: 0.45;
        }
        .orb-1 {
            width: 450px;
            height: 450px;
            background: #06539d;
            top: -100px;
            left: -100px;
        }
        .orb-2 {
            width: 400px;
            height: 400px;
            background: #0284c7;
            bottom: -80px;
            right: -80px;
        }

        .login-card-container {
            width: 100%;
            max-width: 920px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .hero-side {
            background: linear-gradient(135deg, rgba(6, 83, 157, 0.22) 0%, rgba(2, 132, 199, 0.06) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(6, 83, 157, 0.25);
            border: 1px solid rgba(56, 189, 248, 0.4);
            color: #38bdf8;
            font-size: 0.8rem;
            font-weight: 600;
            width: fit-content;
        }

        .form-side {
            padding: 3.5rem 3.25rem;
            background: rgba(15, 23, 42, 0.85);
        }

        .input-group-modern {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-group-modern .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .input-group-modern input {
            width: 100%;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.85rem;
            color: #ffffff;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .input-group-modern input:focus {
            background: rgba(30, 41, 59, 0.95);
            border-color: #06539d;
            box-shadow: 0 0 0 3px rgba(6, 83, 157, 0.3);
            outline: none;
            color: #ffffff;
        }

        .input-group-modern input:focus + .input-icon {
            color: #38bdf8;
        }

        .btn-brand-submit {
            background: linear-gradient(135deg, #06539d 0%, #033a72 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.8rem;
            border-radius: 12px;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 18px rgba(6, 83, 157, 0.45);
        }

        .btn-brand-submit:hover {
            background: linear-gradient(135deg, #054685 0%, #022b54 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(6, 83, 157, 0.55);
        }

        .btn-ghost-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 0.92rem;
            width: 100%;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-ghost-back:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .hero-side { display: none; }
            .form-side { padding: 2.25rem 1.75rem; }
        }
    </style>
</head>
<body>

<div class="ambient-orb orb-1"></div>
<div class="ambient-orb orb-2"></div>

<div class="login-card-container">
    <div class="row g-0">
        <!-- Hero Column -->
        <div class="col-lg-5 hero-side">
            <div>
                <div class="brand-badge-pill mb-4">
                    <i class="bi bi-key-fill"></i>
                    <span>Credential Recovery</span>
                </div>
                <h2 class="text-white fw-bold mb-3" style="font-family: var(--font-heading); font-size: 2.1rem; letter-spacing: -0.03em;">
                    Reset & Recover Account Access.
                </h2>
                <p class="text-slate-400" style="color: #94a3b8; line-height: 1.6;">
                    Enterprise password recovery system. Enter your registered email or mobile number to generate a secure reset link.
                </p>
            </div>

            <div class="pt-4 border-top border-white border-opacity-10">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.2); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 1.3rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold small">Secure Verification</div>
                        <div class="small" style="color: #64748b;">Timed cryptographic reset tokens</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Column -->
        <div class="col-lg-7 form-side">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="background: white; border-radius: 10px; padding: 6px 14px; display: inline-flex; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                        <img src="{{ asset('images/shibaura-logo-cropped.webp') }}" alt="Shibaura Machine" style="max-height: 28px; width: auto;">
                    </div>
                    <span class="badge rounded-pill bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1 small">Account Assistance</span>
                </div>
                <h4 class="text-white fw-bold mb-1">Forgot your password?</h4>
                <p class="small text-slate-400" style="color: #94a3b8;">
                    Enter your registered email address or mobile number. We'll send you instructions to create a new password.
                </p>
            </div>

            @if(session('status'))
                <div class="alert alert-success py-3 px-3 rounded-3 small mb-3 border-0 text-white" style="background: rgba(16, 185, 129, 0.9);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill fs-5 mt-n1"></i>
                        <div>
                            <strong>Reset Link Sent!</strong>
                            <div>{{ session('status') }}</div>
                        </div>
                    </div>
                </div>

                @if(session('dev_reset_url'))
                    <div class="p-3 mb-3 rounded-3" style="background: rgba(56, 189, 248, 0.15); border: 1px dashed rgba(56, 189, 248, 0.4);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary">Local Dev Mode</span>
                            <span class="small text-slate-300">Quick Test Reset Link:</span>
                        </div>
                        <a href="{{ session('dev_reset_url') }}" class="btn btn-sm btn-info text-dark fw-bold w-100 text-truncate">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open Password Reset Form
                        </a>
                    </div>
                @endif
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 rounded-3 small mb-3 border-0 bg-danger text-white">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label text-slate-300 small fw-semibold" style="color: #cbd5e1;">Registered Email or Mobile</label>
                    <div class="input-group-modern">
                        <input id="loginInput" name="login" value="{{ old('login') }}" placeholder="e.g. superadmin@plant.test or 9100000001" required autofocus autocomplete="username">
                        <i class="bi bi-envelope-at-fill input-icon"></i>
                    </div>
                    <div class="form-text small" style="color: #64748b;">
                        We will verify your user record and send a secure reset link.
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-3">
                    <button type="submit" class="btn-brand-submit">
                        <i class="bi bi-send-fill me-1"></i> Email Password Reset Link
                    </button>
                    <a href="{{ route('login') }}" class="btn-ghost-back">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
