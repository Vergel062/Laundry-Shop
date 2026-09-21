<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Dashboard | LaundryCare POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/Staff.css') }}">
</head>
<style>
    #sidebar {
        min-width: 260px;
        max-width: 260px;
        background: #1e293b;
        color: #fff;
        display: flex;
        flex-direction: column;
        height: 100vh;
        z-index: 1050;
        transition: all 0.3s ease;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #475569 #1e293b;
    }
    #sidebar::-webkit-scrollbar { width: 6px; }
    #sidebar::-webkit-scrollbar-track { background: #1e293b; }
    #sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
    #content { 
        flex: 1;
        height: 100vh;
        overflow-y: auto;
        background-color: #f8fafc;
    }
    #sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        padding: 12px 20px;
        border-radius: 8px;
        margin: 5px 15px;
        transition: 0.2s;
    }
    #sidebar .nav-link:hover, #sidebar .nav-link.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    #sidebar .nav-link i { margin-right: 10px; }
    .sticky-nav {
        position: sticky;
        top: 0;
        z-index: 999;
        background: white;
        border-bottom: 1px solid #e2e8f0;
    }
    .overlay {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }
    @media (max-width: 768px) {
        #sidebar { position: fixed; left: -260px; }
        #sidebar.active { left: 0; }
        .overlay.active { display: block; }
    }
