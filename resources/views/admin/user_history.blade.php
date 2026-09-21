<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User History | LaundryCare</title>
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
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i>Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Reports</a></li>
                        <li class="nav-item"><a href="{{ route('admin.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Tracking</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="true">
                    <span><i class="bi bi-gear"></i> Settings</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link active"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
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
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">User Transaction History</h5>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">{{ $user->fullname }}</h4>
                    <p class="text-muted mb-0">
                        {{ $user->email }} · {{ ucfirst($user->role) }}
                        @if($user->branch_name) · {{ $user->branch_name }} @endif
                    </p>
                </div>
                <a href="{{ route('admin.manage.users') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Manage Users
                </a>
            </div>

            <div class="card shadow-sm border-0 rounded-15 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('admin.manage.users.history', $user->id) }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control bg-light border-0 rounded-8 py-2" lang="en-US" title="mm/dd/yyyy" placeholder="mm/dd/yyyy" value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control bg-light border-0 rounded-8 py-2" lang="en-US" title="mm/dd/yyyy" placeholder="mm/dd/yyyy" value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="filter" value="1" class="btn btn-primary w-100 fw-bold rounded-8 py-2">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.manage.users.history', $user->id) }}" class="btn btn-light border w-100 rounded-8 py-2">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 card-stats border-primary rounded-15 bg-white">
                        <small class="text-muted fw-bold">TOTAL RECORDS</small>
                        <h4 class="fw-bold mb-0">{{ $history->count() }}</h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 card-stats border-success rounded-15 bg-white">
                        <small class="text-muted fw-bold text-success">TOTAL AMOUNT</small>
                        <h4 class="fw-bold mb-0">₱{{ number_format($historyTotal, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-15 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Transaction History</h6>
                </div>
                <div class="table-responsive" style="max-height: 620px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="ps-4">Reference / Date</th>
                                <th>Service</th>
                                <th>Weight</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$hasDateFilter)
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No records found. Please choose a date range and filter.</td>
                                </tr>
                            @else
                            @forelse($history as $row)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary">{{ $row->ref_number ?: '—' }}</div>
                                        <small class="text-muted">{{ date('M d, Y h:i A', strtotime($row->created_at)) }}</small>
                                    </td>
                                    <td>{{ $row->service_type ?: '—' }}</td>
                                    <td>{{ number_format((float) $row->weight_kg, 2) }} kg</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $row->order_status }}</span></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $row->payment_status ?: '—' }}</span></td>
                                    <td class="text-end pe-4 fw-bold">₱{{ number_format((float) $row->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No transaction records found.</td>
                                </tr>
                            @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>
</body>
</html>
