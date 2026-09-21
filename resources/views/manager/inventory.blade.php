<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .modal-content { border-radius: 20px; border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 25px; }
        .modal-body { padding: 30px; background-color: #fcfcfc; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 20px 30px; }
        
        .input-group-text { background-color: #fff; border-right: none; color: #64748b; }
        .form-control { border-left: none; padding: 10px 15px; border-color: #e2e8f0; }
        .form-control:focus { box-shadow: none; border-color: #e2e8f0; }
        
        .section-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: block; }
        .update-card { background: #fff; border: 1px solid #eef2f7; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
    </style>
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
            <li class="nav-item">
                <a href="{{ route('manager.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a>
            </li>
            <!-- IDINAGDAG: Service List Link -->
            <li class="nav-item"><a href="{{ route('manager.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item">
                <a href="{{ route('manager.verify.payments') }}" class="nav-link {{ request()->routeIs('manager.verify.payments*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check"></i> Verify Payments
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('manager.inventory') }}" class="nav-link active"><i class="bi bi-box-seam"></i> Inventory</a>
            </li>
            <li class="nav-item"><a href="{{ route('manager.expenses') }}" class="nav-link"><i class="bi bi-wallet2"></i> Expenses</a></li>
            
            <hr class="mx-3 opacity-25">
            <li class="nav-item mt-2">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>

                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('manager.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('manager.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Report</a></li>
                        <li class="nav-item"><a href="{{ route('manager.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Report</a></li>
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
                    <button type="submit" class="nav-link text-danger logout-manager-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- New Header / Navbar -->
        <nav class="navbar navbar-light px-4 py-3 sticky-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <span class="navbar-text fw-semibold">Branch: 
                    <span class="text-primary text-uppercase">
                        {{ $branch_name ?? $branchName ?? 'Unknown Branch' }}
                    </span>
                </span>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="small mb-0 fw-bold">{{ Auth::user()->fullname ?? 'Manager' }}</p>
                    <p class="small text-muted mb-0">Branch Manager</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->fullname ?? 'Manager') }}&background=0ea5e9&color=fff" class="rounded-circle shadow-sm" width="40" alt="Manager Profile">
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="container-fluid p-4">
            <div class="chart-container shadow-sm p-0 overflow-hidden" style="background: white; border-radius: 15px;">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-box-seam text-info me-2"></i>Stock Overview</h6>
                        <small class="text-muted">Current inventory levels for this branch.</small>
                    </div>
                    <button class="btn btn-primary fw-bold shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#inventoryModal">
                        <i class="bi bi-pencil-square me-2"></i> UPDATE STOCKS
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-4 py-3">Item Name</th>
                                <th>Stock Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $row)
                                @php 
                                    $itemNameLower = strtolower(trim($row->item_name));
                                    $threshold = $row->min_threshold;

                                    if (str_contains($itemNameLower, 'detergent') || str_contains($itemNameLower, 'softener') || str_contains($itemNameLower, 'downy')) {
                                        $threshold = 15;
                                    } elseif (str_contains($itemNameLower, 'spray')) {
                                        $threshold = 5;
                                    } elseif (str_contains($itemNameLower, 'lpg') || str_contains($itemNameLower, 'gas')) {
                                        $threshold = 3;
                                    }

                                    $is_low = ($row->stock_level <= $threshold);
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $row->item_name }}</div>
                                        <small class="text-muted">Last updated: {{ \Carbon\Carbon::parse($row->last_updated)->format('M d') }}</small>
                                    </td>
                                    <td class="fw-bold text-primary fs-5">
                                        {{ number_format($row->stock_level) }} 
                                        <span class="text-muted small" style="font-size: 12px;">{{ $row->unit }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $is_low ? 'danger' : 'success' }} bg-opacity-10 text-{{ $is_low ? 'danger' : 'success' }} rounded-pill px-3 py-2">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> {{ $is_low ? 'Low Stock' : 'Available' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No inventory records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('manager.inventory.update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-box-seam text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Stock Management</h5>
                            <small class="text-muted">Adjust branch inventory</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <span class="section-label">Restock (Add to current)</span>
                    <div class="update-card">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-plus-circle"></i></span>
                                <input type="number" name="detergent_stock" class="form-control" placeholder="Powder Detergent Qty">
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-plus-circle"></i></span>
                                <input type="number" name="downy_stock" class="form-control" placeholder="Downy Softener Qty">
                            </div>
                        </div>
                    </div>

                    <span class="section-label">Manual Adjustment (Set balance)</span>
                    <div class="update-card">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-moisture"></i></span>
                                <input type="number" name="spray_stock" class="form-control" placeholder="Spray Bottle Count">
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-fuel-pump"></i></span>
                                <input type="number" name="lpg_stock" class="form-control" placeholder="LPG Tank Balance">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">CONFIRM CHANGES</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    @if(session('status') === 'success')
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Your branch inventory has been updated.',
            confirmButtonColor: '#0d6efd',
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('sidebarToggle')?.addEventListener('click', () => { 
            document.getElementById('sidebar').classList.add('active'); 
            document.getElementById('overlay').classList.add('active'); 
        });
        document.getElementById('closeSidebar')?.addEventListener('click', () => { 
            document.getElementById('sidebar').classList.remove('active'); 
            document.getElementById('overlay').classList.remove('active'); 
        });
    });

    document.addEventListener('click', function(e) {
        const logoutBtn = e.target.closest('.logout-manager-btn');
        if (logoutBtn) {
            e.preventDefault(); 

            Swal.fire({
                title: 'Confirm Logout',
                text: "Are you sure you want to end your manager session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }
    });
</script>
</body>
</html>