</style>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="d-flex">
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
            <li class="nav-item"><a href="{{ route('staff.dashboard') }}" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('staff.services') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('staff.verify.payments') }}" class="nav-link"><i class="bi bi-patch-check"></i> Verify Payments</a></li>
            <li class="nav-item"><a href="{{ route('staff.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <hr class="mx-3 opacity-25">
            
            <li class="nav-item mt-1">
                <form action="{{ route('logout') }}" method="POST" id="logoutForm">
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
                        {{ $branchName ?? 'Unknown Branch' }}
                    </span>
                </span>
            </nav>

            <div class="container-fluid p-4">
                
                {{-- LPG Critical Warning (Alert kapag 2 o pababa) --}}
            @if (isset($lpg) && $lpg && $lpg->stock_level <= 2)
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-3 d-flex align-items-center animate__animated animate__pulse animate__infinite">
                <i class="bi bi-fuel-pump-fill fs-2 me-3"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-danger">LPG CRITICAL WARNING</h6>
                    <p class="small mb-0 text-dark">Detected low gas level. Please coordinate for a refill immediately. Current: <span class="fw-bold">{{ number_format($lpg->stock_level, 0) }} Tanks</span></p>
                </div>
            </div>
            @endif

                {{-- Low Stock Cards for Detergent, Softener, and Spray --}}
                <div class="row mb-3">
                    {{-- Detergent Alert (Alert kapag 15 o pababa) --}}
                    @if (isset($detergent) && $detergent && $detergent->stock_level <= 15)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-3" style="border-left: 5px solid #0dcaf0 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <i class="bi bi-droplet-fill text-info fs-3 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 small">Low Detergent</h6>
                                    <small class="text-muted">Stock: <span class="text-danger fw-bold">{{ number_format($detergent->stock_level, 0) }} Sachets</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Softener Alert (Alert kapag 15 o pababa) --}}
                    @if (isset($downy) && $downy && $downy->stock_level <= 15)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-3" style="border-left: 5px solid #e83e8c !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <i class="bi bi-heart-fill fs-3 me-3" style="color: #e83e8c;"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 small">Low Softener</h6>
                                    <small class="text-muted">Stock: <span class="text-danger fw-bold">{{ number_format($downy->stock_level, 0) }} Sachets</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Fabric Spray Alert (Alert kapag 5 o pababa) --}}
                    @if (isset($spray) && $spray && $spray->stock_level <= 5)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-3" style="border-left: 5px solid #6610f2 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <i class="bi bi-wind text-primary fs-3 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 small">Low Fabric Spray</h6>
                                    <small class="text-muted">Stock: <span class="text-danger fw-bold">{{ number_format($spray->stock_level, 0) }} Gallons</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <h3 class="fw-bold">Staff Control Panel</h3>
                        <p class="text-muted">Manage orders and shop operations efficiently.</p>
                    </div>
                    <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                        <button class="btn btn-primary btn-pos shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                            <i class="bi bi-plus-lg me-2"></i> NEW SERVICE
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="card card-stats p-3 border-start border-primary border-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Pending Wash</small>
                            <h3 class="fw-bold mb-0">{{ $pending_count ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card card-stats p-3 border-start border-warning border-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">For Drying</small>
                            <h3 class="fw-bold mb-0">{{ $drying_count ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card card-stats p-3 border-start border-info border-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Ready to Claim</small>
                            <h3 class="fw-bold mb-0">{{ $ready_count ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card card-stats p-3 border-start border-success border-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Today's Sales</small>
                            <h3 class="fw-bold mb-0">₱{{ number_format($today_sales ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="filter-wrapper bg-light p-1 rounded-3 d-inline-flex mb-3 mt-3 ms-3">
                                <a href="{{ route('staff.dashboard', ['filter' => 'All']) }}" class="btn btn-filter {{ (($filter ?? 'All') == 'All') ? 'active' : '' }}">All Service</a>
                                <a href="{{ route('staff.dashboard', ['filter' => 'Pending']) }}" class="btn btn-filter {{ (($filter ?? 'All') == 'Pending') ? 'active' : '' }}"><i class="bi bi-droplet me-1"></i> Washing</a>
                                <a href="{{ route('staff.dashboard', ['filter' => 'Ready']) }}" class="btn btn-filter {{ (($filter ?? 'All') == 'Ready') ? 'active' : '' }}"><i class="bi bi-check2-all me-1"></i> Ready</a>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr class="small text-muted">
                                            <th class="ps-4 bg-light">CUSTOMER / REF</th>
                                            <th class="bg-light">SERVICE</th>
                                            <th class="bg-light">STATUS</th>
                                            <th class="bg-light">PAYMENT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders ?? [] as $row)
                                            @php
                                                $status_color = ($row->order_status == 'Pending') ? 'primary' : (($row->order_status == 'Ready') ? 'info' : 'warning');
                                                $p_status = trim($row->payment_status);
                                                $pay_badge = ($p_status == 'Paid') ? 'bg-success' : (($p_status == 'Pending Verification') ? 'bg-warning text-dark' : 'bg-danger');
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold">{{ $row->fullname }}</div>
                                                    <small class="text-muted">#{{ $row->ref_number }}</small>
                                                </td>
                                                <td>{{ $row->service_type }}</td>
                                                <td><span class="badge bg-{{ $status_color }}-subtle text-{{ $status_color }} rounded-pill">{{ $row->order_status }}</span></td>
                                                <td>
                                                    <span class="badge {{ $pay_badge }}">{{ $p_status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan='4' class='text-center p-4'>No transactions found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4">
                        <!-- Inventory Status Card -->
                        <div class="card border-0 shadow-sm rounded-4 h-100 mb-4 mb-xl-0">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-primary me-2"></i> Inventory Status</h6>
                                @if (isset($side_inv) && $side_inv->count() > 0)
                                    @foreach($side_inv as $item)
                                        @php
                                            $name_lower = strtolower($item->item_name);
                                            $threshold = $item->min_threshold;

                                            if (str_contains($name_lower, 'detergent') || str_contains($name_lower, 'softener') || str_contains($name_lower, 'downy')) {
                                                $threshold = 15;
                                            } elseif (str_contains($name_lower, 'lpg') || str_contains($name_lower, 'spray') || str_contains($name_lower, 'gas')) {
                                                $threshold = 2; // <--- Binago ko na ginawang 2 para hindi na pumula kapag 5 ang stock
                                            }

                                            $is_low = ($item->stock_level <= $threshold);
                                            $bar_color = $is_low ? 'bg-danger' : 'bg-success';
                                        @endphp
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="fw-semibold">{{ $item->item_name }}</small>
                                                <small class="{{ $is_low ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($item->stock_level, 0) }}</small>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar {{ $bar_color }}" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-2">
                                        <small class="text-muted">No inventory items tracked.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Order Modal -->
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

                        <!-- Supplies Used Section Added -->
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
</body>
</html>