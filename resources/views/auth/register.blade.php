<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="{{ asset('css/Registration.css') }}">
</head>
<body>

    <div class="reg-card animate__animated animate__fadeIn">
        <div class="text-start mb-4">
            <a href="{{ url('/login') }}" class="btn-back-login">
                <div class="icon-circle">
                    <i class="bi bi-chevron-left"></i>
                </div>
                <span>Back to Login</span>
            </a>
        </div>

        <div class="text-center mb-4">
            <h3 class="fw-bold mt-1">Create Account</h3>
            <p class="text-muted small">Start tracking your laundry and earn rewards!</p>
            
            {{-- Success Message --}}
            @if(session('success'))
                <div class='alert alert-success shadow-sm animate__animated animate__backInDown'>
                    {!! session('success') !!} <br><a href='{{ url('/login') }}' class='fw-bold text-success'>Login here</a>
                </div>
            @endif

            {{-- Error Messages --}}
            @if($errors->any())
                <div class='alert alert-danger shadow-sm animate__animated animate__shakeX text-start'>
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form action="{{ url('/register') }}" method="POST" id="regForm">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Full Name</label>
                <input type="text" name="fullname" class="form-control shadow-none" value="{{ old('fullname') }}" placeholder="Juan Dela Cruz" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-primary"><i class="bi bi-geo-alt-fill"></i> Select Your Branch</label>
                <select name="branch_id" class="form-select border-primary-subtle shadow-none" required>
                    <option value="" selected disabled>-- Choose Nearby Branch --</option>
                    @foreach($branches as $row)
                        <option value="{{ $row->id }}" {{ old('branch_id') == $row->id ? 'selected' : '' }}>{{ $row->branch_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-secondary">Phone Number</label>
                    <input type="tel" name="phone" class="form-control shadow-none" value="{{ old('phone') }}" placeholder="0917XXXXXXX" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-secondary">Email</label>
                    <input type="email" name="email" class="form-control shadow-none" value="{{ old('email') }}" placeholder="juan@email.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Create Password</label>
                <input type="password" name="password" id="password" class="form-control shadow-none" placeholder="Min. 8 characters" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control shadow-none" placeholder="Repeat password" required>
                <div id="pw-feedback" class="form-text small mt-1 fw-bold"></div>
            </div>

            <div class="mb-4 p-3 bg-light rounded-3 border border-dashed text-center">
                <label class="form-label small fw-bold text-primary"><i class="bi bi-gift me-1"></i> Have a Referral Code?</label>
                <input type="text" name="referral_from" class="form-control form-control-sm text-uppercase fw-bold text-center shadow-none" value="{{ old('referral_from') }}" placeholder="EX: WAS123">
                <div class="form-text extra-small">Enter a friend's code to get 20 bonus points!</div>
            </div>

            <div class="form-check mb-3 d-flex align-items-start gap-2">
                <input class="form-check-input mt-1 flex-shrink-0 @error('terms') is-invalid @enderror" type="checkbox" name="terms" value="1" id="terms" {{ old('terms') ? 'checked' : '' }} required>
                <label class="form-check-label small text-secondary" for="terms">
                    I agree to the
                    <a href="#" class="fw-semibold text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a>
                    and
                    <a href="#" class="fw-semibold text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">Privacy Policy</a>.
                </label>
            </div>
            @error('terms')
                <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror

            <button type="submit" id="submitBtn" class="btn btn-primary w-100 mb-3 py-2 fw-bold rounded-3 shadow-sm border-0" style="background: linear-gradient(45deg, #0ea5e9, #2563eb);">CREATE ACCOUNT</button>
        </form>

        <div class="text-center">
            <p class="small text-muted mb-0">Already have an account? <a href="{{ url('/login') }}" class="fw-bold text-decoration-none text-primary">Sign In</a></p>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="termsModalLabel">Terms of Service and Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start small text-secondary">
                    <h6 class="fw-bold text-dark">1. TERMS AND CONDITIONS</h6>
                    <h6 class="fw-semibold text-dark mt-3">General Agreement</h6>
                    <p>By registering for and using this system, you agree to comply with and be bound by the terms and policies of <strong>Laundry Care and Services</strong>. This online system is designed to manage laundry transactions, track order progress, and process records across its seven (7) branch locations.</p>

                    <h6 class="fw-semibold text-dark mt-3">User Accounts &amp; Security</h6>
                    <ul>
                        <li>Users (Customers, Staff, Branch Managers, and Administrators) are responsible for maintaining the confidentiality of their account credentials and login information.</li>
                        <li>Each user role is granted access strictly limited to their designated features.</li>
                    </ul>

                    <h6 class="fw-semibold text-dark mt-3">Order Monitoring &amp; Status Updates</h6>
                    <ul>
                        <li>Real-time order progress, such as <em>Pending</em>, <em>Washing</em>, <em>Drying</em>, <em>Ready for Pick-up</em>, or <em>Claimed</em>, reflects the physical processing of items handled by branch staff.</li>
                        <li>Estimated completion times and transaction details depend on accurate record entry at the time of service.</li>
                    </ul>

                    <h6 class="fw-semibold text-dark mt-3">Payments and Verification</h6>
                    <ul>
                        <li>The system accommodates cash payments as well as proof-of-payment uploads for e-wallets such as GCash or PayMaya.</li>
                        <li>Online payment transactions remain subject to manual verification by authorized staff before the order status is marked as “Paid.”</li>
                    </ul>

                    <h6 class="fw-bold text-dark mt-4">2. PRIVACY POLICY</h6>
                    <p><strong>Laundry Care and Services</strong> is committed to protecting your personal information in compliance with <strong>Republic Act No. 10173</strong>, also known as the <strong>Data Privacy Act of 2012</strong>.</p>

                    <h6 class="fw-semibold text-dark mt-3">Information Collected</h6>
                    <p>We collect necessary personal details when you register or place a laundry order, including:</p>
                    <ul>
                        <li>Full name</li>
                        <li>Contact information (phone number and email address)</li>
                        <li>Transaction history, laundry service details, weight records, and uploaded payment reference receipts</li>
                    </ul>

                    <h6 class="fw-semibold text-dark mt-3">Use of Information</h6>
                    <p>Your personal data is used strictly for operational purposes, such as:</p>
                    <ul>
                        <li>Managing and tracking your active laundry transactions</li>
                        <li>Verifying payments and updating order completion statuses in real time</li>
                        <li>Administering loyalty points and referral rewards</li>
                        <li>Generating consolidated service and sales reports for management</li>
                    </ul>

                    <h6 class="fw-semibold text-dark mt-3">Data Protection &amp; Access Control</h6>
                    <ul>
                        <li><strong>Role-Based Access:</strong> Your personal information is protected through Role-Based Access Control (RBAC), ensuring that only authorized personnel can access records relevant to their duties.</li>
                        <li><strong>Third-Party Disclosure:</strong> We do not sell, rent, or share your personal information with external parties without your explicit consent.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pw = document.getElementById('password');
        const cpw = document.getElementById('confirm_password');
        const feedback = document.getElementById('pw-feedback');

        cpw.addEventListener('input', () => {
            if (pw.value === cpw.value) {
                feedback.innerText = "✓ Passwords match";
                feedback.style.color = "green";
            } else {
                feedback.innerText = "✗ Passwords do not match";
                feedback.style.color = "red";
            }
        });
    </script>
</body>
</html>