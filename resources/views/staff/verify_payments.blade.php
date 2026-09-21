<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Payments | LaundryCare STAFF</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        #sidebar { min-width: 260px; max-width: 260px; background: #1e293b; color: #fff; display: flex; flex-direction: column; height: 100vh; z-index: 1050; transition: all 0.3s ease; overflow-x: hidden; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }
        #sidebar::-webkit-scrollbar { width: 6px; }
        #sidebar::-webkit-scrollbar-track { background: #1e293b; }
        #sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        #content { flex: 1; height: 100vh; overflow-y: auto; background-color: #f8fafc; }
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 5px 15px; transition: 0.2s; text-decoration: none; display: block; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        #sidebar .nav-link i { margin-right: 10px; }
        .sticky-nav { position: sticky; top: 0; z-index: 999; background: white; border-bottom: 1px solid #e2e8f0; }
        .filter-section { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; }
        .date-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .overlay { display: none; position: fixed; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; }
        @media (max-width: 768px) { #sidebar { position: fixed; left: -260px; } #sidebar.active { left: 0; } .overlay.active { display: block; } }
        
        .payment-card { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; overflow: hidden; }
        
        /* BINAGO: Nilagyan ng fixed height at vertical scroll ang table container at ginawang sticky ang header */
        .table-scroll-container {
            max-height: 520px;
            overflow-y: auto;
        }
        .table-scroll-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa !important;
            z-index: 10;
        }

        .proof-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #f1f5f9; transition: 0.2s; }
        .proof-thumb:hover { transform: scale(1.1); border-color: #0d6efd; }
        .status-badge { font-weight: 700; padding: 4px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; }
        .method-tag { background: #f1f5f9; color: #475569; font-weight: 600; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
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
            <button class="btn text-white d-md-none p-0 border-0 position-absolute" style="right: 20px; top: 50%; transform: translateY(-50%);" id="closeSidebar">
                <i class="bi bi-x-lg fs-4"></i>
            </button>
        </div>

        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a href="{{ route('staff.dashboard') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('staff.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('staff.verify.payments') }}" class="nav-link active"><i class="bi bi-patch-check"></i> Verify Payments</a></li>
            <li class="nav-item"><a href="{{ route('staff.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <hr class="mx-3 opacity-25">
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
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-1">Payment Verification</h3>
                    <p class="text-muted small">Verify online payments and view transaction history.</p>
                </div>
            </div>

            <!-- Date Picker & Search Filter Section -->
            <div class="filter-section p-3 mb-4 shadow-sm">
                <form action="{{ route('staff.verify.payments') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="date-label">History Start</label>
                        <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ $start_date ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="date-label">History End</label>
                        <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ $end_date ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Filter</button>
                    </div>
                    <div class="col-md-4">
                        <label class="date-label">Search</label>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ref # or Name..." value="{{ $search ?? '' }}">
                    </div>
                </form>
            </div>

            <div class="payment-card shadow-sm">
                <!-- BINAGO: Idinagdag ang class na 'table-scroll-container' dito -->
                <div class="table-responsive table-scroll-container">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th class="ps-4 py-3">CUSTOMER & REF</th>
                                <th>METHOD</th>
                                <th>PAYMENT REF</th>
                                <th class="text-center">AMOUNT</th>
                                <th class="text-center">PROOF</th>
                                <th class="text-center">STATUS / ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $row)
                                @php $is_paid = ($row->payment_status === 'Paid'); @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $row->fullname }}</div>
                                        <small class="text-muted">#{{ $row->ref_number }}</small>
                                    </td>
                                    <td><span class="method-tag">{{ strtoupper($row->payment_method) }}</span></td>
                                    <td><code class="text-primary fw-bold">{{ $row->payment_reference ?: 'N/A' }}</code></td>
                                    <td class="text-center fw-bold">₱{{ number_format($row->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        @if(!empty($row->proof_of_payment))
                                            <img src="{{ asset($row->proof_of_payment) }}" class="proof-thumb" onclick="viewProof('{{ asset($row->proof_of_payment) }}')">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($is_paid)
                                            <span class="status-badge bg-success-subtle text-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> Verified
                                            </span>
                                            <div style="font-size: 10px;" class="text-muted mt-1">
                                                {{ $row->date_paid ? date('M d, h:i A', strtotime($row->date_paid)) : '' }}
                                            </div>
                                        @else
                                            <form action="{{ route('staff.verify.payments.approve') }}" method="POST" class="d-inline" onsubmit="return confirmApproval(event, this);">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="approve_id" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill shadow-sm">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">No online payments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewProof(path) {
            Swal.fire({
                title: 'Proof of Payment',
                imageUrl: path,
                imageAlt: 'Proof of Payment',
                imageWidth: '100%',
                confirmButtonColor: '#1e293b'
            });
        }

        function confirmApproval(e, form) {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Payment?',
                text: "Have you checked the reference number and amount?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Approve it!'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        }

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar() { sidebar.classList.toggle('active'); overlay.classList.toggle('active'); }
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
            }).then((result) => { if (result.isConfirmed) logoutForm.submit(); });
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'verified') {
            Swal.fire({ icon: 'success', title: 'Payment Verified!', timer: 2000, showConfirmButton: false });
            window.history.replaceState({}, '', window.location.pathname);
            }
    </script>
</body>
</html>