<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Generator Monitor') - Enterprise Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom-theme.css') }}" rel="stylesheet">
    <style>
        :root {
            /* Star Admin 2 Pro Color Palette */
            --primary-color: #F29F67;
            --primary-dark: #1E1E2C;
            --secondary-blue: #3B8FF3;
            --secondary-teal: #34B1AA;
            --accent-yellow: #E0B50F;

            /* Gradients based on the color palette */
            --primary-gradient: linear-gradient(135deg, #F29F67 0%, #E0B50F 100%);
            --secondary-gradient: linear-gradient(135deg, #3B8FF3 0%, #34B1AA 100%);
            --success-gradient: linear-gradient(135deg, #34B1AA 0%, #3B8FF3 100%);
            --warning-gradient: linear-gradient(135deg, #E0B50F 0%, #F29F67 100%);
            --danger-gradient: linear-gradient(135deg, #F29F67 0%, #1E1E2C 100%);
            --info-gradient: linear-gradient(135deg, #3B8FF3 0%, #34B1AA 100%);
            --dark-gradient: linear-gradient(135deg, #1E1E2C 0%, #2a2a3a 100%);

            /* Glass effects with Athens grey */
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.18);
            --glass-bg-dark: rgba(30, 30, 44, 0.8);
            --glass-border-dark: rgba(242, 159, 103, 0.2);

            /* Shadows with primary color tint */
            --shadow-light: 0 8px 32px rgba(242, 159, 103, 0.12);
            --shadow-medium: 0 12px 40px rgba(242, 159, 103, 0.18);
            --shadow-heavy: 0 20px 60px rgba(242, 159, 103, 0.25);

            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-bs-theme="dark"] {
            --glass-bg: rgba(30, 30, 44, 0.85);
            --glass-border: rgba(242, 159, 103, 0.25);
            --shadow-light: 0 8px 32px rgba(242, 159, 103, 0.15);
            --shadow-medium: 0 12px 40px rgba(242, 159, 103, 0.22);
            --shadow-heavy: 0 20px 60px rgba(242, 159, 103, 0.3);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #1E1E2C 0%, #2a2a3a 50%, #3a3a4a 100%);
        }

        /* Modern Navbar */
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(242, 159, 103, 0.2);
            box-shadow: 0 2px 20px rgba(242, 159, 103, 0.1);
            transition: var(--transition);
        }

        [data-bs-theme="dark"] .navbar-modern {
            background: rgba(30, 30, 44, 0.95);
            border-bottom: 1px solid rgba(242, 159, 103, 0.3);
            box-shadow: 0 2px 20px rgba(242, 159, 103, 0.2);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: rgba(30, 30, 44, 0.8) !important;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.75rem 1.25rem !important;
            border-radius: 12px;
            margin: 0 0.25rem;
            position: relative;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(242, 159, 103, 0.1);
            transition: var(--transition);
            z-index: -1;
            border-radius: 12px;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            left: 0;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-orange) !important;
            transform: translateY(-2px);
        }

        [data-bs-theme="dark"] .nav-link:hover,
        [data-bs-theme="dark"] .nav-link.active {
            color: #F29F67 !important;
        }

        /* User Dropdown */
        .user-dropdown {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(242, 159, 103, 0.2);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px rgba(242, 159, 103, 0.15);
        }

        [data-bs-theme="dark"] .user-dropdown {
            background: rgba(30, 30, 44, 0.95);
            border: 1px solid rgba(242, 159, 103, 0.3);
            box-shadow: 0 8px 32px rgba(242, 159, 103, 0.25);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(242, 159, 103, 0.25);
            transition: var(--transition);
        }

        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(242, 159, 103, 0.35);
        }

        /* User info text colors */
        .navbar-nav .text-white {
            color: rgba(30, 30, 44, 0.8) !important;
        }

        .navbar-nav .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        [data-bs-theme="dark"] .navbar-nav .text-white {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        [data-bs-theme="dark"] .navbar-nav .text-white-50 {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Main Content */
        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }

        /* Modern Cards */
        .card-modern {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
        }

        .card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .card-modern:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        /* Card text colors for light theme */
        .card-modern .text-white {
            color: var(--primary-dark) !important;
        }

        .card-modern .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        .card-modern h1,
        .card-modern h2,
        .card-modern h3,
        .card-modern h4,
        .card-modern h5,
        .card-modern h6 {
            color: var(--primary-dark) !important;
        }

        [data-bs-theme="dark"] .card-modern .text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .card-modern .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-bs-theme="dark"] .card-modern h1,
        [data-bs-theme="dark"] .card-modern h2,
        [data-bs-theme="dark"] .card-modern h3,
        [data-bs-theme="dark"] .card-modern h4,
        [data-bs-theme="dark"] .card-modern h5,
        [data-bs-theme="dark"] .card-modern h6 {
            color: white !important;
        }

        /* Status Cards */
        .status-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            transition: var(--transition);
        }

        .status-online::before {
            background: var(--success-gradient);
        }

        .status-offline::before {
            background: var(--danger-gradient);
        }

        .status-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: var(--shadow-heavy);
        }

        /* Status card text colors for light theme */
        .status-card .text-white {
            color: var(--primary-dark) !important;
        }

        .status-card .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        .status-card h1,
        .status-card h2,
        .status-card h3,
        .status-card h4,
        .status-card h5,
        .status-card h6 {
            color: var(--primary-dark) !important;
        }

        [data-bs-theme="dark"] .status-card .text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .status-card .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-bs-theme="dark"] .status-card h1,
        [data-bs-theme="dark"] .status-card h2,
        [data-bs-theme="dark"] .status-card h3,
        [data-bs-theme="dark"] .status-card h4,
        [data-bs-theme="dark"] .status-card h5,
        [data-bs-theme="dark"] .status-card h6 {
            color: white !important;
        }

        /* Tables */
        .table-modern {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .table-modern thead {
            background: var(--primary-gradient);
            color: white;
        }

        .table-modern tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid var(--glass-border);
        }

        .table-modern tbody tr:hover {
            background: var(--glass-bg);
            transform: scale(1.01);
        }

        /* Table text colors for light theme */
        .table-modern tbody .text-white {
            color: var(--primary-dark) !important;
        }

        .table-modern tbody .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        [data-bs-theme="dark"] .table-modern tbody .text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .table-modern tbody .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        /* Buttons */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: var(--transition);
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Badges */
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success-modern {
            background: var(--success-gradient);
            color: white;
        }

        .badge-danger-modern {
            background: var(--danger-gradient);
            color: white;
        }

        .badge-warning-modern {
            background: var(--warning-gradient);
            color: white;
        }

        .badge-info-modern {
            background: var(--secondary-gradient);
            color: white;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out;
        }

        /* Theme Toggle */
        .theme-toggle {
            background: rgba(59, 143, 243, 0.1);
            border: 1px solid rgba(59, 143, 243, 0.2);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            cursor: pointer;
            color: #3B8FF3;
        }

        .theme-toggle:hover {
            background: rgba(59, 143, 243, 0.2);
            border-color: rgba(59, 143, 243, 0.4);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(59, 143, 243, 0.25);
        }

        [data-bs-theme="dark"] .theme-toggle {
            background: rgba(242, 159, 103, 0.1);
            border-color: rgba(242, 159, 103, 0.2);
            color: #F29F67;
        }

        [data-bs-theme="dark"] .theme-toggle:hover {
            background: rgba(242, 159, 103, 0.2);
            border-color: rgba(242, 159, 103, 0.4);
            box-shadow: 0 4px 15px rgba(242, 159, 103, 0.25);
        }

        /* Refresh Indicator */
        .refresh-indicator {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            color: var(--primary-dark);
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: var(--shadow-light);
            transition: var(--transition);
        }

        .refresh-indicator:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        [data-bs-theme="dark"] .refresh-indicator {
            color: white;
        }

        /* Page Header */
        .page-header-modern {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
        }

        .page-header-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        /* Light theme text colors */
        .page-header-modern h1,
        .page-header-modern .display-4,
        .page-header-modern .lead,
        .page-header-modern .text-white {
            color: var(--primary-dark) !important;
        }

        .page-header-modern .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        [data-bs-theme="dark"] .page-header-modern h1,
        [data-bs-theme="dark"] .page-header-modern .display-4,
        [data-bs-theme="dark"] .page-header-modern .lead,
        [data-bs-theme="dark"] .page-header-modern .text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .page-header-modern .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        /* Additional light theme text fixes */
        .text-white {
            color: var(--primary-dark) !important;
        }

        .text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        .form-label.text-white-50 {
            color: rgba(30, 30, 44, 0.6) !important;
        }

        .form-label.text-white {
            color: var(--primary-dark) !important;
        }

        .btn-outline-light {
            color: var(--primary-dark) !important;
            border-color: rgba(30, 30, 44, 0.2) !important;
        }

        .btn-outline-light:hover {
            color: white !important;
            background-color: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
        }

        /* Dark theme overrides for additional fixes */
        [data-bs-theme="dark"] .text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-bs-theme="dark"] .form-label.text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-bs-theme="dark"] .form-label.text-white {
            color: white !important;
        }

        [data-bs-theme="dark"] .btn-outline-light {
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        [data-bs-theme="dark"] .btn-outline-light:hover {
            color: var(--primary-dark) !important;
            background-color: white !important;
            border-color: white !important;
        }

        /* Breadcrumb */
        .breadcrumb-modern {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-modern .breadcrumb-item {
            color: rgba(30, 30, 44, 0.7);
        }

        .breadcrumb-modern .breadcrumb-item.active {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: rgba(30, 30, 44, 0.5);
            font-weight: 600;
        }

        [data-bs-theme="dark"] .breadcrumb-modern .breadcrumb-item {
            color: rgba(255, 255, 255, 0.7);
        }

        [data-bs-theme="dark"] .breadcrumb-modern .breadcrumb-item.active {
            color: white;
        }

        [data-bs-theme="dark"] .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-top: 70px;
                padding: 1rem 0;
            }

            .card-modern {
                margin-bottom: 1rem;
            }

            .refresh-indicator {
                top: 80px;
                right: 10px;
                font-size: 0.75rem;
                padding: 0.5rem 0.75rem;
            }
        }

        /* Loading States */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-modern fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand animate-fadeInUp" href="{{ route('dashboard') }}">
                <i class="fas fa-bolt me-2"></i>
                GeneratorPro
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logs') ? 'active' : '' }}" href="{{ route('logs') }}">
                            <i class="fas fa-chart-line me-2"></i>
                            Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('write-logs') ? 'active' : '' }}" href="{{ route('write-logs') }}">
                            <i class="fas fa-database me-2"></i>
                            Data Logs
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <div class="theme-toggle" onclick="toggleTheme()">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar me-3">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ Auth::user()->name }}</span>
                                <small class="opacity-75">Administrator</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                            <li>
                                <div class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3" style="width: 50px; height: 50px;">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                            <small class="text-muted">{{ Auth::user()->email }}</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showProfileModal()">
                                    <i class="fas fa-user-circle me-2"></i>
                                    Profile Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showSettingsModal()">
                                    <i class="fas fa-cog me-2"></i>
                                    Preferences
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Auto-refresh indicator -->
    <div class="refresh-indicator animate-slideInRight">
        <i class="fas fa-sync-alt me-2 animate-pulse"></i>
        <span id="refreshText">Auto-refresh every 30s</span>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Enhanced Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content card-modern">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle me-2"></i>
                        Profile Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="user-avatar mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                            <p class="text-muted mb-0">{{ Auth::user()->email }}</p>
                            <span class="badge badge-success-modern badge-modern mt-2">Administrator</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="card card-modern h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                            <h6>Member Since</h6>
                                            <small class="text-muted">{{ Auth::user()->created_at->format('M Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card card-modern h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                            <h6>Access Level</h6>
                                            <small class="text-muted">Full Access</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card card-modern h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                            <h6>Last Login</h6>
                                            <small class="text-muted">Just now</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card card-modern h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-bolt fa-2x text-info mb-2"></i>
                                            <h6>Generators</h6>
                                            <small class="text-muted">All Monitored</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-modern" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content card-modern">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-cog me-2"></i>
                        System Preferences
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Auto-refresh Interval</label>
                        <select class="form-select" id="refreshInterval">
                            <option value="10">10 seconds</option>
                            <option value="30" selected>30 seconds</option>
                            <option value="60">1 minute</option>
                            <option value="300">5 minutes</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="soundAlerts" checked>
                            <label class="form-check-label fw-bold" for="soundAlerts">
                                Enable sound alerts
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="notifications">
                            <label class="form-check-label fw-bold" for="notifications">
                                Push notifications
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="animations" checked>
                            <label class="form-check-label fw-bold" for="animations">
                                Enable animations
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-modern" onclick="saveSettings()">Save Settings</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <!-- Fallback Chart.js CDN -->
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"><\/script>');
        }
    </script>
    <script>
        // Ensure Chart.js is loaded before initializing
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js failed to load');
                // Retry loading Chart.js
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js';
                script.onload = function() {
                    console.log('Chart.js loaded successfully');
                };
                script.onerror = function() {
                    console.error('Failed to load Chart.js');
                };
                document.head.appendChild(script);
            } else {
                console.log('Chart.js is available');
            }
        });
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');

            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        document.getElementById('themeIcon').className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        // Modal functions
        function showProfileModal() {
            new bootstrap.Modal(document.getElementById('profileModal')).show();
        }

        function showSettingsModal() {
            new bootstrap.Modal(document.getElementById('settingsModal')).show();
        }

        // Auto-refresh functionality
        let refreshInterval = 30000; // 30 seconds default

        function refreshData() {
            // This will be overridden by individual pages
            console.log('Refreshing data...');
        }

        function updateRefreshIndicator() {
            const refreshText = document.getElementById('refreshText');
            const interval = refreshInterval / 1000;
            refreshText.textContent = `Auto-refresh every ${interval}s`;
        }

        setInterval(function() {
            refreshData();
        }, refreshInterval);

        // Settings change handlers
        document.getElementById('refreshInterval').addEventListener('change', function() {
            refreshInterval = parseInt(this.value) * 1000;
            clearInterval(window.refreshTimer);
            window.refreshTimer = setInterval(refreshData, refreshInterval);
            updateRefreshIndicator();
        });

        function saveSettings() {
            // Save settings logic here
            new bootstrap.Modal(document.getElementById('settingsModal')).hide();
            // Show success message
            showNotification('Settings saved successfully!', 'success');
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Initial refresh on page load
        $(document).ready(function() {
            refreshData();
            updateRefreshIndicator();

            // Add loading states to cards
            $('.card-modern').addClass('loading');
            setTimeout(() => {
                $('.card-modern').removeClass('loading');
            }, 1000);
        });
    </script>
    @yield('scripts')
</body>
</html>
