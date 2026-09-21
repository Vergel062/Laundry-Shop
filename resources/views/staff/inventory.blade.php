<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory | LaundryCare {{ strtoupper($user_role) }}</title>
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
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 5px 15px; transition: 0.2s; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        #sidebar .nav-link i { margin-right: 10px; }
        .sticky-nav { position: sticky; top: 0; z-index: 999; background: white; border-bottom: 1px solid #e2e8f0; }
        .inventory-card { background: #ffffff; border-radius: 15px; border: 1px solid #eef2f7; overflow: hidden; }
        .status-badge { font-weight: 700; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
        .overlay { display: none; position: fixed; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; }
        
        @media (max-width: 768px) { 
            #sidebar { position: fixed; left: -260px; } 
            #sidebar.active { left: 0; } 
            .overlay.active { display: block; } 
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <nav id="sidebar" class="shadow">
        <div class="p-4 text-center position-relative">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-droplet-half text-info"></i> Staff
                <span class="small text-muted" style="font-size: 10px;">{{ strtoupper($user_role) }}</span>
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
            <li class="nav-item"><a href="{{ route('staff.inventory') }}" class="nav-link active"><i class="bi bi-box-seam"></i> Inventory</a></li>
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
            <button class="btn btn-light d-md-none me-2" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
            <span class="navbar-text fw-semibold">
                Branch: <span class="text-primary text-uppercase">{{ $branch_name }}</span>
            </span>
        </nav>

        <div class="container-fluid p-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-12">
                    <h3 class="fw-bold mb-1">Stock Items Inventory</h3>
                    <p class="text-muted small">Viewing mode for inventory levels.</p>
                </div>
            </div>

            <div class="inventory-card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th class="ps-4 py-3">ITEM NAME</th>
                                <th>REMAINING STOCK</th>
                                <th>UNIT</th>
                                <th>STATUS</th>
                                <th class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $row)
                                @php
                                    $item_name = strtolower($row->item_name);
                                    $status_threshold = (int) $row->min_threshold;
                                    if (str_contains($item_name, 'detergent') || str_contains($item_name, 'softener') || str_contains($item_name, 'downy')) {
                                        $status_threshold = 15;
                                    } elseif (str_contains($item_name, 'spray')) {
                                        $status_threshold = 5;
                                    } elseif (str_contains($item_name, 'lpg') || str_contains($item_name, 'gas')) {
                                        $status_threshold = 3;
                                    }
                                    $is_low = $row->stock_level <= $status_threshold;
                                    $status_class = $is_low ? 'danger' : 'success';
                                    $can_manual_deduct = str_contains($item_name, 'spray')
                                        || str_contains($item_name, 'lpg')
                                        || str_contains($item_name, 'gas');
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $row->item_name }}</td>
                                    <td><span class="fw-bold fs-5 {{ $is_low ? 'text-danger' : 'text-primary' }}">{{ number_format($row->stock_level, 0) }}</span></td>
                                    <td><small class="text-muted fw-bold">{{ $row->unit }}</small></td>
                                    <td>
                                        <span class="status-badge bg-{{ $status_class }}-subtle text-{{ $status_class }}">
                                            {{ $is_low ? 'Low / Out' : 'Available' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($can_manual_deduct)
                                            <form action="{{ route('staff.inventory.deduct') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="inventory_id" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-outline-danger btn-sm fw-bold" {{ (int) $row->stock_level < 1 ? 'disabled' : '' }}>
                                                    <i class="bi bi-dash-circle me-1"></i> Deduct 1
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Auto</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No inventory items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
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

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Inventory Updated',
                text: @json(session('success')),
                timer: 1800,
                showConfirmButton: false
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Unable to Update Inventory',
                text: @json(session('error')),
                timer: 2200,
                showConfirmButton: false
            });
        @endif
    </script>
</body>
</html>