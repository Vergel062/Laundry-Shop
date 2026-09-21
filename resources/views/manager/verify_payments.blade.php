<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Payments | LaundryCare</title>
    
    <!-- External Fonts & Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- External Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="app-container">
   <nav id="sidebar">
        <div class="sidebar-brand p-4 text-center d-flex align-items-center justify-content-between">
            <h4 class="fw-bold text-info mb-0"><i class="bi bi-droplet-half"></i> Laundry<span class="text-white">Care</span></h4>
            <button class="btn text-white d-lg-none p-0 border-0" id="closeSidebar"><i class="bi bi-x-lg fs-4"></i></button>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a href="{{ route('manager.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a>
            </li>
            <!-- IDINAGDAG: Service List Link -->
            <li class="nav-item">
                <a href="{{ route('manager.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('manager.verify.payments') }}" class="nav-link {{ request()->routeIs('manager.verify.payments*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check"></i> Verify Payments
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('manager.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a>
            </li>
            <li class="nav-item"><a href="{{ route('manager.expenses') }}" class="nav-link"><i class="bi bi-wallet2"></i> Expenses</a></li>
            
            <hr class="mx-3 opacity-25">
            <li class="nav-item mt-2">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>

                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('manager.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('manager.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Report</a></li>
                        <li class="nav-item"><a href="{{ route('manager.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Report</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-gear"></i> Settings</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('manager.register.customer') }}" class="nav-link"><i class="bi bi-shop"></i> Register Accounts</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mt-2">
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-manager-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar Header -->
        <nav class="navbar navbar-light px-4 py-3 sticky-top bg-white border-bottom">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <span class="navbar-text fw-semibold">Branch: 
                    <span class="text-primary text-uppercase">
                        {{ $branch_name ?? $branchName ?? 'Unknown Branch' }}
                    </span>
                </span>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="small mb-0 fw-bold">{{ Auth::user()->fullname ?? 'Manager' }}</p>
                    <p class="small text-muted mb-0">Branch Manager</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->fullname ?? 'Manager') }}&background=0ea5e9&color=fff" class="rounded-circle shadow-sm" width="40" alt="Manager Profile">
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="container-fluid p-4">
            
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-1">Payment Verification</h3>
                    <p class="text-muted small">Verify online payments and view transaction history.</p>
                </div>
            </div>

            <!-- Date Picker & Search Filter Section -->
            <div class="filter-section p-3 mb-4 shadow-sm">
                <form action="{{ route('manager.verify.payments') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="date-label">History Start</label>
                        <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ $start_date ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="date-label">History End</label>
                        <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ $end_date ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Filter</button>
                    </div>
                    <div class="col-md-4">
                        <label class="date-label">Search</label>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ref # or Name..." value="{{ $search ?? '' }}">
                    </div>
                </form>
            </div>

            <!-- Payment Table Card -->
            <div class="payment-card shadow-sm">
                <div class="table-responsive table-scroll-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th class="ps-4 py-3">CUSTOMER & REF</th>
                                <th>METHOD</th>
                                <th>PAYMENT REF</th>
                                <th class="text-center">AMOUNT</th>
                                <th class="text-center">PROOF</th>
                                <th class="text-center">STATUS / ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $row)
                                @php $is_paid = ($row->payment_status === 'Paid'); @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $row->fullname }}</div>
                                        <small class="text-muted">#{{ $row->ref_number }}</small>
                                    </td>
                                    <td><span class="method-tag">{{ strtoupper($row->payment_method ?? 'N/A') }}</span></td>
                                    <td><code class="text-primary fw-bold">{{ $row->payment_reference ?: 'N/A' }}</code></td>
                                    <td class="text-center fw-bold">₱{{ number_format($row->total_amount ?? $row->amount ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        @if(!empty($row->proof_of_payment))
                                            <img src="{{ asset($row->proof_of_payment) }}" class="proof-thumb" onclick="viewProof('{{ asset($row->proof_of_payment) }}')">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($is_paid)
                                            <span class="status-badge bg-success-subtle text-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> Verified
                                            </span>
                                            <div style="font-size: 10px;" class="text-muted mt-1">
                                                {{ $row->date_paid ? date('M d, h:i A', strtotime($row->date_paid)) : '' }}
                                            </div>
                                        @else
                                            <form action="{{ route('manager.verify.payments.approve') }}" method="POST" class="d-inline" onsubmit="return confirmApproval(event, this);">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="approve_id" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill shadow-sm">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No online payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div> 
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert Scripts -->
<script>
    // Visualizing Proof of Payment
    function viewProof(path) {
        Swal.fire({
            title: 'Proof of Payment',
            imageUrl: path,
            imageAlt: 'Proof of Payment',
            imageWidth: '100%',
            confirmButtonColor: '#1e293b'
        });
    }

    // Confirmation Alert before Submitting Approval
    function confirmApproval(e, form) {
        e.preventDefault();
        Swal.fire({
            title: 'Confirm Payment?',
            text: "Have you checked the reference number and amount?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    // Manager Logout Alert
    document.querySelector('.logout-manager-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const logoutForm = document.getElementById('logoutForm');
        Swal.fire({
            title: 'Confirm Logout',
            text: "Are you sure you want to end your manager session?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                logoutForm.submit();
            }
        });
    });

    // Automatically Trigger Success Alert if redirected with status=verified
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'verified') {
        Swal.fire({
            icon: 'success',
            title: 'Payment Verified!',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, '', window.location.pathname);
    }
</script>

<!-- External Custom JS -->
<script src="{{ asset('js/Admin_script.js') }}"></script>
</body>
</html>