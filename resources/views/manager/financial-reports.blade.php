<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Financial Reports |LaundryCare</title>
    
    <!-- FONTS & BOOTSTRAP -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- SWEETALERT2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- EXTERNAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    
    <!-- SWEETALERT2 & HTML2PDF JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-light" @if($export_mode) data-report-export-mode="{{ $export_mode }}" @endif>

<div class="overlay" id="overlay"></div>

<div class="app-container">
    <!-- SIDEBAR -->
    <nav id="sidebar" class="no-print">
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
            <li class="nav-item">
                <a href="{{ route('manager.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a>
            </li>
            <li class="nav-item"><a href="{{ route('manager.expenses') }}" class="nav-link"><i class="bi bi-wallet2"></i> Expenses</a></li>
            
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>

                <div class="collapse show" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('manager.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item">
                            <a href="{{ route('manager.financial.reports') }}" class="nav-link active" style="white-space: nowrap;">
                                <i class="bi bi-graph-up-arrow"></i> Financial Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.utility.tracking') }}" class="nav-link" style="white-space: nowrap;">
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
                        <li class="nav-item"><a href="{{ route('manager.register.customer') }}" class="nav-link"><i class="bi bi-shop"></i> Register Accounts</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mt-2">
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="nav-link text-danger logout-manager-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="main-wrapper">
        <!-- HEADER WITH PRINT & SAVE PDF BUTTONS -->
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom no-print">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Financial Reports</h5>
                <div class="ms-auto d-flex gap-2">
                    @include('shared.report-export-controls', [
                        'exportRoute' => route('manager.financial.reports'),
                        'pageCount' => $pageCount,
                        'canExport' => $totalRecords > 0,
                        'pdfTarget' => '#printableArea',
                        'pdfFilename' => 'Manager_Financial_Report',
                        'pdfMargin' => 10,
                        'pdfLayout' => 'financial-manager',
                        'printFunction' => 'triggerPrint',
                    ])
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">

            <!-- FILTER CONTROLS -->
            <div class="card shadow-sm border-0 rounded-15 mb-4 no-print">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('manager.financial.reports') }}" class="row g-3 align-items-end" autocomplete="off">
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control bg-light border-0 rounded-8 py-2" lang="en-US" title="mm/dd/yyyy" value="{{ $start_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control bg-light border-0 rounded-8 py-2" lang="en-US" title="mm/dd/yyyy" value="{{ $end_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Search</label>
                            <input type="search" name="search" class="form-control bg-light border-0 rounded-8 py-2" placeholder="Reference, service, customer..." value="{{ $search }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-8 py-2 shadow-sm">
                                <i class="bi bi-filter me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- EXPORT TARGET CONTAINER (ISOLATED FROM SIDEBAR AND GRID OFFSET) -->
            <div id="printableArea" style="width: 100%; max-width: 100%; box-sizing: border-box;">

                <!-- PRINT / PDF HEADER -->
                <div class="d-none print-document-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; width: 100%;">
                    <div>
                        <div class="print-brand">LaundryCare</div>
                        <small class="text-muted" style="font-size: 8pt; letter-spacing: 0.5px;">BRANCH: {{ strtoupper($branch_name ?? $branchName ?? 'SPECIFIC BRANCH') }}</small>
                    </div>
                    <div class="print-doc-title text-end">
                        <h2 style="font-size: 14pt; font-weight: 700; margin: 0; color: #0f172a;">Financial Statement</h2>
                        <p class="mb-0" style="font-size: 8.5pt;"><strong>Generated Date:</strong> <span class="printGeneratedDate">{{ date('F d, Y h:i A') }}</span></p>
                        <p class="mb-0" style="font-size: 8.5pt;"><strong>Filter Period:</strong> 
                            <span id="printFilterPeriod">
                                {{ isset($start_date) && $start_date ? \Carbon\Carbon::parse($start_date)->format('M d, Y') : 'All Time' }} 
                                — 
                                {{ isset($end_date) && $end_date ? \Carbon\Carbon::parse($end_date)->format('M d, Y') : 'Present' }}
                            </span>
                        </p>
                    </div>
                </div>
            
                <!-- SCREEN TITLE HEADER -->
                <div class="text-center mb-4 mt-2 screen-only-header">
                    <h3 class="fw-bold mb-0">OFFICIAL SERVICE REPORT</h3>
                    <p class="text-muted">
                        {{ isset($start_date) && $start_date ? \Carbon\Carbon::parse($start_date)->format('M d, Y') : 'Start Date' }} 
                        — 
                        {{ isset($end_date) && $end_date ? \Carbon\Carbon::parse($end_date)->format('M d, Y') : 'End Date' }}
                    </p>
                </div>

                <!-- SUMMARY CARDS -->
                <div class="summary-container mb-4" style="display: flex; gap: 15px; width: 100%;">
                    <div class="summary-box" style="flex: 1;">
                        <div class="card border-0 shadow-sm p-3 p-md-4 card-stats border-primary rounded-15 bg-white print-summary-card sales h-100">
                            <small class="text-muted fw-bold mb-1 d-block text-uppercase">Total Service Sales</small>
                            <h2 class="fw-bold text-primary mb-0 amount">₱{{ number_format($total_sales, 2) }}</h2>
                        </div>
                    </div>

                    <div class="summary-box" style="flex: 1;">
                        <div class="card border-0 shadow-sm p-3 p-md-4 card-stats border-danger rounded-15 bg-white print-summary-card expenses h-100">
                            <small class="text-muted fw-bold mb-1 d-block text-uppercase">Total Expenses</small>
                            <h2 class="fw-bold text-danger mb-0 amount">₱{{ number_format($total_expenses, 2) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- TRANSACTION HISTORY TABLE -->
                <div class="card shadow-sm border-0 rounded-15 overflow-hidden mb-4" style="width: 100%;">
                    <div class="card-header bg-white py-3 px-4 border-0 no-print">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Financial History</h6>
                            <form method="GET" action="{{ route('manager.financial.reports') }}" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <input type="hidden" name="start_date" value="{{ $start_date ?? '' }}">
                                <input type="hidden" name="end_date" value="{{ $end_date ?? '' }}">
                                <label for="financial-sort" class="small fw-semibold text-muted text-nowrap mb-0">Sort by:</label>
                                <select id="financial-sort" name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sort financial report transactions">
                                    <option value="newest" {{ ($sort_by ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                    <option value="oldest" {{ ($sort_by ?? '') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                    <option value="name_asc" {{ ($sort_by ?? '') === 'name_asc' ? 'selected' : '' }}>Customer Name: A–Z</option>
                                    <option value="name_desc" {{ ($sort_by ?? '') === 'name_desc' ? 'selected' : '' }}>Customer Name: Z–A</option>
                                    <option value="amount_desc" {{ ($sort_by ?? '') === 'amount_desc' ? 'selected' : '' }}>Amount: High to Low</option>
                                    <option value="amount_asc" {{ ($sort_by ?? '') === 'amount_asc' ? 'selected' : '' }}>Amount: Low to High</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <div class="table-responsive scrollable-table-container">
                        <table class="table table-hover align-middle mb-0" style="width: 100%; table-layout: fixed;">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th style="width: 16%;">Date</th>
                                    <th style="width: 14%;">Branch</th>
                                    <th style="width: 18%;">Customer</th>
                                    <th style="width: 18%;">Status</th>
                                    <th style="width: 14%;">Details</th>
                                    <th style="width: 12%;">Amount</th>
                                    <th style="width: 9%;" class="text-end pe-3">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($report_generated)
                                @php
                                    $rows = $transactions ?? $list_q ?? [];
                                @endphp
                                @forelse($rows as $row)
                                    @php
                                        $is_out = (($row->order_status ?? '') == 'Claimed');
                                        $track_label = $is_out ? 'OUT' : 'IN-SHOP';
                                        $track_color = $is_out ? 'bg-dark' : 'bg-info';
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold small">{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</div>
                                            <div class="text-muted extra-small" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($row->created_at)->format('h:i A') }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border-0 small px-2">{{ $row->branch_name ?? $branch_name ?? 'Branch' }}</span></td>
                                        <td class="small fw-bold text-truncate">{{ htmlspecialchars($row->customer_name ?? 'Walk-in') }}</td>
                                        <td>
                                            <span class="badge {{ $track_color }} bg-opacity-10 {{ $is_out ? 'text-dark' : 'text-info' }} border-0 px-2 py-1">
                                                {{ $track_label }} ({{ $row->order_status ?? 'N/A' }})
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-muted">{{ $row->service_type ?? 'Laundry' }}</div>
                                            <div class="extra-small text-muted" style="font-size: 0.7rem;">{{ $row->weight_kg ?? '0' }} kg</div>
                                        </td>
                                        <td class="text-primary fw-bold">₱{{ number_format($row->total_amount, 2) }}</td>
                                        <td class="text-end pe-3"><span class="badge bg-success bg-opacity-10 text-success border-0 px-2 py-1">PAID</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted small">No transactions found for this period.</td></tr>
                                @endforelse
                                @else
                                    <tr><td colspan="7" class="text-center py-5 text-muted small">No records found. Please choose a date and generate.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @include('shared.report-pagination', ['paginator' => $transactions])
                </div>

                <!-- FOOTER & SIGNATURE -->
                <div class="d-none print-footer-block" style="width: 100%; margin-top: 30px; box-sizing: border-box;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                        <div style="flex: 1; max-width: 60%;">
                            <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                                <strong>Report Prepared By:</strong> {{ Auth::user()->fullname ?? 'Branch Manager' }}
                            </p>
                            <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                                <strong>Date & Time Generated:</strong> <span class="printGeneratedDateTime">{{ date('F d, Y - h:i A') }}</span>
                            </p>
                            <p class="mb-0 mt-2" style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                                * This branch financial report is auto-generated by LaundryCare Manager System.
                            </p>
                        </div>
                        <div style="width: 200px; text-align: right;">
                            <div class="signature-block">
                                <div class="signature-line" style="width: 100%;"></div>
                                <p class="fw-bold mb-0 mt-1" style="font-size: 8.5pt; text-transform: uppercase; color: #0f172a;">Authorized Signature</p>
                            </div>
                            <small style="font-size: 7.5pt; color: #64748b;">Branch Manager</small>
                        </div>
                    </div>
                </div>
            </div> <!-- END printableArea -->

        </div>
    </div>
</div>

@include('shared.report-page-selection-modal')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>

<script>
function updatePrintTimestamp() {
    const now = new Date();
    const optionsDate = { month: 'long', day: '2-digit', year: 'numeric' };
    const optionsTime = { hour: 'numeric', minute: '2-digit', hour12: true };
    
    const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    const formattedTime = now.toLocaleTimeString('en-US', optionsTime);
    
    const dateString1 = `${formattedDate} ${formattedTime}`;
    const dateString2 = `${formattedDate} - ${formattedTime}`;
    
    document.querySelectorAll('.printGeneratedDate').forEach(el => el.innerText = dateString1);
    document.querySelectorAll('.printGeneratedDateTime').forEach(el => el.innerText = dateString2);
}

function triggerPrint() {
    updatePrintTimestamp();
    window.print();
}

</script>
<script src="{{ asset('js/report-page-export.js') }}"></script>

</body>
</html>