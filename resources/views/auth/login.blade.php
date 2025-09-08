<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Generator Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Portal Color Palette */
            --primary-color: #F29F67;
            --primary-dark: #1E1E2C;
            --secondary-blue: #3B8FF3;
            --secondary-teal: #34B1AA;
            --accent-yellow: #E0B50F;

            /* Gradients based on the portal color palette */
            --primary-gradient: linear-gradient(135deg, #F29F67 0%, #E0B50F 100%);
            --secondary-gradient: linear-gradient(135deg, #3B8FF3 0%, #34B1AA 100%);
            --dark-gradient: linear-gradient(135deg, #1E1E2C 0%, #2a2a3a 100%);

            /* Glass effects */
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.18);
            --glass-bg-dark: rgba(30, 30, 44, 0.8);
            --glass-border-dark: rgba(242, 159, 103, 0.2);

            /* Shadows with primary color tint */
            --shadow-light: 0 8px 32px rgba(242, 159, 103, 0.12);
            --shadow-medium: 0 12px 40px rgba(242, 159, 103, 0.18);
            --shadow-heavy: 0 20px 60px rgba(242, 159, 103, 0.25);

            --text-primary: #1E1E2C;
            --text-secondary: #666666;
            --text-light: #999999;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --border-color: #e2e8f0;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating particles background */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 120px; height: 120px; top: 60%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 60px; height: 60px; top: 80%; left: 20%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 100px; height: 100px; top: 10%; left: 70%; animation-delay: 1s; }
        .particle:nth-child(5) { width: 40px; height: 40px; top: 40%; left: 50%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
        }

        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-heavy);
            position: relative;
            z-index: 10;
            max-width: 450px;
            width: 100%;
            margin: 20px;
            animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: var(--radius-xl);
            z-index: -1;
        }

        .login-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
            position: relative;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.1; }
            100% { transform: scale(1); opacity: 0.3; }
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .login-body {
            padding: 0 2.5rem 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
            width: 16px;
        }

        .input-container {
            position: relative;
            background: rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .input-container:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(242, 159, 103, 0.2);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1rem;
            z-index: 2;
            transition: var(--transition-fast);
        }

        .input-container:focus-within .input-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .form-control {
            background: transparent;
            border: none;
            padding: 1rem 1rem 1rem 3rem;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
            border-radius: var(--radius-md);
            transition: var(--transition);
            width: 100%;
        }

        .form-control::placeholder {
            color: var(--text-light);
            font-weight: 400;
        }

        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.9);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition-fast);
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .form-check {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            background: transparent;
            margin-right: 0.75rem;
            transition: var(--transition);
        }

        .form-check-input:checked {
            background: var(--primary-gradient);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-primary);
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: var(--radius-md);
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            top: 50%;
            left: 50%;
            margin-left: -12px;
            margin-top: -12px;
            border: 3px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            border-radius: var(--radius-md);
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .security-info {
            text-align: center;
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .security-info small {
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-info i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .login-container {
                margin: 10px;
                max-width: none;
            }

            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .login-body {
                padding: 0 1.5rem 2rem;
            }

            .logo-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }

            .login-title {
                font-size: 1.75rem;
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .form-control {
                border: 2px solid white;
            }

            .btn-login {
                border: 2px solid var(--primary-color);
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .form-control::placeholder {
                color: rgba(255, 255, 255, 0.4);
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <div class="logo-container">
                            <div class="logo-icon">
                            <i class="fas fa-bolt"></i>
                            </div>
                        </div>
                        <h1 class="login-title">Generator Monitor</h1>
                        <p class="login-subtitle">Secure Admin Portal</p>
                    </div>

                    <div class="login-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>Email Address
                                </label>
                                <div class="input-container">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="Enter your email address"
                                           required
                                           autofocus
                                           autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>Password
                                </label>
                                <div class="input-container">
                                    <i class="fas fa-key input-icon"></i>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Enter your password"
                                           required
                                           autocomplete="current-password">
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="remember"
                                       name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                    Keep me signed in
                                    </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <span class="btn-text">Access Dashboard</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');

            // Auto-focus on email field
            if (emailField && !emailField.value) {
                setTimeout(() => emailField.focus(), 300);
            }

            // Enhanced form validation
            form.addEventListener('submit', function(e) {
                const email = emailField.value.trim();
                const password = passwordField.value.trim();

            if (!email || !password) {
                e.preventDefault();
                    showNotification('Please fill in all required fields.', 'error');
                    return false;
                }

                if (!isValidEmail(email)) {
                    e.preventDefault();
                    showNotification('Please enter a valid email address.', 'error');
                    emailField.focus();
                return false;
                }

                // Show loading state
                loginBtn.classList.add('loading');
                loginBtn.querySelector('.btn-text').textContent = 'Signing in...';
                loginBtn.disabled = true;
            });

            // Email validation
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Enhanced notification system
            function showNotification(message, type = 'info') {
                const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
                const icon = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';

                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <i class="${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                const loginBody = document.querySelector('.login-body');
                loginBody.insertBefore(alertDiv, loginBody.firstChild);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }

            // Real-time validation
            emailField.addEventListener('blur', function() {
                if (this.value && !isValidEmail(this.value)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            passwordField.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.classList.remove('is-invalid');
                }
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    form.submit();
                }
            });

            // Auto-capitalize email field
            emailField.addEventListener('input', function() {
                this.value = this.value.toLowerCase();
            });

            // Enhanced accessibility
            emailField.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    passwordField.focus();
                }
            });

            passwordField.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });

            // Add floating animation to particles on mouse move
            document.addEventListener('mousemove', function(e) {
                const particles = document.querySelectorAll('.particle');
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;

                particles.forEach((particle, index) => {
                    const speed = (index + 1) * 0.5;
                    const xPos = (x - 0.5) * speed * 20;
                    const yPos = (y - 0.5) * speed * 20;

                    particle.style.transform = `translate(${xPos}px, ${yPos}px)`;
                });
            });
        });

        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
