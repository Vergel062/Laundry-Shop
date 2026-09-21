<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory History | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<div class="overlay" id="overlay"></div>
<div class="app-container">
    <nav id="sidebar">
        <div class="sidebar-brand p-4 text-center d-flex align-items-center justify-content-between">
            <h4 class="fw-bold text-info mb-0"><i class="bi bi-droplet-half"></i> Laundry<span class="text-white">Care</span></h4>
            <button class="btn text-white d-lg-none p-0 border-0" id="closeSidebar"><i class="bi bi-x-lg fs-4"></i></button>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i> Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link active"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span><i class="bi bi-chevron-down small"></i>
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
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-gear"></i> Settings</span><i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2"><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start"><i class="bi bi-power"></i> Logout</button></form></li>
        </ul>
    </nav>
    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Inventory History</h5>
                <div class="ms-auto">
                    <a href="{{ route('admin.inventory', ['branch_id' => $selected_branch]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Go Back
                    </a>
                </div>
            </div>
        </nav>
        <div class="container-fluid p-4">
            <div class="card shadow-sm border-0 rounded-15 mb-4 filter-section no-print">
                <div class="card-body p-2">
                    <form method="GET" action="{{ route('admin.inventory.history') }}" class="row g-3 align-items-end" autocomplete="off">
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Branch</label>
                            <select name="branch_id" class="form-select bg-light border-0 rounded-8 py-2">
                                <option value="all">All Branches</option>
                                @foreach($branches_dropdown as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $selected_branch === (string) $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Item</label>
                            <select name="item" class="form-select bg-light border-0 rounded-8 py-2">
                                <option value="all" {{ $selected_item === 'all' ? 'selected' : '' }}>All Items</option>
                                <option value="detergent" {{ $selected_item === 'detergent' ? 'selected' : '' }}>Detergent</option>
                                <option value="downy" {{ $selected_item === 'downy' ? 'selected' : '' }}>Downy / Softener</option>
                                <option value="spray" {{ $selected_item === 'spray' ? 'selected' : '' }}>Fabric Spray</option>
                                <option value="lpg" {{ $selected_item === 'lpg' ? 'selected' : '' }}>LPG</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ $start_date }}" lang="en-US" title="mm/dd/yyyy" class="form-control bg-light border-0 rounded-8 py-2" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted text-uppercase mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ $end_date }}" lang="en-US" title="mm/dd/yyyy" class="form-control bg-light border-0 rounded-8 py-2" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="filter" value="1" class="btn btn-primary w-100 fw-bold rounded-8 py-2 shadow-sm">
                                <i class="bi bi-filter me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mb-4 mt-2 screen-only-header no-print">
                <h3 class="fw-bold mb-0">INVENTORY HISTORY</h3>
                <p class="text-muted">
                    {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('M d, Y') : 'Start Date' }}
                    —
                    {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('M d, Y') : 'End Date' }}
                </p>
            </div>

            <div class="card shadow-sm border-0 rounded-15 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Inventory Movement History</h6>
                </div>
                <div class="table-responsive scrollable-table-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted"><tr>
                            <th class="ps-4">Date</th><th>Branch</th><th>Item</th><th>Movement</th><th>Quantity</th><th>Stock Before</th><th>Stock After</th><th>Source</th>
                        </tr></thead>
                        <tbody>
                        @if(!$hasDateFilter)
                            <tr><td colspan="8" class="text-center text-muted py-5">No records found. Please choose a date range and filter.</td></tr>
                        @else
                        @forelse($history as $record)
                            <tr>
                                <td class="ps-4">{{ date('M d, Y h:i A', strtotime($record->created_at)) }}</td>
                                <td>{{ $record->branch_name }}</td>
                                <td class="fw-semibold">{{ $record->item_name }}</td>
                                <td><span class="badge {{ $record->movement_type === 'IN' ? 'bg-success' : 'bg-danger' }}">{{ $record->movement_type }}</span></td>
                                <td class="fw-bold">{{ $record->quantity }}</td>
                                <td>{{ $record->stock_before }}</td>
                                <td>{{ $record->stock_after }}</td>
                                <td class="text-muted small">{{ $record->source }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">No inventory movement history found.</td></tr>
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
