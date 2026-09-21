<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | LaundryCare</title>
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
                <a href="{{ route('manager.dashboard') }}" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a>
            </li>
            <!-- IDINAGDAG: Service List Link -->
            <li class="nav-item"><a href="{{ route('manager.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
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

    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0 d-none d-sm-block">Manager Overview</h5>
                <span class="badge bg-info bg-opacity-10 text-info ms-sm-3 px-3 py-1.5 rounded-pill fw-semibold border border-info border-opacity-25">
                    <i class="bi bi-building me-1"></i> {{ $branch_name }}
                </span>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="small mb-0 fw-bold">{{ Auth::user()->fullname ?? 'Manager' }}</p>
                    <p class="small text-muted mb-0">Branch Manager</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->fullname ?? 'Manager') }}&background=0ea5e9&color=fff" class="rounded-circle shadow-sm" width="40">
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card stat-card bg-white p-3 shadow-sm h-100 border-start border-success border-5">
                        <small class="text-muted fw-bold text-uppercase">TOTAL SALES</small>
                        <h3 class="fw-bold text-success mt-1 mb-0">₱{{ number_format((float)$total_income, 2) }}</h3>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-warning border-5 h-100">
                        <small class="text-muted fw-bold text-uppercase">PENDING WASH</small>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ $counts_res['pending_total'] ?? 0 }}</h3>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-info border-5 h-100">
                        <small class="text-muted fw-bold text-uppercase">IN THE DRYERS</small>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ $counts_res['drying_total'] ?? 0 }}</h3>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-primary border-5 h-100">
                        <small class="text-muted fw-bold text-uppercase">READY TO CLAIM</small>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ $counts_res['ready_total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-3">{{ session('success') }}</div>
            @endif

            <div class="row g-4 mb-4">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-15 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-wallet2 text-info me-2"></i>Quick Expense</h6>
                            <form action="{{ route('manager.expenses.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <select name="expense_type" class="form-select bg-light border-0" required>
                                        <option value="" selected disabled>Select Bill Type</option>
                                        <option value="Electricity">Electricity</option>
                                        <option value="Water">Water</option>
                                        <option value="Supplies">Supplies</option>
                                        <option value="Rent">Shop Rent</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <input type="number" name="amount" step="0.01" class="form-control bg-light border-0" placeholder="Amount (₱)" required>
                                </div>
                                <div class="mb-3">
                                    <textarea name="remarks" class="form-control bg-light border-0" placeholder="Optional remarks..." rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-info w-100 text-white fw-bold shadow-sm">SUBMIT EXPENSE</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card shadow-sm border-0 rounded-15 h-100">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="bi bi-droplet text-primary me-2"></i>Recent Utility Logs</h6>
                            <a href="{{ route('manager.utility.tracking') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($util_logs as $log)
                                    <tr>
                                        <td class="ps-4 small text-muted">{{ date('M d, Y', strtotime($log->date_logged)) }}</td>
                                        <td>{{ $log->expense_type }}</td>
                                        <td class="fw-bold text-danger">₱{{ number_format($log->amount, 2) }}</td>
                                        <td class="small text-muted">{{ $log->remarks ?: '---' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">No utility logs found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="transactions" class="chart-container mb-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-activity text-info me-2"></i>Live Branch Transaction Logs</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted text-uppercase">
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Weight</th>
                                <th>Amount</th>
                                <th>Order Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tx_query as $tx)
                            <tr>
                                <td class="fw-semibold text-primary small">{{ $tx->ref_number ?? 'N/A' }}</td>
                                <td class="fw-bold small">{{ $tx->customer_name ?? 'Walk-in' }}</td>
                                <td>{{ $tx->weight_kg }} kg</td>
                                <td class="fw-bold">₱{{ number_format((float)$tx->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-opacity-10 px-2 {{ $tx->order_status === 'Pending' ? 'bg-warning text-warning' : ($tx->order_status === 'Drying' ? 'bg-info text-info' : 'bg-primary text-primary') }}">
                                        {{ strtoupper($tx->order_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge px-2 rounded-pill {{ $tx->payment_status === 'Paid' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                        {{ $tx->payment_status }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ date('M d, Y', strtotime($tx->created_at)) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div> 
    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('sidebarToggle')?.addEventListener('click', () => { 
        document.getElementById('sidebar').classList.add('active'); 
        document.getElementById('overlay').classList.add('active'); 
    });
    document.getElementById('closeSidebar')?.addEventListener('click', () => { 
        document.getElementById('sidebar').classList.remove('active'); 
        document.getElementById('overlay').classList.remove('active'); 
    });
});

document.addEventListener('click', function(e) {
    const logoutBtn = e.target.closest('.logout-manager-btn');
    if (logoutBtn) {
        e.preventDefault();
        const logoutForm = logoutBtn.closest('form');

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
            if (result.isConfirmed && logoutForm) {
                logoutForm.submit();
            }
        });
    }
});
</script>
</body>
</html>