<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaundryCare | Modern Fabric & Garment Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Landing.css') }}">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-3" href="#">
                <i class="bi bi-droplet-fill"></i> LaundryCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link px-3" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#about-us">About Us</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#contact-us">Contact Us</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn btn-outline-primary rounded-pill px-4" href="{{ url('/login') }}">Login</a>
                    </li>
                    <li class="nav-item ms-2 mt-2 mt-lg-0">
                        <a class="btn btn-primary rounded-pill px-4" href="{{ url('/register') }}">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill mb-3 fw-bold">🚀 Real-time Tracking Available</span>
                    <h1 class="display-4 fw-bold mb-4">Your Laundry, <span class="text-primary">Tracked & Care Guaranteed.</span></h1>
                    <p class="lead text-muted mb-5">Experience the future of laundry services. Monitor your clothes from wash to fold in real time, manage orders seamlessly, and enjoy premium garment care.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ url('/register') }}" class="btn btn-primary-custom btn-lg shadow">Register Now</a>
                        <a href="#about-us" class="btn btn-light btn-lg rounded-pill border px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                    <img src="https://img.freepik.com/free-vector/cleaning-service-abstract-concept-vector-illustration-commercial-cleaning-services-office-house-disinfection-business-industrial-sanitization-professional-equipment-abstract-metaphor_335657-2933.jpg" alt="Laundry Illustration" class="img-fluid rounded-4 hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h6 class="text-primary text-uppercase fw-bold tracking-wider">Core Features</h6>
                <h2 class="fw-bold">Why Choose LaundryCare?</h2>
                <p class="text-muted">We combine quality cleaning with smart digital management.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="icon-box"><i class="bi bi-clock-history"></i></div>
                        <h5 class="fw-bold">Real-time Order Tracking</h5>
                        <p class="text-muted small">Monitor the exact status of your laundry—from washing and drying to ready for pick-up—anytime, anywhere.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="icon-box"><i class="bi bi-receipt"></i></div>
                        <h5 class="fw-bold">Transparent Records</h5>
                        <p class="text-muted small">Keep accurate digital records of all your transactions, payments, and pick-up details in one account.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="icon-box"><i class="bi bi-geo-alt"></i></div>
                        <h5 class="fw-bold">Multi-Branch Network</h5>
                        <p class="text-muted small">Access our services across 7 branches in Carmona, Biñan, and Cabuyao with centralized service history.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $aboutImage = $content['about_image'] ?? 'image/laundry.jpg';
        $aboutImageUrl = str_starts_with($aboutImage, 'http://') || str_starts_with($aboutImage, 'https://')
            ? $aboutImage
            : (str_starts_with($aboutImage, 'image/') ? asset($aboutImage) : asset('storage/' . $aboutImage));
    @endphp

    <!-- Enhanced About Us Section -->
    <section id="about-us" class="about-section py-5 position-relative">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="pe-lg-4">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-3">
                            <i class="bi bi-award-fill me-1"></i> Trusted Local Service
                        </span>
                        <h2 class="display-6 fw-extrabold mb-4">{{ $content['about_title'] }}</h2>
                        <p class="text-secondary leading-relaxed mb-4">
                            {{ $content['about_content'] }}
                        </p>
                        <p class="text-secondary leading-relaxed mb-4">
                            {{ $content['about_secondary'] }}
                        </p>

                        <!-- Stat Boxes -->
                        <div class="row g-3 pt-2">
                            <div class="col-4">
                                <div class="stat-card p-3 rounded-4 border text-center">
                                    <h3 class="fw-bold text-primary mb-1">7</h3>
                                    <span class="text-muted small fw-medium">Branches</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card p-3 rounded-4 border text-center">
                                    <h3 class="fw-bold text-primary mb-1">200+</h3>
                                    <span class="text-muted small fw-medium">Monthly Orders</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card p-3 rounded-4 border text-center">
                                    <h3 class="fw-bold text-primary mb-1">100%</h3>
                                    <span class="text-muted small fw-medium">Digital Tracking</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="about-image-card position-relative">
                       <div class="image-wrapper rounded-5 overflow-hidden shadow-lg border border-white border-4">
                            <img src="{{ $aboutImageUrl }}" alt="Laundry Care Operations" class="img-fluid object-fit-cover w-100" onerror="this.onerror=null; this.src='{{ asset('image/laundry.jpg') }}';">
