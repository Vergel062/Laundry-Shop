<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports | LaundryCare</title>
    
    <!-- FONTS & BOOTSTRAP -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- SWEETALERT2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- EXTERNAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    
    <!-- LIBRARIES -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-light">

<div class="overlay" id="overlay"></div>

<div class="app-container">
    <!-- SIDEBAR -->
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
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link" style="white-space: nowrap;"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item">
                            <a href="{{ route('admin.financial.reports') }}" class="nav-link active" style="white-space: nowrap;"> 
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

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom no-print">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Financial Reports</h5>
                <div class="ms-auto d-flex gap-2">
                    <button onclick="exportToPDF()" id="pdfBtn" class="btn btn-outline-danger btn-sm rounded-8 px-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Save PDF
                    </button>
                    <button onclick="triggerPrint()" class="btn btn-dark btn-sm rounded-8 px-3 fw-bold">
                        <i class="bi bi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">

            <div id="printableArea" style="width: 100%; max-width: 100%; box-sizing: border-box;">

            <!-- PRINT / PDF HEADER -->
           <div class="d-none print-document-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; width: 100%;">
                    <div>
                        <div class="print-brand">LaundryCare</div>
                        <small class="text-muted" style="font-size: 8pt; letter-spacing: 0.5px;">BRANCH: {{ strtoupper($selected_branch_name ?? 'ALL BRANCHES') }}</small>
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

            <!-- FILTER CONTROLS -->
            <div class="card shadow-sm border-0 rounded-15 mb-4 filter-section no-print">
                <div class="card-body p-2">
                    <form method="GET" action="{{ route('admin.financial.reports') }}" class="row g-3 align-items-end" autocomplete="off">
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Branch</label>
                            <select name="branch_id" class="form-select bg-light border-0 rounded-8 py-2">
                                <option value="all">All Branches</option>
                                @foreach($branches_dropdown as $b)
                                    <option value="{{ $b->id }}" {{ $selected_branch == $b->id ? 'selected' : '' }}>
                                        {{ $b->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control bg-light border-0 rounded-8 py-2" value="{{ $start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted text-uppercase mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control bg-light border-0 rounded-8 py-2" value="{{ $end_date }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-8 py-2 shadow-sm">
                                <i class="bi bi-filter me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

                
            <!-- SCREEN HEADER -->
            <div class="text-center mb-4 mt-2 screen-only-header no-print">
                <h3 class="fw-bold mb-0">OFFICIAL SALES REPORT</h3>
                <p class="text-muted">
                    {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('M d, Y') : 'Start Date' }}
                    —
                    {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('M d, Y') : 'End Date' }}
                </p>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="summary-container mb-4">
                <div class="summary-box">
                    <div class="card border-0 shadow-sm p-3 p-md-4 card-stats border-primary rounded-15 bg-white print-summary-card sales h-100">
                        <small class="text-muted fw-bold mb-1 d-block text-uppercase">Total Service Sales</small>
                        <h2 class="fw-bold text-primary mb-0 amount">₱{{ number_format($total_sales, 2) }}</h2>
                    </div>
                </div>

                <div class="summary-box">
                    <div class="card border-0 shadow-sm p-3 p-md-4 card-stats border-danger rounded-15 bg-white print-summary-card expenses h-100">
                        <small class="text-muted fw-bold mb-1 d-block text-uppercase">Total Expenses</small>
                        <h2 class="fw-bold text-danger mb-0 amount">₱{{ number_format($total_expenses, 2) }}</h2>
                    </div>
                </div>
            </div>

            <!-- TRANSACTION HISTORY TABLE -->
            <div class="card shadow-sm border-0 rounded-15 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center no-print">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Transaction History</h6>
                </div>
                
                <div class="table-responsive scrollable-table-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Date</th>
                                <th>Branch</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Details</th>
                                <th>Amount</th>
                                <th class="text-end pe-4">Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($report_generated)
                            @forelse($list_q as $row)
                                @php
                                    $is_out = ($row->order_status == 'Claimed');
                                    $track_label = $is_out ? 'OUT' : 'IN-SHOP';
                                    $track_color = $is_out ? 'bg-dark' : 'bg-info';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold small">{{ date('M d, Y', strtotime($row->created_at)) }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.7rem;">{{ date('h:i A', strtotime($row->created_at)) }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border-0 small px-2">{{ $row->branch_name }}</span></td>
                                    <td class="small fw-bold">{{ htmlspecialchars($row->customer_name ?? 'Walk-in') }}</td>
                                    <td>
                                        <span class="badge {{ $track_color }} bg-opacity-10 {{ $is_out ? 'text-dark' : 'text-info' }} border-0 px-3">
                                            {{ $track_label }} ({{ $row->order_status }})
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-muted">{{ $row->service_type ?? 'Laundry' }}</div>
                                        <div class="extra-small text-muted" style="font-size: 0.7rem;">{{ $row->weight_kg }} kg</div>
                                    </td>
                                    <td class="text-primary fw-bold">₱{{ number_format($row->total_amount, 2) }}</td>
                                    <td class="text-end pe-4"><span class="badge bg-success bg-opacity-10 text-success border-0 px-3">PAID</span></td>
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
            </div>

            <!-- FOOTER & SIGNATURE -->
            <div class="d-none print-footer-block">
                <div class="row align-items-end w-100 m-0">
                    <div class="col-7 p-0">
                        <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                            <strong>Report Generated By:</strong> System Admin
                        </p>
                        <p class="mb-1" style="font-size: 8.5pt; color: #475569;">
                            <strong>Date & Time Generated:</strong> <span class="printGeneratedDateTime">{{ date('F d, Y - h:i A') }}</span>
                        </p>
                        <p class="mb-0 mt-2" style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                            * This financial statement is auto-generated by LaundryCare System.
                        </p>
                    </div>
                    <div class="col-5 text-end p-0">
                        <div class="signature-line"></div>
                        <p class="fw-bold mb-0 mt-1" style="font-size: 9pt; text-transform: uppercase;">Authorized Signature</p>
                        <small style="font-size: 8pt; color: #64748b;">Finance / Admin Officer</small>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

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

async function exportToPDF() {
    const pdfBtn = document.getElementById('pdfBtn');
    const element = document.getElementById('printableArea');

    if (!element) return;

    if (pdfBtn) {
        pdfBtn.disabled = true;
    }

    updatePrintTimestamp();

    // Idagdag ang eksaktong class na gamit sa CSS para ma-expand ang buong table
    document.body.classList.add('is-exporting-pdf');

    const opt = {
        margin:       [8, 8, 8, 8],
        filename:     'Financial_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true, 
            logging: false,
            scrollY: 0
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['css', 'legacy'] }
    };

    try {
        await html2pdf().set(opt).from(element).save();
    } catch (error) {
        console.error('PDF Export Error:', error);
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