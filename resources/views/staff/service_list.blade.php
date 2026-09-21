<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Tracking | LaundryCare STAFF</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/Staff.css') }}">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        #sidebar { min-width: 260px; max-width: 260px; background: #1e293b; color: #fff; display: flex; flex-direction: column; height: 100vh; z-index: 1050; transition: all 0.3s ease; overflow-x: hidden; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }
        #sidebar::-webkit-scrollbar { width: 6px; }
        #sidebar::-webkit-scrollbar-track { background: #1e293b; }
        #sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        #content { flex: 1; height: 100vh; overflow-y: auto; background-color: #f8fafc; display: flex; flex-direction: column; }
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 5px 15px; transition: 0.2s; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        #sidebar .nav-link i { margin-right: 10px; }
        .sticky-nav { position: sticky; top: 0; z-index: 999; background: white; border-bottom: 1px solid #e2e8f0; }
        .filter-section { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; }
        .date-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .overlay { display: none; position: fixed; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; }
        
        .table-scroll-container {
            max-height: calc(100vh - 340px);
            overflow-y: auto;
        }
        .table-scroll-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }

        @media (max-width: 768px) { #sidebar { position: fixed; left: -260px; } #sidebar.active { left: 0; } .overlay.active { display: block; } }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="d-flex w-100">
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
                <li class="nav-item"><a href="{{ route('staff.services') }}" class="nav-link active"><i class="bi bi-clock-history"></i>Transaction History</a></li>
                <li class="nav-item"><a href="{{ route('staff.verify.payments') }}" class="nav-link"><i class="bi bi-patch-check"></i> Verify Payments</a></li>
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
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 sticky-nav">
                <button type="button" id="sidebarToggle" class="btn btn-light d-md-none me-3">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="navbar-text fw-semibold">Branch: 
                    <span class="text-primary text-uppercase">
                        {{ $branch_name ?? $branchName ?? 'Unknown Branch' }}
                    </span>
                </span>
            </nav>

            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">Transaction History</h3>
                        <p class="text-muted small mb-0">Monitor active orders, update status, and encode new transactions.</p>
                    </div>
                    <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                        <button class="btn btn-primary btn-pos shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                            <i class="bi bi-plus-lg me-2"></i> NEW SERVICE
                        </button>
                    </div>
                </div>

                <div class="filter-section p-3 mb-4 shadow-sm">
                    <form action="{{ route('staff.services') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="date-label">History Start</label>
                            <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="date-label">History End</label>
                            <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ $end_date ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Filter</button>
                        </div>
                        <div class="col-md-4">
                            <label class="date-label">Search</label>
                            <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ref # or Name..." value="{{ $search_val ?? '' }}">
                        </div>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive table-scroll-container">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted">
                                    <th class="ps-4 py-3">DATE</th>
                                    <th>REF #</th>
                                    <th>CUSTOMER</th>
                                    <th>SERVICE</th>
                                    <th>STATUS</th>
                                    <th>PAYMENT</th>
                                    <th class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions ?? [] as $row)
                                    @php
                                        $status_color = match($row->order_status) {
                                            'Pending' => 'primary', 
                                            'Washing' => 'warning', 
                                            'Drying' => 'info', 
                                            'Ready' => 'success', 
                                            'Claimed' => 'dark', 
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4 small text-muted">{{ date('M d', strtotime($row->created_at)) }}</td>
                                        <td class="fw-bold">{{ $row->ref_number }}</td>
                                        <td class="fw-semibold">{{ $row->fullname }}</td>
                                        <td>{{ ($row->payment_status === 'Service Request') ? 'New Service Request' : $row->service_type }}</td>
                                        <td><span class="badge bg-{{ $status_color }}-subtle text-{{ $status_color }} rounded-pill px-3">{{ $row->order_status }}</span></td>
                                        <td><span class="badge bg-{{ ($row->payment_status == 'Paid') ? 'success' : 'danger' }} px-3">{{ $row->payment_status }}</span></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($row->order_status === 'Pending' && $row->payment_status === 'Service Request')
                                                    <button class="btn btn-sm btn-success approve-request-btn"
                                                            data-id="{{ $row->id }}"
                                                            data-user="{{ $row->user_id }}"
                                                            data-weight="{{ $row->weight_kg }}"
                                                            data-service="{{ $row->service_type }}"
                                                            data-bs-toggle="modal" data-bs-target="#newOrderModal"
                                                            title="Approve Request">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </button>
                                                @else
                                                <button class="btn btn-sm btn-light border edit-btn" 
                                                        data-id="{{ $row->id }}" 
                                                        data-status="{{ $row->order_status }}" 
                                                        data-payment="{{ $row->payment_status }}"
                                                        data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                </button>
                                                @endif
                                                
                                                @if($row->payment_status !== 'Service Request')
                                                    <a href="{{ route('staff.print.receipt', $row->id) }}" target="_blank" class="btn btn-sm btn-light border" title="Print Receipt">
                                                        <i class="bi bi-printer text-secondary"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center p-5 text-muted">No transactions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- UPDATE STATUS MODAL -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-0">
                    <h6 class="modal-title small">Update Transaction</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateTransactionForm" action="{{ route('staff.services.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="transaction_id" id="mod_trans_id">
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">Order Status:</label>
                            <select name="new_status" id="mod_status" class="form-select border-0 bg-light shadow-none">
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">Payment Status:</label>
                            <select name="payment_status" id="mod_payment" class="form-select border-0 bg-light shadow-none">
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- NEW ORDER MODAL -->
    <div class="modal fade" id="newOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">New Laundry Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="orderForm" action="{{ route('staff.save.order') }}" method="POST"> 
                    @csrf
                    <input type="hidden" name="request_id" id="requestIdInput">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Customer</label>
                            <select name="user_id" id="customerSelect" class="form-select" required>
                                <option value="" disabled selected>Choose Registered Customer</option>
                                @foreach($customers ?? [] as $u)
                                    <option value="{{ $u->id }}">{{ $u->fullname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Weight (kg)</label>
                                <input type="number" step="0.01" name="weight" id="weightInput" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Service Type</label>
                                <select name="service" id="serviceSelect" class="form-select" required>
                                    <option value="" disabled selected>-- Select Service Type --</option>
                                    <option value="Wash Only" data-base-price="70">Wash Only - ₱70</option>
                                    <option value="Dry Only" data-base-price="60">Dry Only - ₱60</option>
                                    <option value="Wash-Dry" data-base-price="130">Wash-Dry - ₱130</option>
                                    <option value="Wash-Dry-Fold" data-base-price="170">Wash-Dry-Fold - ₱170</option>
                                    <option value="Comforter (Wash Only)" data-base-price="140">Comforter (Wash Only) - ₱140</option>
                                    <option value="Comforter (Dry Only)" data-base-price="130">Comforter (Dry Only) - ₱130</option>
                                    <option value="Comforter (Wash-Dry)" data-base-price="200">Comforter (Wash-Dry) - ₱200</option>
                                    <option value="Comforter (Wash-Dry-Fold)" data-base-price="240">Comforter (Wash-Dry-Fold) - ₱240</option>
                                </select>
                            </div>
                        </div>

                        <!-- Supplies Used Section -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <h6 class="fw-bold small mb-3 text-primary"><i class="bi bi-box-seam me-1"></i> Add Used Sachets</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label extra-small fw-bold text-muted" style="font-size: 11px;">Powder Detergent</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="bi bi-droplet"></i></span>
                                        <input type="number" name="det_qty" class="form-control" placeholder="0" min="0" step="1" onfocus="this.select()">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label extra-small fw-bold text-muted" style="font-size: 11px;">Downy Softener</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="bi bi-heart"></i></span>
                                        <input type="number" name="soft_qty" class="form-control" placeholder="0" min="0" step="1" onfocus="this.select()">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 10px;">I-type ang bilang ng sachet na nagamit (iwanang blanko kung wala).</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Total Amount (₱)</label>
                            <input type="number" step="0.01" name="amount" id="amountInput" class="form-control fw-bold text-primary" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">SAVE ORDER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/Staff_script.js') }}"></script>
    <script>
        document.querySelectorAll('.approve-request-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('requestIdInput').value = this.dataset.id;
                document.getElementById('customerSelect').value = this.dataset.user;
                document.getElementById('weightInput').value = '';
                document.getElementById('serviceSelect').value = '';
                document.getElementById('amountInput').value = '';
            });
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const currentStatus = this.getAttribute('data-status');
                document.getElementById('mod_trans_id').value = this.getAttribute('data-id');
                document.getElementById('mod_payment').value = this.getAttribute('data-payment');

                const statusSelect = document.getElementById('mod_status');
                statusSelect.innerHTML = ''; 

                const statuses = ['Pending', 'Washing', 'Drying', 'Ready', 'Claimed'];
                const currentIndex = statuses.indexOf(currentStatus);

                statuses.forEach((status, index) => {
                    if (index >= currentIndex) {
                        const opt = document.createElement('option');
                        opt.value = status;
                        opt.text = status;
                        if(status === currentStatus) opt.selected = true;
                        statusSelect.appendChild(opt);
                    }
                });
            });
        });

        document.getElementById('updateTransactionForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: 'Update Transaction?',
                text: "Are you sure you want to save these status changes?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 2500,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}"
            });
        @endif

        @if ($errors->any())
            var newOrderModal = new bootstrap.Modal(document.getElementById('newOrderModal'));
            newOrderModal.show();
        @endif

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
    </script>
</body>
</html>