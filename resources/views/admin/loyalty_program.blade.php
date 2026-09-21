<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loyalty Program | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .rounded-15 { border-radius: 15px !important; }
        .stat-card { transition: transform 0.2s; border: none; }
        .stat-card:hover { transform: translateY(-5px); }
        .swal2-html-container { overflow: hidden !important; }
        .rounded-8 { border-radius: 8px !important; }
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
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i>Transaction History</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ (request()->routeIs('admin.financial.reports') || request()->routeIs('admin.utility.tracking')) ? 'show' : '' }}" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item">
                            <a href="{{ route('admin.financial.reports') }}" 
                            class="nav-link {{ request()->routeIs('admin.financial.reports') ? 'active' : '' }}"
                            style="white-space: nowrap;"> <i class="bi bi-graph-up-arrow"></i> Financial Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.utility.tracking') }}" 
                            class="nav-link {{ request()->routeIs('admin.utility.tracking') ? 'active' : '' }}"
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
                <div class="collapse {{ (request()->routeIs('admin.branch.management') || request()->routeIs('admin.manage.users')) ? 'show' : '' }}" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.branch.management') }}" class="nav-link {{ request()->routeIs('admin.branch.management') ? 'active' : '' }}">
                                <i class="bi bi-shop"></i> Branch Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.manage.users') }}" class="nav-link {{ request()->routeIs('admin.manage.users') ? 'active' : '' }}">
                                <i class="bi bi-person-plus"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link active"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2">
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 bg-white border-bottom">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Loyalty Program</h5>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-info border-5 h-100">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Total Points Awarded</small>
                        <h3 class="fw-bold text-info mt-1 mb-0">{{ number_format($total_points_awarded) }} pts</h3>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card bg-white p-4 shadow-sm border-start border-warning border-5 h-100">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Total Active Members</small>
                        <h3 class="fw-bold text-warning mt-1 mb-0">{{ $total_active_members }}</h3>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 mb-4 rounded-15">
                <form method="GET" action="{{ route('admin.loyalty.program') }}" class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0 shadow-none" placeholder="Search by name, email or referral code..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm rounded-8">Search</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-15 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0">Customer Rewards Ledger</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">Customer</th>
                                <th>Referral Code</th>
                                <th>Points Balance</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $row)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $row->fullname }}</div>
                                    <div class="text-muted small">{{ $row->email }}</div>
                                </td>
                                <td><code class="fw-bold text-info">{{ $row->referral_code ?: 'N/A' }}</code></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-8">
                                        <i class="bi bi-star-fill me-1"></i> {{ number_format($row->balance) }} pts
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary fw-bold rounded-8" onclick="managePoints({{ $row->id }}, '{{ addslashes($row->fullname) }}')">
                                        Adjust Points
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No customers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('closeSidebar');

    function toggleMenu() {
        if(sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }

    toggleBtn?.addEventListener('click', toggleMenu);
    closeBtn?.addEventListener('click', toggleMenu);
    overlay?.addEventListener('click', toggleMenu);

    // Logout Confirmation
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Logout',
                text: "Are you sure you want to log out?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Logout!',
                customClass: { popup: 'rounded-15' }
            }).then((result) => {
                if (result.isConfirmed) { 
                    document.getElementById('logoutForm').submit(); 
                }
            });
        });
    }
});

// Manage Points Modal
function managePoints(userId, name) {
    Swal.fire({
        title: `<h5 class="fw-bold mb-0">Adjust Points: ${name}</h5>`,
        html: `
            <div class="text-start mt-3">
                <label class="small fw-bold text-muted mb-2 text-uppercase">Action Type</label>
                <select id="pointAction" class="form-select shadow-none mb-3 rounded-8">
                    <option value="add">Add Points (Earned)</option>
                    <option value="redeem">Redeem Points (Deduct)</option>
                </select>
                
                <label class="small fw-bold text-muted mb-2 text-uppercase">Amount</label>
                <input type="number" id="pointAmount" class="form-control shadow-none mb-3 rounded-8" placeholder="Enter pts">
                
                <label class="small fw-bold text-muted mb-2 text-uppercase">Point Source</label>
                <select id="pointSource" class="form-select shadow-none rounded-8">
                    <option value="Admin Reward">Admin Reward</option>
                    <option value="Referral Bonus">Referral Bonus</option>
                    <option value="Transaction">Transaction</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        confirmButtonColor: '#0d6efd',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-15 border-0 shadow-lg',
            confirmButton: 'btn btn-primary px-4 rounded-8 fw-bold',
            cancelButton: 'btn btn-light px-4 rounded-8'
        },
        preConfirm: () => {
            const amount = document.getElementById('pointAmount').value;
            if(!amount || amount <= 0) {
                Swal.showValidationMessage('Please enter a valid amount');
                return false;
            }
            return {
                user_id: userId,
                action: document.getElementById('pointAction').value,
                amount: amount,
                source: document.getElementById('pointSource').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('user_id', result.value.user_id);
            formData.append('action', result.value.action);
            formData.append('amount', result.value.amount);
            formData.append('source', result.value.source);

            fetch("{{ route('admin.loyalty.process') }}", { 
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData 
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, confirmButtonColor: '#0d6efd' }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}
</script>
</body>
</html>