</div>
                        <div class="floating-badge bg-white p-3 rounded-4 shadow-lg border d-flex align-items-center gap-3">
                            <div class="badge-icon bg-success-subtle text-success rounded-circle p-3 fs-4">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Quality Service</h6>
                                <small class="text-muted">Verified & Managed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Contact Us Section -->
    <section id="contact-us" class="contact-section py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">Get In Touch</span>
                <h2 class="display-6 fw-bold">We’d Love to Hear From You</h2>
                <p class="text-muted max-w-600 mx-auto">{{ $content['contact_description'] }}</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- Info Cards Block -->
                <div class="col-lg-5">
                    <div class="contact-info-card p-4 p-md-5 rounded-5 h-100 shadow-sm text-white d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold mb-4 text-white">Contact Information</h3>
                            <p class="text-white-50 mb-4">Reach out directly or visit our main branch for bulk washing inquiries.</p>

                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon-glass me-3"><i class="bi bi-geo-alt-fill"></i></div>
                                <div>
                                    <small class="text-white-50 d-block">Main Branch Location</small>
                                    <span class="fw-semibold">{{ $content['contact_address'] }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon-glass me-3"><i class="bi bi-envelope-fill"></i></div>
                                <div>
                                    <small class="text-white-50 d-block">Email Support</small>
                                    <span class="fw-semibold">{{ $content['contact_email'] }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon-glass me-3"><i class="bi bi-telephone-fill"></i></div>
                                <div>
                                    <small class="text-white-50 d-block">Call Us Direct</small>
                                    <span class="fw-semibold">{{ $content['contact_phone'] }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="contact-icon-glass me-3"><i class="bi bi-clock-fill"></i></div>
                                <div>
                                    <small class="text-white-50 d-block">Operating Hours</small>
                                    <span class="fw-semibold">{{ $content['contact_hours'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-top border-white-10">
                            <span class="small text-white-50">Branches operating across Carmona, Biñan, and Cabuyao.</span>
                        </div>
                    </div>
                </div>

                <!-- Modern Form Block -->
                <div class="col-lg-7">
                    <div class="contact-form-card p-4 p-md-5 rounded-5 h-100 shadow-sm border bg-white">
                        <h4 class="fw-bold mb-4 text-dark">Send Us a Direct Message</h4>
                        @if(session('contact_success'))
                            <div class="alert alert-success">{{ session('contact_success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">Please check the form fields and try again.</div>
                        @endif
                        <form action="{{ route('contact.message') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating mb-1">
                                        <input type="text" name="name" class="form-control modern-input" id="nameInput" placeholder="John Doe" value="{{ old('name') }}" required>
                                        <label for="nameInput">Your Full Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-1">
                                        <input type="email" name="email" class="form-control modern-input" id="emailInput" placeholder="name@example.com" value="{{ old('email') }}" required>
                                        <label for="emailInput">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-1">
                                        <input type="text" name="subject" class="form-control modern-input" id="subjectInput" placeholder="Subject" value="{{ old('subject') }}" required>
                                        <label for="subjectInput">Subject</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-1">
                                        <textarea name="message" class="form-control modern-input" id="messageInput" placeholder="Message" style="height: 140px" required>{{ old('message') }}</textarea>
                                        <label for="messageInput">Your Message...</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <button class="btn btn-primary-custom w-100 py-3 shadow-lg" type="submit">
                                        Send Message <i class="bi bi-send ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern Dark Footer -->
    <footer class="footer-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary border-opacity-25">
                <div class="col-lg-5 col-md-6">
                    <a class="navbar-brand fw-bold text-white fs-3 d-inline-block mb-3" href="#">
                        <i class="bi bi-droplet-fill text-primary"></i> LaundryCare
                    </a>
                    <p class="text-white-50 small pe-lg-4">
                        Modernizing laundry transactions and inventory monitoring across Cavite and Laguna. Fast, transparent, and reliable garment management.
                    </p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-white mb-3">Quick Navigation</h6>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="#features" class="text-white-50 text-decoration-none">Core Features</a></li>
                        <li class="mb-2"><a href="#about-us" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#contact-us" class="text-white-50 text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="{{ url('/login') }}" class="text-white-50 text-decoration-none">Customer Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h6 class="fw-bold text-white mb-3">Coverage Areas</h6>
                    <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt text-primary me-2"></i> Carmona, Cavite (Main Shop)</p>
                    <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt text-primary me-2"></i> Biñan & Cabuyao, Laguna</p>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-4 small text-white-50">
                <p class="mb-0">&copy; 2026 Laundry Care and Services. All Rights Reserved.</p>
                <p class="mb-0 mt-2 mt-md-0">Online Transaction & Inventory Monitoring Platform</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>