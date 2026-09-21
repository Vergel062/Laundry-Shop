<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LaundryCare System</title>
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
        .forgot-link { font-size: 0.85rem; color: #6c757d; text-decoration: none; transition: 0.2s; }
        .forgot-link:hover { color: #0dcaf0; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card text-center shadow-lg border-0">
        <div class="text-start mb-4">
            <a href="{{ url('/') }}" class="text-decoration-none text-muted small fw-bold">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>

        <div class="brand-logo mb-2"><i class="bi bi-droplet-half"></i></div>
        <h4 class="fw-bold mb-1">LaundryCare</h4>
        <p class="text-muted small mb-4">Welcome back! Please sign in to continue.</p>

        {{-- Error Message Display --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 small border-0">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="form-floating mb-3 text-start">
                <input type="email" name="email" class="form-control border-light-subtle" id="floatingInput" placeholder="name@example.com" value="{{ old('email') }}" required>
                <label for="floatingInput" class="text-muted small">Email Address</label>
            </div>

            <div class="form-floating mb-3 text-start">
                <input type="password" name="password" class="form-control border-light-subtle" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword" class="text-muted small">Password</label>
            </div>

            <!-- Forgot Password Link -->
            <div class="text-end mb-3">
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-3 shadow-sm">
                SIGN IN
            </button>
        </form>

        <div class="mt-4 pt-3 border-top border-light">
            <p class="text-muted small mb-2 text-center">New to LaundryCare?</p>
            <a href="{{ url('/register') }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold btn-sm py-2">
                <i class="bi bi-person-plus me-1"></i> CREATE NEW ACCOUNT
            </a>
        </div>
    </div>

</body>
</html>