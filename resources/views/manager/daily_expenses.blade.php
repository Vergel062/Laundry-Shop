<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expenses | LaundryCare Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <li class="nav-item"><a href="{{ route('manager.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item">
                <a href="{{ route('manager.verify.payments') }}" class="nav-link {{ request()->routeIs('manager.verify.payments*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check"></i> Verify Payments
                </a>
            </li>
            <li class="nav-item"><a href="{{ route('manager.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <li class="nav-item"><a href="{{ route('manager.expenses') }}" class="nav-link active"><i class="bi bi-wallet2"></i> Expenses</a></li>
            
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

    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <span class="navbar-text fw-semibold">Branch:
                    <span class="text-primary text-uppercase">{{ $branch_name }}</span>
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
            <div class="chart-container shadow-sm p-0 overflow-hidden" style="background: white; border-radius: 15px;">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-wallet2 text-info me-2"></i>Expenses</h6>
                        <small class="text-muted">Track and review expenses for this branch.</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Total Expenses</small>
                        <strong class="text-danger fs-5">₱{{ number_format($range_total, 2) }}</strong>
                    </div>
                </div>
                <div class="p-4 border-bottom bg-light bg-opacity-50">
                    <form action="{{ route('manager.expenses') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Start Date</label>
                            <input type="date" name="start_date" class="form-control" lang="en-US" placeholder="mm/dd/yyyy" value="{{ $start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">End Date</label>
                            <input type="date" name="end_date" class="form-control" lang="en-US" placeholder="mm/dd/yyyy" value="{{ $end_date }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Water, Rent..." value="{{ $search }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold flex-grow-1">Apply</button>
                            <a href="{{ route('manager.expenses') }}" class="btn btn-light border" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
                        </div>
                    </form>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-list-stars text-primary me-2"></i>Expense Logs</h6>
                        <small class="text-muted">
                            @if($report_generated)
                                Showing records from {{ date('M d, Y', strtotime($start_date)) }} to {{ date('M d, Y', strtotime($end_date)) }}
                            @else
                                Choose a date range and apply the filter to view records.
                            @endif
                        </small>
                    </div>
                    <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr class="small text-muted">
                                        <th class="ps-4">CATEGORY</th>
                                        <th>REMARKS</th>
                                        <th class="text-center">DATE</th>
                                        <th class="text-center">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $row)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $row->expense_type }}</td>
                                            <td class="text-muted small">{{ $row->remarks ?: '--' }}</td>
                                            <td class="text-center text-muted small">{{ date('M d, Y', strtotime($row->date)) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger rounded-pill">₱{{ number_format($row->amount, 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted small">
                                                {{ $report_generated ? 'No records found.' : 'No records found. Please choose a date and apply.' }}
                                            </td>
                                        </tr>
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
        @if(session('status') === 'success')
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Daily expense saved successfully.',
                confirmButtonColor: '#0d6efd',
                timer: 2500,
                showConfirmButton: false
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sidebarToggle')?.addEventListener('click', function() {
                document.getElementById('sidebar').classList.add('active');
                document.getElementById('overlay').classList.add('active');
            });

            document.getElementById('closeSidebar')?.addEventListener('click', function() {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
            });

            document.getElementById('overlay')?.addEventListener('click', function() {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
            });
        });

        document.addEventListener('click', function(e) {
            const logoutBtn = e.target.closest('.logout-manager-btn');
            if (!logoutBtn) {
                return;
            }

            e.preventDefault();
            const logoutForm = document.getElementById('logoutForm');
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