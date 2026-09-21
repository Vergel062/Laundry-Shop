<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Transaction History | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .rounded-15 { border-radius: 15px; }
        .card-stats { border-left: 5px solid; }
        .tracking-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .service-history-table-container {
            max-height: 460px;
            overflow-y: auto;
            overflow-x: auto;
        }
        .service-history-table-container thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #fff !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .service-history-report .print-document-header,
        .service-history-report .print-footer-block { display: none; }
        body.is-exporting-pdf .service-history-report .print-document-header {
            display: flex !important;
            width: 100% !important;
            border-bottom: 2px solid #0f172a !important;
        }
        body.is-exporting-pdf .service-history-report .print-footer-block { display: block !important; }
        body.is-exporting-pdf .service-history-report {
            width: 100% !important;
            padding: 0 !important;
            background: #fff !important;
        }
        body.is-exporting-pdf .service-history-report .service-history-stats {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
            width: 100% !important;
        }
        body.is-exporting-pdf .service-history-report .service-history-stats > [class*="col-"] {
            flex: 1 1 0 !important;
            width: 25% !important;
            max-width: 25% !important;
            min-width: 0 !important;
        }
        body.is-exporting-pdf .service-history-report .service-history-stats .card {
            padding: 12px !important;
        }
        body.is-exporting-pdf .service-history-report .table-responsive { overflow: visible !important; }
        body.is-exporting-pdf .service-history-report .service-history-table-container {
            max-height: none !important;
            overflow: visible !important;
        }
        body.is-exporting-pdf .service-history-report table { width: 100% !important; border-collapse: collapse !important; }
        body.is-exporting-pdf .service-history-report table th {
            background: #fff !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        body.is-exporting-pdf .service-history-report thead { display: table-header-group !important; }
        body.is-exporting-pdf .service-history-report tr { break-inside: avoid !important; page-break-inside: avoid !important; }
        @media print { 
            .no-print, #sidebar, .overlay, #sidebarToggle, .navbar, button { display: none !important; } 
            .main-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .service-history-report { padding: 0 !important; }
            .service-history-report .print-document-header { display: flex !important; }
            .service-history-report .print-footer-block { display: block !important; }
            .service-history-report .card { box-shadow: none !important; border: none !important; background: transparent !important; }
            .service-history-report .service-history-stats {
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 8px !important;
                width: 100% !important;
            }
            .service-history-report .service-history-stats > [class*="col-"] {
                flex: 1 1 0 !important;
                width: 25% !important;
                max-width: 25% !important;
                min-width: 0 !important;
            }
            .service-history-report .service-history-stats .card {
                padding: 12px !important;
            }
            .service-history-report .table-responsive { overflow: visible !important; }
            .service-history-report .service-history-table-container { max-height: none !important; overflow: visible !important; }
            .service-history-report table { width: 100% !important; border-collapse: collapse !important; }
            .service-history-report table th {
                background: #fff !important;
                color: #334155 !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            .service-history-report thead { display: table-header-group !important; }
            .service-history-report tr { break-inside: avoid !important; page-break-inside: avoid !important; }
            .service-history-report th, .service-history-report td { break-inside: avoid !important; }
            body, html { height: auto !important; overflow: visible !important; background: white !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="overlay" id="overlay"></div>

<div class="app-container">
    <nav id="sidebar" class="no-print">
        <div class="sidebar-brand p-4 text-center d-flex align-items-center justify-content-between">
            <h4 class="fw-bold text-info mb-0"><i class="bi bi-droplet-half"></i> Laundry<span class="text-white">Care</span></h4>
            <button class="btn text-white d-lg-none p-0 border-0" id="closeSidebar"><i class="bi bi-x-lg fs-4"></i></button>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link active"><i class="bi bi-clock-history"></i> Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.financial.reports', 'admin.utility.tracking', 'admin.transaction.report']) ? 'show' : '' }}" id="reportsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.transaction.report') }}" class="nav-link {{ Route::currentRouteName() == 'admin.transaction.report' ? 'active' : '' }}" style="white-space: nowrap;">
                                <i class="bi bi-file-earmark-text"></i> Transaction Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.financial.reports', ['branch_id' => $selected_branch ?? 'all']) }}" 
                               class="nav-link {{ Route::currentRouteName() == 'admin.financial.reports' ? 'active' : '' }}"
                               style="white-space: nowrap;">
                                <i class="bi bi-graph-up-arrow"></i> Financial Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.utility.tracking') }}" 
                               class="nav-link {{ Route::currentRouteName() == 'admin.utility.tracking' ? 'active' : '' }}"
                               style="white-space: nowrap;">
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
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.branch.management', 'admin.manage.users']) ? 'show' : '' }}" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.branch.management') }}" class="nav-link {{ Route::currentRouteName() == 'admin.branch.management' ? 'active' : '' }}">
                                <i class="bi bi-shop"></i> Branch Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.manage.users') }}" class="nav-link {{ Route::currentRouteName() == 'admin.manage.users' ? 'active' : '' }}">
                                <i class="bi bi-person-plus"></i> Manage Users
                            </a>
                        </li>
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
        <!-- NAVBAR NA MAY COMPACT PRINT BUTTON -->
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom no-print">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                    <h5 class="fw-bold mb-0">Transaction History</h5>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4 service-history-report" id="serviceHistoryPrintableArea">
            <div class="print-document-header align-items-end justify-content-between border-bottom border-2 pb-3 mb-4">
                <div>
                    <div class="print-brand">LaundryCare</div>
                    <small class="text-muted">LAUNDRY MANAGEMENT SYSTEM</small>
                </div>
                <div class="print-doc-title text-end">
                    <h2 class="mb-1">Service Transaction History</h2>
                    <p class="mb-0 small"><strong>Generated Date:</strong> {{ date('F d, Y h:i A') }}</p>
                    <p class="mb-0 small"><strong>Filter Period:</strong> {{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="card shadow-sm border-0 rounded-15 mb-4 no-print">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('admin.service.history') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Search Ref # / Name</label>
                            <input type="text" name="search" class="form-control bg-light border-0 rounded-8 py-2" placeholder="LC-XXXX or Name..." value="{{ htmlspecialchars($search ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Branch</label>
                            <select name="branch_id" class="form-select bg-light border-0 rounded-8 py-2">
                                <option value="all">All Branches</option>
                                @foreach($branches_dropdown as $b)
                                    <option value="{{ $b->id }}" {{ ($selected_branch ?? '') == $b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Start</label>
                            <input type="date" name="start_date" class="form-control bg-light border-0 rounded-8 py-2" value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted text-uppercase mb-2">End</label>
                            <input type="date" name="end_date" class="form-control bg-light border-0 rounded-8 py-2" value="{{ $end_date ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-8 py-2 shadow-sm">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4 service-history-stats">
                <div class="col-md-3"><div class="card border-0 shadow-sm p-4 card-stats border-primary rounded-15 bg-white"><small class="text-muted fw-bold">TOTAL SERVICE</small><h4 class="fw-bold mb-0">{{ $stats['total_trans'] ?? 0 }}</h4></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm p-4 card-stats border-info rounded-15 bg-white"><small class="text-muted fw-bold text-info">IN-SHOP (ACTIVE)</small><h4 class="fw-bold mb-0">{{ $stats['total_in'] ?? 0 }}</h4></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm p-4 card-stats border-dark rounded-15 bg-white"><small class="text-muted fw-bold text-dark">OUT (CLAIMED)</small><h4 class="fw-bold mb-0">{{ $stats['total_out'] ?? 0 }}</h4></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm p-4 card-stats border-success rounded-15 bg-white"><small class="text-muted fw-bold text-success">COLLECTED</small><h4 class="fw-bold mb-0">₱{{ number_format($stats['collected'] ?? 0, 2) }}</h4></div></div>
            </div>

            <div class="card shadow-sm border-0 rounded-15 overflow-hidden">
                <div class="table-responsive service-history-table-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>REF # / Date</th>
                                <th>Customer / Branch</th>
                                <th>Laundry Phase</th>
                                <th>Payment</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($list as $row)
                                @php
                                    $is_out = ($row->order_status === 'Claimed');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary">{{ $row->ref_number }}</div>
                                        <div class="extra-small text-muted" style="font-size: 0.7rem;">{{ date('M d, Y', strtotime($row->created_at)) }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">{{ htmlspecialchars($row->customer_name ?? 'Walk-in') }}</div>
                                        <div class="extra-small text-muted" style="font-size: 0.7rem;">{{ $row->branch_name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $is_out ? 'bg-dark' : 'bg-info' }} bg-opacity-10 {{ $is_out ? 'text-dark' : 'text-info' }} border-0 px-3">
                                            <span class="tracking-dot {{ $is_out ? 'bg-dark' : 'bg-info' }}"></span>
                                            {{ $is_out ? 'OUT / CLAIMED' : 'IN / ' . strtoupper($row->order_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $row->payment_status == 'Paid' ? 'bg-success text-success' : 'bg-danger text-danger' }} bg-opacity-10 border-0 px-3">
                                            {{ strtoupper($row->payment_status) }}
                                        </span>
                                        @if($row->payment_status == 'Paid')
                                            <div class="extra-small text-muted mt-1" style="font-size: 0.6rem;">{{ $row->payment_method }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 fw-bold">₱{{ number_format($row->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="print-footer-block border-top pt-3 mt-4">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="small text-muted">
                        <strong>Report Prepared By:</strong> {{ Auth::user()->fullname ?? 'System Administrator' }}<br>
                        <strong>Date & Time Generated:</strong> {{ date('F d, Y - h:i A') }}
                    </div>
                    <div class="text-end small text-muted">
                        <div class="border-top border-dark pt-1" style="width: 180px;">Authorized Signature</div>
                        <span>Administrator</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>
<script>
    async function exportServiceHistoryToPDF() {
        const button = document.getElementById('serviceHistoryPdfBtn');
        const report = document.getElementById('serviceHistoryPrintableArea');

        if (!report || typeof html2pdf !== 'function') {
            alert('Hindi ma-load ang PDF report. I-refresh ang page at subukan muli.');
            return;
        }

        if (button) button.disabled = true;
        document.body.classList.add('is-exporting-pdf');

        try {
            await html2pdf().set({
                margin: [10, 10, 10, 10],
                filename: 'Service_Transaction_History_' + new Date().toISOString().slice(0, 10) + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'], avoid: ['tr'] }
            }).from(report).save();
        } catch (error) {
            console.error('Service History PDF Export Error:', error);
            alert('Nagkaroon ng problema sa pag-save ng PDF.');
        } finally {
            document.body.classList.remove('is-exporting-pdf');
            if (button) button.disabled = false;
        }
    }
</script>
</body>
</html>