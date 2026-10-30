<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Inventory | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .rounded-15 { border-radius: 15px; }
        .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 20px; font-weight: 600; }
        .low-stock-row { background-color: rgba(220, 53, 69, 0.05); }
        .action-btn { padding: 4px 8px; border-radius: 8px; transition: 0.2s; }
        .action-btn:hover { background: #e2e8f0; color: #0d6efd; }
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
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link active"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.financial.reports', 'admin.utility.tracking']) ? 'show' : '' }}" id="reportsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item">
                            <a href="{{ route('admin.financial.reports', ['branch_id' => $selected_branch ?? 'all']) }}" 
                               class="nav-link {{ Route::currentRouteName() == 'admin.financial.reports' ? 'active' : '' }}"
                               style="white-space: nowrap;">
                                <i class="bi bi-graph-up-arrow"></i> Financial Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.utility.tracking') }}" 
                               class="nav-link {{ Route::currentRouteName() == 'admin.utility.tracking' ? 'active' : '' }}"
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
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.branch.management', 'admin.manage.users']) ? 'show' : '' }}" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.branch.management') }}" class="nav-link {{ Route::currentRouteName() == 'admin.branch.management' ? 'active' : '' }}">
                                <i class="bi bi-shop"></i> Branch Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.manage.users') }}" class="nav-link {{ Route::currentRouteName() == 'admin.manage.users' ? 'active' : '' }}">
                                <i class="bi bi-person-plus"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
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
                <h5 class="fw-bold mb-0">Branch Inventory</h5>
                <div class="ms-auto">
                    <select class="form-select form-select-sm d-inline-block" style="width: 220px;" onchange="location.href='{{ route('admin.inventory') }}?branch_id='+this.value">
                        <option value="all">All Branches</option>
                        @foreach($branches_dropdown as $b)
                            <option value="{{ $b->id }}" {{ $selected_branch == $b->id ? 'selected' : '' }}>{{ htmlspecialchars($b->branch_name) }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.inventory.history', ['branch_id' => $selected_branch]) }}" class="btn btn-outline-primary btn-sm ms-2">
                        <i class="bi bi-clock-history me-1"></i> Inventory History
                    </a>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="mb-3 d-flex justify-content-between align-items-center gap-3">
                <p class="text-muted small"><i class="bi bi-info-circle"></i> Items marked as Low Stock are automatically prioritized at the top.</p>
                <div class="d-flex gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#editInventoryItemModal">
                        <i class="bi bi-pencil-square me-1"></i> Edit Items
                    </button>
                    <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addInventoryItemModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Items
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-15 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-4">Branch</th>
                                <th>Item Description</th>
                                <th>Current Stock</th>
                                <th>Unit Type</th>
                                <th>Min. Threshold</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $item)
                                @php
                                    $unit_display = $item->unit;
                                    $status_threshold = (int) $item->min_threshold;
                                    $is_low = $item->stock_level <= $status_threshold;
                                @endphp
                                <tr class="{{ $is_low ? 'low-stock-row' : '' }}">
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark fw-normal border">{{ htmlspecialchars($item->branch_name) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ htmlspecialchars($item->item_name) }}</div>
                                        @if($is_low)<div class="text-danger fw-bold" style="font-size: 10px;">REPLENISH REQUIRED</div>@endif
                                    </td>
                                    <td>
                                        <span class="fw-bold fs-5 {{ $is_low ? 'text-danger' : 'text-primary' }}">
                                            {{ number_format($item->stock_level, 0) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ htmlspecialchars($unit_display) }}</td>
                                    <td class="small text-muted">{{ number_format($status_threshold, 0) }}</td>
                                    <td>
                                        @if($is_low)
                                            <span class="status-badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> LOW STOCK
                                            </span>
                                        @else
                                            <span class="status-badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                <i class="bi bi-check-circle-fill me-1"></i> IN STOCK
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <button class="btn btn-link text-primary action-btn" 
                                                onclick="openUpdateModal({{ $item->id }}, '{{ htmlspecialchars($item->item_name) }}', '{{ htmlspecialchars($item->branch_name) }}', {{ $item->stock_level }}, '{{ $unit_display }}')">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5 text-muted">No inventory items found for the selected branch.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editInventoryItemModal" tabindex="-1" aria-labelledby="editInventoryItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-15">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold mb-1" id="editInventoryItemLabel">Edit Inventory Item</h5>
                    <p class="text-muted small mb-0">Change the item's name or minimum stock threshold.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.inventory.items.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_branch_id" value="{{ $selected_branch }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editInventorySelect" class="form-label small fw-bold">Select Item</label>
                        <select name="item_id" id="editInventorySelect" class="form-select @error('item_id') is-invalid @enderror" required>
                            <option value="">Select an item</option>
                            @foreach($inventory_items as $inventoryItem)
                                <option
                                    value="{{ $inventoryItem->id }}"
                                    data-item-name="{{ $inventoryItem->item_name }}"
                                    data-threshold="{{ $inventoryItem->min_threshold }}"
                                    @selected((string) old('item_id') === (string) $inventoryItem->id)
                                >
                                    {{ $inventoryItem->branch_name }} — {{ $inventoryItem->item_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="editInventoryName" class="form-label small fw-bold">Item Name</label>
                        <input type="text" name="item_name" id="editInventoryName" class="form-control @error('item_name') is-invalid @enderror" value="{{ old('item_name') }}" maxlength="100" required>
                        @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="editInventoryThreshold" class="form-label small fw-bold">Minimum Stock Threshold</label>
                        <input type="number" name="min_threshold" id="editInventoryThreshold" class="form-control @error('min_threshold') is-invalid @enderror" value="{{ old('min_threshold') }}" min="0" step="1" required>
                        @error('min_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Items at or below this quantity will be marked Low Stock.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-8 fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-8 fw-bold px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addInventoryItemModal" tabindex="-1" aria-labelledby="addInventoryItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-15">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold mb-1" id="addInventoryItemLabel">Add Inventory Item</h5>
                    <p class="text-muted small mb-0">Add a new item to one branch's inventory.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.inventory.store') }}" method="POST">
                @csrf
                <input type="hidden" name="return_branch_id" value="{{ $selected_branch }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="newItemBranch" class="form-label small fw-bold">Branch</label>
                        <select name="branch_id" id="newItemBranch" class="form-select @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select a branch</option>
                            @foreach($branches_dropdown as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $selected_branch !== 'all' ? $selected_branch : '') == $branch->id)>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="newItemName" class="form-label small fw-bold">Item Name</label>
                        <input type="text" name="item_name" id="newItemName" class="form-control @error('item_name') is-invalid @enderror" value="{{ old('item_name') }}" maxlength="100" required>
                        @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="newItemStock" class="form-label small fw-bold">Starting Stock</label>
                            <input type="number" name="stock_level" id="newItemStock" class="form-control @error('stock_level') is-invalid @enderror" value="{{ old('stock_level', 0) }}" min="0" step="1" required>
                            @error('stock_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="newItemUnit" class="form-label small fw-bold">Unit Type</label>
                            <select name="unit" id="newItemUnit" class="form-select @error('unit') is-invalid @enderror" required>
                                <option value="">Select unit</option>
                                @foreach(['Sachets', 'Gallons', 'Tanks', 'Pieces'] as $unit)
                                    <option value="{{ $unit }}" @selected(old('unit') === $unit)>{{ $unit }}</option>
                                @endforeach
                            </select>
                            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="newItemThreshold" class="form-label small fw-bold">Minimum Stock Threshold</label>
                        <input type="number" name="min_threshold" id="newItemThreshold" class="form-control @error('min_threshold') is-invalid @enderror" value="{{ old('min_threshold', 2) }}" min="0" step="1" required>
                        @error('min_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-8 fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-8 fw-bold px-4 shadow-sm">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="updateStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-15">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Update Inventory Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.inventory.update') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="item_id" id="modal_item_id">
                    <input type="hidden" name="branch_id" value="{{ $selected_branch }}">
                    
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold d-block mb-1">Item Details</small>
                        <p class="fw-bold mb-0" id="modal_display_name"></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-2">Current Count</label>
                            <input type="text" id="modal_current_stock" class="form-control bg-light border-0 fw-bold" readonly>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-2">Unit</label>
                            <input type="text" id="modal_unit" class="form-control bg-light border-0" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-primary mb-2">New Remaining Balance (Actual Pieces)</label>
                        <input type="number" name="new_stock" class="form-control form-control-lg border-primary shadow-none" placeholder="Enter whole number" required min="0" step="1">
                        <small class="text-muted" style="font-size: 11px;">Set the total physical quantity currently present in the branch.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-8 fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_stock" class="btn btn-primary rounded-8 fw-bold px-4 shadow-sm">Confirm Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editInventorySelect = document.getElementById('editInventorySelect');
    const editInventoryName = document.getElementById('editInventoryName');
    const editInventoryThreshold = document.getElementById('editInventoryThreshold');

    editInventorySelect?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        editInventoryName.value = selectedOption.dataset.itemName || '';
        editInventoryThreshold.value = selectedOption.dataset.threshold || '';
    });

    @if($errors->has('item_name') && old('item_id'))
        editInventoryName.value = @js(old('item_name'));
    @endif
    @if($errors->has('min_threshold') && old('item_id'))
        editInventoryThreshold.value = @js(old('min_threshold'));
    @endif

    document.addEventListener('DOMContentLoaded', function () {
        // Sidebar Toggle Logic for Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() {
            sidebar?.classList.add('active');
            overlay?.classList.add('active');
        }

        function closeSidebarMenu() {
            sidebar?.classList.remove('active');
            overlay?.classList.remove('active');
        }

        sidebarToggle?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarMenu);
        overlay?.addEventListener('click', closeSidebarMenu);
    });

    function openUpdateModal(id, name, branch, stock, unit) {
        document.getElementById('modal_item_id').value = id;
        document.getElementById('modal_display_name').innerText = name + ' (' + branch + ')';
        document.getElementById('modal_current_stock').value = stock;
        document.getElementById('modal_unit').value = unit;
        
        const myModal = new bootstrap.Modal(document.getElementById('updateStockModal'));
        myModal.show();
    }

    // Success Alert
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status') && urlParams.get('status') === 'updated') {
        Swal.fire({
            icon: 'success',
            title: 'Inventory Updated',
            text: 'Stock balance has been successfully recorded.',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, '', "{{ route('admin.inventory', ['branch_id' => $selected_branch]) }}");
    }
    if (urlParams.has('status') && urlParams.get('status') === 'added') {
        Swal.fire({
            icon: 'success',
            title: 'Inventory Item Added',
            text: 'The new item has been added to the selected branch.',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, '', "{{ route('admin.inventory', ['branch_id' => $selected_branch]) }}");
    }
    if (urlParams.has('status') && urlParams.get('status') === 'item-updated') {
        Swal.fire({
            icon: 'success',
            title: 'Inventory Item Updated',
            text: 'The item name and minimum threshold have been updated.',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, '', "{{ route('admin.inventory', ['branch_id' => $selected_branch]) }}");
    }

    @if($errors->has('branch_id') || $errors->has('stock_level') || $errors->has('unit') || (!old('item_id') && ($errors->has('item_name') || $errors->has('min_threshold'))))
        new bootstrap.Modal(document.getElementById('addInventoryItemModal')).show();
    @endif
    @if($errors->has('item_id') || (old('item_id') && ($errors->has('item_name') || $errors->has('min_threshold'))))
        new bootstrap.Modal(document.getElementById('editInventoryItemModal')).show();
    @endif

    document.querySelector('.logout-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('logoutForm');

        Swal.fire({
            title: 'Confirm Logout',
            text: "Are you sure you want to end your admin session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
</body>
</html>