<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | LaundryCare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .login-card { max-width: 400px; margin: 80px auto; padding: 40px; border-radius: 20px; background: white; }
        .brand-logo { font-size: 3.5rem; color: #0dcaf0; }
        .btn-login { background: #0dcaf0; color: white; font-weight: 600; border-radius: 12px; padding: 12px; border: none; transition: 0.3s; }
        .btn-login:hover { background: #0baccc; color: white; transform: translateY(-2px); }
        .form-control { border-radius: 10px; }
    </style>
</head>
<body>

    <div class="login-card text-center shadow-lg border-0">
        <!-- Back to Login Link -->
        <div class="text-start mb-4">
            <a href="{{ url('/login') }}" class="text-decoration-none text-muted small fw-bold">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>

        <div class="brand-logo mb-2"><i class="bi bi-key-fill"></i></div>
        <h4 class="fw-bold mb-1">Forgot Password?</h4>
        <p class="text-muted small mb-4">
            @if (!session('otp_sent'))
                Enter your registered email address to receive an OTP.
            @elseif (session('otp_sent') && !session('otp_verified'))
                Enter the 6-digit OTP sent to your email.
            @else
                Create your new password.
            @endif
        </p>

        {{-- Success Message --}}
        @if (session('status'))
            <div class="alert alert-success py-2 small border-0 mb-3">
                {{ session('status') }}
            </div>
        @endif

        {{-- Error Message Display --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 small border-0 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- STEP 1: Hihingi ng Email --}}
        @if (!session('otp_sent'))
            <form action="{{ url('/forgot-password') }}" method="POST">
                @csrf
                <div class="form-floating mb-4 text-start">
                    <input type="email" name="email" class="form-control border-light-subtle" id="floatingInput" placeholder="name@example.com" value="{{ old('email') }}" required>
                    <label for="floatingInput" class="text-muted small">Email Address</label>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3 shadow-sm">
                    SEND OTP
                </button>
            </form>

        {{-- STEP 2: OTP na may Live Countdown Timer --}}
        @elseif (session('otp_sent') && !session('otp_verified'))
            <form action="{{ url('/verify-otp-code') }}" method="POST" id="otpForm">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">

                <!-- Countdown Timer Display -->
                <div class="alert alert-warning py-2 small mb-3 text-dark fw-bold">
                    <i class="bi bi-clock-history"></i> OTP Expires in: <span id="timer" class="text-danger">05:00</span>
                </div>

                <div class="form-floating mb-4 text-start">
                    <input type="text" name="otp" class="form-control border-light-subtle" id="floatingOtp" placeholder="Enter OTP" required>
                    <label for="floatingOtp" class="text-muted small">Enter 6-digit OTP</label>
                </div>

                <button type="submit" id="verifyBtn" class="btn btn-login w-100 mb-3 shadow-sm">
                    VERIFY OTP
                </button>

                <div class="text-center mt-2">
                    <a href="{{ url('/forgot-password') }}" id="resendLink" class="text-decoration-none small text-muted" style="display: none;">
                        Didn't receive code? Click here to resend.
                    </a>
                </div>
            </form>

            <script>
                // Itakda ang oras sa 5 minuto (300 segundo)
                let timeRemaining = 300;
                const timerDisplay = document.getElementById('timer');
                const verifyBtn = document.getElementById('verifyBtn');
                const resendLink = document.getElementById('resendLink');
                const otpInput = document.getElementById('floatingOtp');

                const countdown = setInterval(function () {
                    let minutes = Math.floor(timeRemaining / 60);
                    let seconds = timeRemaining % 60;

                    // I-format para laging may kasamang zero (hal. 04:09)
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    seconds = seconds < 10 ? '0' + seconds : seconds;

                    timerDisplay.textContent = minutes + ':' + seconds;

                    if (timeRemaining <= 0) {
                        clearInterval(countdown);
                        timerDisplay.textContent = "Expired";
                        timerDisplay.classList.replace('text-danger', 'text-secondary');
                        verifyBtn.disabled = true; // I-disable ang verify button pag nag-expire na
                        verifyBtn.classList.add('opacity-50');
                        otpInput.disabled = true;  // I-disable din ang input field
                        resendLink.style.display = 'block'; // Ipakita ang link para makapag-request ulit
                    } else {
                        timeRemaining--;
                    }
                }, 1000);
            </script>

        {{-- STEP 3: New Password & Confirm Password (May Validation) --}}
        @else
            <form action="{{ url('/reset-password-store') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <input type="hidden" name="otp" value="{{ session('verified_otp') }}">

                <div class="form-floating mb-3 text-start">
                    <input type="password" name="password" class="form-control border-light-subtle" id="floatingPassword" placeholder="New Password" required minlength="6">
                    <label for="floatingPassword" class="text-muted small">New Password (Min. 6 chars)</label>
                </div>

                <div class="form-floating mb-2 text-start">
                    <input type="password" name="password_confirmation" class="form-control border-light-subtle" id="floatingPasswordConfirm" placeholder="Confirm Password" required>
                    <label for="floatingPasswordConfirm" class="text-muted small">Confirm New Password</label>
                </div>
                
                <div id="passwordAlert" class="text-danger small text-start mb-3" style="display: none;">
                    <i class="bi bi-exclamation-circle"></i> Passwords do not match!
                </div>

                <button type="submit" id="submitBtn" class="btn btn-login w-100 mb-3 shadow-sm">
                    RESET PASSWORD
                </button>
            </form>

            <script>
                const password = document.getElementById('floatingPassword');
                const confirmPassword = document.getElementById('floatingPasswordConfirm');
                const passwordAlert = document.getElementById('passwordAlert');
                const submitBtn = document.getElementById('submitBtn');

                function validatePasswords() {
                    if (confirmPassword.value.length > 0) {
                        if (password.value !== confirmPassword.value) {
                            confirmPassword.style.borderColor = '#dc3545';
                            confirmPassword.style.boxShadow = '0 0 0 0.25rem rgba(220, 53, 69, 0.25)';
                            passwordAlert.style.display = 'block';
                            submitBtn.disabled = true;
                        } else {
                            confirmPassword.style.borderColor = '#198754';
                            confirmPassword.style.boxShadow = '0 0 0 0.25rem rgba(25, 135, 84, 0.25)';
                            passwordAlert.style.display = 'none';
                            submitBtn.disabled = false;
                        }
                    } else {
                        confirmPassword.style.borderColor = '';
                        confirmPassword.style.boxShadow = '';
                        passwordAlert.style.display = 'none';
                        submitBtn.disabled = false;
                    }
                }

                password.addEventListener('input', validatePasswords);
                confirmPassword.addEventListener('input', validatePasswords);
            </script>
        @endif
    </div>

</body>
</html>