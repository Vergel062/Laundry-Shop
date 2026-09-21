<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Tracking | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- SWEETALERT2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- EXTERNAL ADMIN CSS -->
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="true">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link" style="white-space: nowrap;"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.financial.reports') }}" class="nav-link" style="white-space: nowrap;"><i class="bi bi-graph-up-arrow"></i> Financial Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.utility.tracking') }}" class="nav-link active" style="white-space: nowrap;"><i class="bi bi-droplet"></i> Utility Report</a></li>
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
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2">
                <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                    @csrf
                    <button type="button" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Dinagdag ang 'utility-report-container' dito -->
    <div class="main-wrapper">
        <!-- Idinagdag ang 'no-print' dito para siguraduhing hindi sasama sa print/pdf -->
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom no-print">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Utility & Expenses Report</h5>
                <div class="ms-auto d-flex gap-2">
                    <button id="pdfBtn" onclick="exportUtilityToPDF()" class="btn btn-outline-danger btn-sm rounded-8 px-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Save PDF
                    </button>
                    <button onclick="window.print()" class="btn btn-dark btn-sm rounded-8 px-3 fw-bold">
                        <i class="bi bi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4 utility-report-container" id="printableArea">

            <!-- PRINT HEADER -->
            <div class="d-none print-document-header">
                <div>
                    <div class="print-brand">LaundryCare</div>
                    <small class="text-muted" style="font-size: 8pt; letter-spacing: 0.5px;">
                        BRANCH: <strong>{{ strtoupper($selected_branch_name ?? 'ALL BRANCHES') }}</strong>
                    </small>
                </div>
                <div class="print-doc-title">
                    <h2>Utility & Expenses Report</h2>
                    <p class="mb-0"><strong>Generated Date:</strong> {{ date('F d, Y h:i A') }}</p>
                    <p class="mb-0"><strong>Filter Period:</strong> 
                        {{ isset($date_from) && $date_from ? \Carbon\Carbon::parse($date_from)->format('M d, Y') : 'All Time' }} 
                        — 
                        {{ isset($date_to) && $date_to ? \Carbon\Carbon::parse($date_to)->format('M d, Y') : 'Present' }}
                    </p>
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="row g-3 mb-4 utility-stats-print">
                <div class="col-md-6 col-xl-3 stat-box">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-warning border-5 h-100 rounded-15">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning bg-opacity-10 rounded-3 me-3 no-print"><i class="bi bi-lightning-charge fs-4 text-warning"></i></div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase extra-small">Electricity Total</small>
                                <h3 class="fw-bold text-dark mt-1 mb-0">₱{{ number_format($electricity_total ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stat-box">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-primary border-5 h-100 rounded-15">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary bg-opacity-10 rounded-3 me-3 no-print"><i class="bi bi-droplet-fill fs-4 text-primary"></i></div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase extra-small">Water Bill Total</small>
                                <h3 class="fw-bold text-dark mt-1 mb-0">₱{{ number_format($water_total ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stat-box">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-success border-5 h-100 rounded-15">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success bg-opacity-10 rounded-3 me-3 no-print"><i class="bi bi-building fs-4 text-success"></i></div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase extra-small">Rent Total</small>
                                <h3 class="fw-bold text-dark mt-1 mb-0">₱{{ number_format($rent_total ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stat-box">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-info border-5 h-100 rounded-15">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info bg-opacity-10 rounded-3 me-3 no-print"><i class="bi bi-box-seam fs-4 text-info"></i></div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase extra-small">Supplies Total</small>
                                <h3 class="fw-bold text-dark mt-1 mb-0">₱{{ number_format($supplies_total ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER CONTROLS -->
            <div class="filter-section shadow-sm no-print">
                <form method="GET" action="{{ route('admin.utility.tracking') }}" class="row g-3 align-items-end utility-filter-form" autocomplete="off">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Branch View</label>
                        <select name="branch_id" class="form-select shadow-none bg-light border-0 py-2">
                            <option value="all" {{ ($selected_branch ?? '') == 'all' ? 'selected' : '' }}>All Branches</option>
                            @foreach($branches_dropdown as $b)
                                <option value="{{ $b->id }}" {{ ($selected_branch ?? '') == $b->id ? 'selected' : '' }}>
                                    {{ $b->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">From Date</label>
                        <input type="date" name="date_from" class="form-control shadow-none bg-light border-0 py-2" autocomplete="off" value="{{ $date_from ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">To Date</label>
                        <input type="date" name="date_to" class="form-control shadow-none bg-light border-0 py-2" autocomplete="off" value="{{ $date_to ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Search Keywords</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0 shadow-none py-2" placeholder="Water, Electricity, Rent, Supplies..." value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">Apply</button>
                        <a href="{{ route('admin.utility.tracking') }}" class="btn btn-light border shadow-sm px-3 d-flex align-items-center justify-content-center" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
            </div>

            <!-- TABLE UTILITY LOGS -->
            <div class="card border-0 shadow-sm rounded-15 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-stars me-2 text-primary"></i>Utility & Expenses History Logs</h6>
                </div>
                <div class="table-responsive scrollable-table-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Date Logged</th>
                                <th>Branch</th>
                                <th>Expense Type</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $type = strtolower($log->expense_type ?? '');
                                @endphp
                                <tr>
                                    <td class="ps-4 small text-muted">
                                        <div class="fw-bold text-dark">{{ $log->date_logged ? \Carbon\Carbon::parse($log->date_logged)->format('M d, Y') : '---' }}</div>
                                        <span class="extra-small text-info no-print">{{ $log->date_logged ? \Carbon\Carbon::parse($log->date_logged)->format('h:i A') : '' }}</span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border-0 small px-2">{{ $log->branch_name ?? '---' }}</span></td>
                                    <td class="fw-bold">
                                        @if(str_contains($type, 'elect') || str_contains($type, 'meralco') || str_contains($type, 'power'))
                                            <i class="bi bi-lightning-charge text-warning me-2 no-print"></i>
                                        @elseif(str_contains($type, 'water') || str_contains($type, 'maynilad') || str_contains($type, 'primewater'))
                                            <i class="bi bi-droplet text-primary me-2 no-print"></i>
                                        @elseif(str_contains($type, 'rent') || str_contains($type, 'renta'))
                                            <i class="bi bi-building text-success me-2 no-print"></i>
                                        @else
                                            <i class="bi bi-box-seam text-info me-2 no-print"></i>
                                        @endif
                                        {{ $log->expense_type ?? 'N/A' }}
                                    </td>
                                    <td class="text-danger fw-bold">₱{{ number_format($log->amount ?? 0, 2) }}</td>
                                    <td class="text-muted small">{{ $log->remarks ?: '---' }}</td>
                                    <td class="text-center"><span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">Verified</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small">No records found. Please choose a date and apply.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FOOTER & SIGNATURE -->
            <div class="d-none print-footer-block">
                <div class="row align-items-end">
                    <div class="col-7">
                        <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                            <strong>Report Prepared By:</strong> {{ Auth::user()->fullname ?? 'System Administrator' }}
                        </p>
                        <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                            <strong>Date & Time Generated:</strong> {{ date('F d, Y - h:i A') }}
                        </p>
                        <p class="mb-0 mt-2" style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                            * This official utility report is auto-generated by LaundryCare System.
                        </p>
                    </div>
                    <div class="col-5 text-end">
                        <div class="signature-line"></div>
                        <p class="fw-bold mb-0 mt-1" style="font-size: 9pt; text-transform: uppercase;">Authorized Signature</p>
                        <small style="font-size: 8pt; color: #64748b;">Administrator</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- EXTERNAL ADMIN JS -->
<script src="{{ asset('js/Admin_script.js') }}"></script>

<script>
    async function exportUtilityToPDF() {
        const pdfBtn = document.getElementById('pdfBtn');
        const originalElement = document.getElementById('printableArea');

        if (!originalElement) {
            alert('Error: Element #printableArea not found.');
            return;
        }

        if (typeof html2pdf !== 'function') {
            alert('Hindi ma-load ang PDF generator. I-refresh ang page at subukan muli.');
            return;
        }

        if (pdfBtn) {
            pdfBtn.disabled = true;
        }

        document.body.classList.add('is-exporting-pdf');

        const options = {
            margin: [10, 10, 10, 10],
            filename: 'Utility_Expenses_Report_' + new Date().toISOString().slice(0, 10) + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'], avoid: ['tr'] }
        };

        try {
            await html2pdf().set(options).from(originalElement).save();
        } catch (error) {
            console.error('Utility PDF Export Error:', error);
            alert('Nagkaroon ng problema sa pag-save ng PDF.');
        } finally {
            document.body.classList.remove('is-exporting-pdf');
            if (pdfBtn) {
                pdfBtn.disabled = false;
            }
        }
    }
</script>

</body>
</html>