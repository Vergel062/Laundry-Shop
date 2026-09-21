<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expenses | LaundryCare STAFF</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR STYLING */
        #sidebar { min-width: 260px; max-width: 260px; background: #1e293b; color: #fff; display: flex; flex-direction: column; height: 100vh; z-index: 1050; transition: all 0.3s ease; overflow-x: hidden; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }
        #sidebar::-webkit-scrollbar { width: 6px; }
        #sidebar::-webkit-scrollbar-track { background: #1e293b; }
        #sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        #content { flex: 1; height: 100vh; overflow-y: auto; background-color: #f8fafc; }
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 5px 15px; transition: 0.2s; text-decoration: none; display: block; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        #sidebar .nav-link i { margin-right: 10px; }

        .sticky-nav { position: sticky; top: 0; z-index: 999; background: white; border-bottom: 1px solid #e2e8f0; }

        /* MOBILE VIEW LOGIC */
        .overlay { display: none; position: fixed; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; }
        @media (max-width: 768px) {
            #sidebar { position: fixed; left: -260px; }
            #sidebar.active { left: 0; }
            .overlay.active { display: block; }
        }

        .registration-card { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; }
        .form-label-custom { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .customer-table-card { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; overflow: hidden; }
        .expense-badge { background: #fef2f2; color: #dc2626; font-weight: 700; padding: 5px 12px; border-radius: 20px; font-size: 12px; border: 1px solid #fee2e2; }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <nav id="sidebar" class="shadow">
        <div class="p-4 text-center position-relative">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-droplet-half text-info"></i> Staff
                <span class="small text-muted" style="font-size: 10px;">STAFF</span>
            </h4>
            <button class="btn text-white d-md-none p-0 border-0 position-absolute" 
                    style="right: 20px; top: 50%; transform: translateY(-50%);" 
                    id="closeSidebar">
                <i class="bi bi-x-lg fs-4"></i>
            </button>
        </div>

        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a href="{{ route('staff.dashboard') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('staff.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('staff.verify.payments') }}" class="nav-link"><i class="bi bi-patch-check"></i> Verify Payments</a></li>
             <li class="nav-item"><a href="{{ route('staff.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <hr class="mx-3 opacity-25">
           
            <li class="nav-item"><a href="{{ route('staff.expenses') }}" class="nav-link active"><i class="bi bi-wallet2"></i> Daily Expenses</a></li>
            <li class="nav-item mt-1">
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-light sticky-nav px-4 py-3">
            <div class="container-fluid">
                <button class="btn btn-light d-md-none me-2" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <span class="navbar-text fw-semibold">
                    Branch: <span class="text-primary text-uppercase">{{ $branch_name }}</span>
                </span>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="mb-3">
                        <h4 class="fw-bold mb-1">Filter Records</h4>
                        <p class="text-muted small">Search by date range or expense type.</p>
                    </div>
                    <div class="registration-card p-4 shadow-sm">
                        <form action="{{ route('staff.expenses') }}" method="GET">
                            <div class="mb-3">
                                <label class="form-label-custom">Start Date</label>
                                <input type="date" name="start_date" class="form-control border-0 bg-light py-2 shadow-none" value="{{ $start_date }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">End Date</label>
                                <input type="date" name="end_date" class="form-control border-0 bg-light py-2 shadow-none" value="{{ $end_date }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Search</label>
                                <input type="text" name="search" class="form-control border-0 bg-light py-2 shadow-none" placeholder="e.g. Water, Rent..." value="{{ $search }}">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">APPLY FILTER</button>
                                <a href="{{ route('staff.expenses') }}" class="btn btn-light fw-bold py-2 border text-dark">RESET</a>
                            </div>
                        </form>
                    </div>

                    <div class="registration-card p-4 shadow-sm mt-3 bg-primary bg-opacity-10 border-primary border-opacity-25">
                        <label class="form-label-custom text-primary">Total Expenses</label>
                        <h3 class="fw-bold text-dark mb-0">₱{{ number_format($range_total, 2) }}</h3>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="mb-3">
                        <h4 class="fw-bold mb-1">Expense Logs</h4>
                        <p class="text-muted small">Showing records from {{ date('M d, Y', strtotime($start_date)) }} to {{ date('M d, Y', strtotime($end_date)) }}</p>
                    </div>
                    <div class="customer-table-card shadow-sm">
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
                                                <span class="expense-badge">₱{{ number_format($row->amount, 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted small">No records found.</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);

        document.querySelector('.logout-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const logoutForm = document.getElementById('logoutForm');
            Swal.fire({
                title: 'Confirm Logout',
                text: "Are you sure you want to end your staff session?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Logout'
            }).then((result) => {
                if (result.isConfirmed) { logoutForm.submit(); }
            });
        });
    </script>
</body>
</html>