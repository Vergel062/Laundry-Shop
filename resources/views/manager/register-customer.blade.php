<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <li class="nav-item"><a href="{{ route('manager.register.customer') }}" class="nav-link active"><i class="bi bi-shop"></i> Register Accounts</a></li>
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

    <div class="main-wrapper">
        <!-- Inilagay ang panibagong Navbar Header -->
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
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

        <div class="container-fluid p-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="chart-container shadow-sm p-4">
                        <h6 class="fw-bold mb-4"><i class="bi bi-person-plus text-info me-2"></i>New Registration</h6>
                        <form action="{{ route('manager.register.customer.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">ACCOUNT TYPE</label>
                                <select name="role" class="form-select bg-light border-0 py-2" required>
                                    <option value="customer" selected>Customer Account</option>
                                    <option value="staff">Staff Account</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">FULL NAME</label>
                                <input type="text" name="fullname" class="form-control bg-light border-0 py-2" placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                                <input type="text" name="phone" class="form-control bg-light border-0 py-2" placeholder="09123456789" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                                <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="customer@email.com">
                            </div>
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3 mb-3">
                                <small class="text-primary fw-bold"><i class="bi bi-info-circle me-1"></i> Default Pass: user12345</small>
                            </div>
                            <button type="submit" name="register_customer" class="btn btn-primary w-100 fw-bold py-2">Register Account</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="chart-container shadow-sm p-0 overflow-hidden">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <h6 class="fw-bold mb-0"><i class="bi bi-people text-info me-2"></i>Active Users</h6>
                            <form action="{{ route('manager.register.customer') }}" method="GET" class="d-flex align-items-center gap-2">
                                <input type="search" name="user_search" class="form-control form-control-sm" placeholder="Search users..." value="{{ $userSearch }}" aria-label="Search active users">
                                <select name="user_type" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter active users">
                                    <option value="all" {{ $selectedRole === 'all' ? 'selected' : '' }}>All Users</option>
                                    <option value="staff" {{ $selectedRole === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="customer" {{ $selectedRole === 'customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th class="ps-4">Name</th>
                                        <th>Phone / Email</th>
                                        <th class="text-center">Account Type</th>
                                        <th class="text-center">Ref Code</th>
                                        <th class="text-center">Total Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users_result as $row)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $row->fullname }}</td>
                                        <td class="small text-muted">{{ $row->phone }}<br>{{ $row->email ?: 'No email' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $row->role === 'staff' ? 'bg-warning text-dark' : 'bg-info bg-opacity-10 text-info' }}">
                                                {{ ucfirst($row->role) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($row->role === 'customer')
                                                <span class="badge bg-light text-dark border">{{ $row->referral_code }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($row->role === 'customer')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                                    <i class="bi bi-star-fill me-1"></i> {{ number_format($row->total_points) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No active users found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => { document.getElementById('sidebar').classList.toggle('active'); document.getElementById('overlay').classList.toggle('active'); });
    document.getElementById('closeSidebar')?.addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('overlay').classList.remove('active'); });

    @if(session('status_msg') === 'success')
        Swal.fire({ icon: 'success', title: 'Registered!', text: 'Account created successfully.' });
    @elseif(session('status_msg') === 'error_exists')
        Swal.fire({ icon: 'warning', title: 'Duplicate!', text: 'Phone or Email already registered.' });
    @endif

    document.querySelector('.logout-manager-btn')?.addEventListener('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Confirm Logout',
            text: "Are you sure you want to end your manager session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
            }
        });
    });
</script>
</body>
</html>