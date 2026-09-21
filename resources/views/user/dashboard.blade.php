<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/User.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="shadow-sm">
            <div class="p-4 text-center position-relative d-flex align-items-center justify-content-center">
                <h4 class="fw-bold text-primary mb-0">
                    <i class="bi bi-water"></i> L-Care
                </h4>
                <button type="button" class="btn text-dark d-md-none p-0 border-0 position-absolute" style="right: 20px;" id="closeSidebar">
                    <i class="bi bi-x-lg fs-4"></i>
                </button>
            </div>
           <div class="px-4 mb-4 text-center">
                @php
                    $firstLetter = mb_substr($userData->fullname ?? 'U', 0, 1);
                    $hasPic = !empty($userData->profile_pic) && \Illuminate\Support\Facades\Storage::disk('public')->exists($userData->profile_pic);
                @endphp

                <img src="{{ $hasPic ? asset('storage/' . $userData->profile_pic) : 'https://ui-avatars.com/api/?name=' . urlencode($firstLetter) . '&length=1&background=0ea5e9&color=fff' }}" 
                    class="rounded-circle mb-2 shadow-sm" width="70" height="70" style="object-fit: cover;">
                    
                <h6 class="fw-bold mb-0">{{ $userData->fullname }}</h6>
                <small class="text-muted">Regular Member</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item"><a href="{{ route('user.dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2"></i> Overview</a></li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal">
                        <i class="bi bi-credit-card-2-back"></i> Payment History
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#historyModal">
                        <i class="bi bi-clock-history"></i> My Laundry History
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#rewardsModal">
                        <i class="bi bi-gift"></i> Rewards & Points
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                <hr class="mx-3">
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-left"></i> Sign Out
                    </button>
                </form>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-2 d-md-none">
                <button type="button" id="sidebarCollapse" class="btn btn-light border"><i class="bi bi-list"></i></button>
                <span class="ms-3 fw-bold">My Dashboard</span>
            </nav>

            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="fw-bold">Welcome back, {{ $firstName }}!</h3>
                            <p class="text-muted small mb-0">Here's what's happening with your laundry today.</p>
                        </div>
                        <div class="dropdown ms-3">
                            <button class="btn btn-light rounded-circle position-relative shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open notifications">
                                <i class="bi bi-bell fs-5 text-primary"></i>
                                @if($readyOrders->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $readyOrders->count() }}
                                    </span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0 notification-menu">
                                <div class="px-3 py-2 border-bottom fw-bold">Laundry Notifications</div>
                                @forelse($readyOrders as $readyOrder)
                                    <button type="button" class="dropdown-item py-3 text-wrap text-start" data-bs-toggle="modal" data-bs-target="#readyOrderModal{{ $readyOrder->id }}">
                                        <div class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i> Ready for pickup</div>
                                        <small class="text-muted">Ref #{{ $readyOrder->ref_number }} - Please claim your laundry.</small>
                                    </button>
                                @empty
                                    <div class="px-3 py-3 text-muted small">No new laundry notifications.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card glass-card p-4 mb-4 border-start border-primary border-5" id="active-order">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Active Order Status</h5>
                                @if($activeOrder)
                                    <span class="badge bg-primary rounded-pill">{{ $activeOrder->order_status }}</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">No Active Order</span>
                                @endif
                            </div>

                            @if($activeOrder)
                                <div class="mb-4 mt-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-bold text-primary">Laundry Progress</small>
                                        <small class="fw-bold">{{ $progressPercent }}%</small>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <span class="{{ ($progressPercent >= 25) ? 'text-primary fw-bold' : '' }}">Pending</span>
                                        <span class="{{ ($progressPercent >= 50) ? 'text-primary fw-bold' : '' }}">Washing</span>
                                        <span class="{{ ($progressPercent >= 75) ? 'text-primary fw-bold' : '' }}">Drying</span>
                                        <span class="{{ ($progressPercent >= 100) ? 'text-success fw-bold' : '' }}">Ready</span>
                                    </div>
                                </div>
                                <div class="row g-2 text-center bg-light rounded-3 p-3 mb-3">
                                    <div class="col-4 border-end"><small class="d-block text-muted">Ref #</small><b>{{ $activeOrder->ref_number }}</b></div>
                                    <div class="col-4 border-end"><small class="d-block text-muted">Weight</small><b>{{ $activeOrder->weight_kg }}kg</b></div>
                                    <div class="col-4"><small class="d-block text-muted">Total</small><b>₱{{ number_format($activeOrder->total_amount, 2) }}</b></div>
                                </div>

                                @if($activeOrder->order_status == 'Ready')
                                    <div class="mt-3">
                                        @if($activeOrder->payment_status == 'Unpaid')
                                            <button type="button" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                                <i class="bi bi-credit-card-2-front me-2"></i> PAY NOW TO CLAIM
                                            </button>
                                        @elseif($activeOrder->payment_status == 'Pending Verification')
                                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-0">
                                                <i class="bi bi-clock-history fs-4 me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Payment Pending</h6>
                                                    <small>Please wait for the staff to verify your payment.</small>
                                                </div>
                                            </div>
                                        @elseif($activeOrder->payment_status == 'Paid')
                                            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-0">
                                                <i class="bi bi-qr-code-scan fs-4 me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Payment Verified</h6>
                                                    <small>You can now go to the shop to pick up your laundry.</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <p class="text-muted text-center my-3">You don't have any laundry being processed right now.</p>
                            @endif
                        </div>

                        <!-- Recent Activity Table -->
                        <div class="card glass-card border-0 p-4 mb-4">
                            <h5 class="fw-bold mb-3">Recent Activity</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="text-muted small">
                                        <tr>
                                            <th>DATE</th>
                                            <th>REF</th>
                                            <th>STATUS</th>
                                            <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @php $count = 0; @endphp
                                        @foreach($transactions as $row)
                                            @if($count >= 5) @break @endif
                                            <tr>
                                                <td>{{ date('M d, Y', strtotime($row->created_at)) }}</td>
                                                <td>#{{ $row->ref_number }}</td>
                                                <td>
                                                    <span class="badge {{ ($row->order_status == 'Claimed') ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                                        {{ $row->order_status }}
                                                    </span>
                                                </td>
                                                <td>₱{{ number_format($row->total_amount, 2) }}</td>
                                            </tr>
                                            @php $count++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <button type="button" class="btn btn-primary w-100 fw-bold py-3 mb-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#requestServiceModal">
                            <i class="bi bi-plus-circle me-2"></i> Request New Service
                        </button>

                        <div class="card loyalty-balance p-4 mb-4 shadow">
                            <h6 class="small opacity-75">Loyalty Points Balance</h6>
                            <h1 class="fw-bold">{{ number_format($currentPoints) }} <small style="font-size: 1rem;">pts</small></h1>
                            <hr class="opacity-25">
                            <p class="small mb-0"><i class="bi bi-gift me-2"></i> Earn rewards now!</p>
                        </div>

                        <div class="card glass-card p-4">
                            <h6 class="fw-bold mb-3">Monthly Kilo Tracker</h6>
                            <div class="text-center py-2">
                                <h3 class="fw-bold text-primary mb-0">{{ number_format($monthlyKilos, 1) }} kg</h3>
                                <p class="text-muted small">Processed in {{ date('F') }}</p>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: {{ min(($monthlyKilos/50)*100, 100) }}%"></div>
                            </div>
                            @if(($pendingServiceRequests ?? collect())->isNotEmpty())
                                <div class="mt-3">
                                    <small class="text-muted fw-bold">Pending Requests</small>
                                    @foreach($pendingServiceRequests as $request)
                                        <div class="d-flex justify-content-between align-items-center border rounded-3 p-2 mt-2">
                                            <div>
                                                <div class="small fw-bold">{{ $request->service_type }} · {{ number_format($request->weight_kg, 2) }} kg</div>
                                                <small class="text-muted">{{ $request->ref_number }}</small>
                                            </div>
                                            <form method="POST" action="{{ route('user.service.cancel', $request->id) }}" class="cancel-service-request-form">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-basket2 text-primary me-2"></i>Request New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.service.request') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Send a request to notify the Staff and Manager of your registered branch that you want a new laundry service. They will confirm the weight, service type, and supplies used with you.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold">Send Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($readyOrders as $readyOrder)
        <div class="modal fade" id="readyOrderModal{{ $readyOrder->id }}" tabindex="-1" aria-labelledby="readyOrderModalLabel{{ $readyOrder->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="readyOrderModalLabel{{ $readyOrder->id }}">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>Ready for pickup
                            </h5>
                            <small class="text-muted">Your laundry is ready to be claimed.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-success border-0 d-flex align-items-center mb-4">
                            <i class="bi bi-shop fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold">Please claim your laundry.</div>
                                <small>Present your reference number at the branch.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Reference Number</small>
                                <span class="fw-bold">#{{ $readyOrder->ref_number }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Date</small>
                                <span class="fw-bold">{{ date('M d, Y', strtotime($readyOrder->created_at)) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Service</small>
                                <span class="fw-bold">{{ $readyOrder->service_type }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Weight</small>
                                <span class="fw-bold">{{ number_format($readyOrder->weight_kg, 2) }} kg</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Total Amount</small>
                                <span class="fw-bold text-primary">₱{{ number_format($readyOrder->total_amount, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Payment Status</small>
                                <span class="badge {{ $readyOrder->payment_status === 'Paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $readyOrder->payment_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; max-height: 90vh;">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-credit-card me-2 text-primary"></i> Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="text-muted small bg-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Payment Ref</th>
                                    <th>Method</th>
                                    <th>Proof</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($transactions as $h)
                                    @if(!empty($h->payment_reference))
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $h->payment_reference }}</span><br>
                                            <small class="text-muted">{{ date('M d, Y', strtotime($h->date_paid ?? $h->created_at)) }}</small>
                                        </td>
                                        <td>{{ $h->payment_method ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($h->proof_of_payment))
                                                @php
                                                    $proofPath = ltrim($h->proof_of_payment, '/');
                                                    $proofUrl = filter_var($proofPath, FILTER_VALIDATE_URL)
                                                        ? $proofPath
                                                        : asset(str_starts_with($proofPath, 'uploads/') ? $proofPath : 'uploads/payments/' . $proofPath);
                                                @endphp
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="viewProof(@js($proofUrl))">
                                                    <i class="bi bi-image"></i> View
                                                </button>
                                            @else
                                                <span class="text-muted small">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($h->payment_status == 'Paid')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($h->payment_status == 'Pending Verification')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $h->payment_status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Online Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" action="{{ route('payment.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body px-4 pb-4">
                        @if($activeOrder)
                        <input type="hidden" name="transaction_id" value="{{ $activeOrder->id }}">
                        <input type="hidden" name="ref_number" value="{{ $activeOrder->ref_number }}">
                        
                        <div id="payment_step1">
                            <div class="text-center mb-4">
                                <p class="text-muted mb-1 small">Total Amount to Pay</p>
                                <h2 class="fw-bold text-primary">₱{{ number_format($activeOrder->total_amount, 2) }}</h2>
                            </div>

                            <label class="form-label small fw-bold">1. Select Method:</label>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="method" id="pay_gcash" value="GCash" checked>
                                    <label class="btn btn-outline-primary w-100 py-3" for="pay_gcash">GCash</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="method" id="pay_maya" value="PayMaya">
                                    <label class="btn btn-outline-info w-100 py-3" for="pay_maya">PayMaya</label>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary w-100 fw-bold py-2 shadow" onclick="goToStep2()">PROCEED</button>
                        </div>

                        <div id="payment_step2" class="d-none">
                            <div class="qr-container text-center mb-3" style="background: #f8f9fa; padding: 15px; border-radius: 15px;">
                                <img id="qr_image" src="{{ asset('Gcash/gcash_qr.jpg') }}" class="img-fluid rounded border mb-3" style="max-height: 200px;">
                                <div class="p-2 bg-white rounded border mb-2">
                                    <small class="text-muted d-block small">L-CARE:</small>
                                    <h5 class="fw-bold text-dark mb-0" id="admin_num">09098256981</h5>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">2. Reference Number (13 Digits)</label>
                                <input type="text" name="payment_ref" id="payment_ref" class="form-control form-control-sm fw-bold" placeholder="Enter 13-digit number" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                                <small id="ref_count" class="text-muted" style="font-size: 0.7rem;">Characters: 0/13</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">3. Upload Screenshot</label>
                                <input type="file" name="proof_of_payment" class="form-control form-control-sm" accept="image/*" required>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light border btn-sm w-50" onclick="goToStep1()">Back</button>
                                <button type="submit" id="submitPayment" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Submit Payment</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Laundry History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i> Laundry History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top" style="top: -1px; z-index: 1;">
                                <tr class="small text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>DATE</th>
                                    <th>SERVICE</th>
                                    <th>WEIGHT</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 13px;">
                                @forelse($historyTransactions as $h)
                                    <tr>
                                        <td class="text-muted">{{ date('M d, Y', strtotime($h->created_at)) }}</td>
                                        <td class="fw-bold text-dark">{{ $h->service_type }}</td>
                                        <td>{{ $h->weight_kg }} kg</td>
                                        <td class="fw-bold text-primary">₱{{ number_format($h->total_amount, 2) }}</td>
                                        <td>
                                            @if($h->order_status == 'Claimed')
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill">Claimed</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 rounded-pill">Cancelled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-folder2-open d-block fs-1 text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No past transactions found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary w-100 rounded-3 fw-bold py-2" data-bs-dismiss="modal">Close History</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Modal -->
    <div class="modal fade" id="rewardsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="fw-bold mb-0 text-dark">
                        <span class="bi bi-star-fill text-warning me-2"></span> My Rewards
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="p-4 mb-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); color: white;">
                        <p class="small mb-1 opacity-75 text-uppercase fw-bold" style="letter-spacing: 1px;">Available Balance</p>
                        <h1 class="display-4 fw-bold mb-0">{{ number_format($currentPoints) }}</h1>
                        <p class="mb-0 fw-medium">Loyalty Points</p>
                    </div>

                    <div class="bg-light p-3 rounded-4 border">
                        <h6 class="fw-bold text-dark mb-2">Share & Earn More!</h6>
                        <p class="text-muted small mb-3">Get 10 points for every new customer who uses your code.</p>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control text-center fw-bold bg-white" id="referralCode" value="{{ $userData->referral_code }}" readonly>
                            <button type="button" class="btn btn-primary" onclick="copyReferral()"><i class="bi bi-copy"></i></button>
                        </div>
                        <small class="text-primary fw-bold" id="copyMsg" style="display:none;">Code Copied!</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
  <!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-gear text-primary me-2"></i> Account Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
        
                <!-- ================= 1. FORM PARA SA EDIT PROFILE INFO ================= -->
                <form action="{{ route('user.update.profile') }}" method="POST" enctype="multipart/form-data" id="profileUpdateForm">
                    @csrf

                    <!-- Profile Picture Preview & Upload -->
                    <div class="text-center mb-4">
                        @php
                            $firstLetter = mb_substr($userData->fullname ?? 'U', 0, 1);
                            $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($firstLetter) . '&length=1&background=0ea5e9&color=fff';
                            
                            $hasPic = !empty($userData->profile_pic) && \Illuminate\Support\Facades\Storage::disk('public')->exists($userData->profile_pic);
                            $avatarUrl = $hasPic ? asset('storage/' . $userData->profile_pic) : $defaultAvatar;
                        @endphp

                        <div class="d-inline-block position-relative mb-2">
                            <img src="{{ $avatarUrl }}" 
                                 onerror="this.onerror=null; this.src='{{ $defaultAvatar }}';"
                                 class="rounded-circle shadow-sm border" 
                                 width="95" 
                                 height="95" 
                                 style="object-fit: cover;" 
                                 id="profilePreview">
                        </div>

                        <div class="mt-2">
                            <label for="profilePicInput" class="form-label small fw-bold text-muted d-block mb-1">Change Profile Picture</label>
                            <input type="file" id="profilePicInput" name="profile_pic" class="form-control form-control-sm w-75 mx-auto @error('profile_pic') is-invalid @enderror" accept="image/*">
                            @error('profile_pic')
                                <div class="invalid-feedback text-center d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">Full Name</label>
                            <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname', $userData->fullname) }}">
                            @error('fullname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $userData->phone ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Gmail / Email -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Gmail / Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $userData->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 px-4 fw-bold">Save Profile Changes</button>
                    </div>
                </form>


                <!-- Divider -->
                <hr class="my-4 text-muted">


                <!-- ================= 2. FORM PARA SA CHANGE PASSWORD ================= -->
                <form action="{{ route('user.update-password') }}" method="POST" id="passwordUpdateForm">
                    @csrf

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock text-info"></i> Change Password</h6>
                        <p class="text-muted small mb-3">Fill out these fields only if you want to update your password.</p>
                    </div>

                    <div class="row g-3">
                        <!-- Current Password -->
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Current Password</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password">
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="newPassword" placeholder="Min. 6 characters">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" id="confirmPassword" placeholder="Re-type new password">
                        </div>
                    </div>

                    <!-- Password Match Alert (Client-side JS) -->
                    <div id="passwordAlert" class="text-danger small mt-2" style="display: none;">
                        <i class="bi bi-exclamation-circle"></i> New passwords do not match!
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" id="savePasswordBtn" class="btn btn-dark rounded-3 px-4 fw-bold btn-sm">Update Password</button>
                    </div>
                </form>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODAL & SWEETALERT SCRIPTS ================= -->

{{-- 1. KAPAG MAY VALIDATION ERRORS: Buksan ang modal AT huwag magpakita ng Success alert --}}
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalElement = document.getElementById('settingsModal'); 
        if (modalElement) {
            var myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        }
    });
</script>
@endif

{{-- 2. KAPAG MAY SUCCESS SESSION AT WALANG ERRORS: Lalabas ang SweetAlert --}}
@if (session('success') && !$errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#0d6efd'
        });
    });
</script>
@endif

       
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/User_script.js') }}"></script>
    <script>
            document.querySelectorAll('.cancel-service-request-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Cancel service request?',
                        text: 'You can only cancel this request while it is still pending.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Yes, cancel it'
                    }).then(function (result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
    </script>
</body>
</html>