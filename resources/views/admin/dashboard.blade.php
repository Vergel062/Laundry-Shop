<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | LaundryCare Analytics</title>
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
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item">
                            <a href="{{ route('admin.financial.reports') }}" class="nav-link" style="white-space: nowrap;">
                                <i class="bi bi-graph-up-arrow"></i> Financial Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.utility.tracking') }}" class="nav-link" style="white-space: nowrap;">
                                <i class="bi bi-droplet"></i> Utility Report
                            </a>
                        </li>
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
                        <li class="nav-item">
                            <a href="{{ route('admin.branch.management') }}" class="nav-link">
                                <i class="bi bi-shop"></i> Branch Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.manage.users') }}" class="nav-link">
                                <i class="bi bi-person-plus"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.modified.content') }}" class="nav-link">
                                <i class="bi bi-pencil-square"></i> Modified Content
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
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
                <h5 class="fw-bold mb-0 d-none d-sm-block">Executive Overview</h5>
                <select class="form-select form-select-sm ms-sm-3" style="width: 160px;" onchange="filterBranch(this.value)">
                    <option value="all" {{ ($selected_branch == 'all') ? 'selected' : '' }}>All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ ($selected_branch == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="small mb-0 fw-bold">Admin User</p>
                    <p class="small text-muted mb-0">System Owner</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=0ea5e9&color=fff" class="rounded-circle shadow-sm" width="40">
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-4">
                    <div class="card stat-card bg-white p-3 shadow-sm h-100">
                        <small class="text-muted fw-bold">TOTAL SALES</small>
                        <h3 class="fw-bold text-primary mt-1 mb-0">₱{{ number_format($total_sales, 0) }}</h3>
                        <small class="text-success small mt-1"><i class="bi bi-arrow-up"></i>Real-time</small>
                    </div>
                </div>
                <div class="col-6 col-xl-4">
                    <div class="card stat-card bg-white p-3 shadow-sm h-100">
                        <small class="text-muted fw-bold">EXPENSES</small>
                        <h3 class="fw-bold text-danger mt-1 mb-0">₱{{ number_format($total_expenses, 0) }}</h3>
                        <small class="text-muted small">Total branch expenses</small>
                    </div>
                </div>
                <div class="col-6 col-xl-4">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-info border-5 h-100">
                        <small class="text-muted fw-bold">TOTAL KG</small>
                        <h3 class="fw-bold mt-1 mb-0">{{ number_format($total_kg, 1) }}</h3>
                        <small class="text-muted small">Laundry weight</small>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="fw-bold">Weekly Sales</h6>
                            <input type="month" class="form-control form-control-sm w-auto border-0 bg-light" value="{{ date('Y-m') }}">
                        </div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container h-100">
                        <h6 class="fw-bold mb-3">Top Branches</h6>
                        @foreach($ranking as $rank)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                            <div>
                                <h6 class="mb-0 small fw-bold">{{ $rank->branch_name }}</h6>
                                <small class="text-muted">{{ number_format($rank->orders ?? 0) }} orders</small>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                ₱{{ number_format(($rank->sales ?? 0) / 1000, 1) }}k
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="chart-container mb-4">
                <h6 class="fw-bold mb-4">Recent Customer Payments</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th>DATE</th>
                                <th>CUSTOMER</th>
                                <th>BRANCH</th>
                                <th>AMOUNT</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history_q as $h)
                            <tr>
                                <td class="small">{{ date('M d, Y', strtotime($h->created_at)) }}</td>
                                <td class="fw-bold small">{{ $h->fullname }}</td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ $h->branch_name }}</span></td>
                                <td class="fw-bold">₱{{ number_format($h->total_amount, 2) }}</td>
                                <td><span class="badge rounded-pill {{ ($h->payment_status=='Paid') ? 'bg-success text-success' : 'bg-warning text-warning' }} bg-opacity-10">{{ $h->payment_status }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="chart-container">
                <h6 class="fw-bold mb-4">Recent Utility Logs</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th>DATE</th>
                                <th>BRANCH</th>
                                <th>TYPE</th>
                                <th>AMOUNT</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs_q as $log)
                            <tr>
                                <td class="small">{{ date('M d, Y', strtotime($log->date_logged)) }}</td>
                                <td><span class="badge bg-secondary">{{ $log->branch_name }}</span></td>
                                <td class="fw-bold small">{{ $log->expense_type }}</td>
                                <td class="text-danger fw-bold">₱{{ number_format($log->amount, 2) }}</td>
                                <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success">Verified</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No recent logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>
<script>
function filterBranch(val) { window.location.href = '{{ route("admin.dashboard") }}?branch_id=' + val; }

document.addEventListener('DOMContentLoaded', function() {
    const dynamicData = {!! $json_revenue !!};
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Revenue (₱)',
                data: dynamicData,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#0ea5e9',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
</body>
</html>