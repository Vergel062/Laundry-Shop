<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Management | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .rounded-15 { border-radius: 15px; }
        .rounded-8 { border-radius: 8px; }
        .archived-branches-btn {
            height: 34px;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding-top: 0;
            padding-bottom: 0;
        }
    </style>
</head>
<body class="bg-light">

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
                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Report</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="true">
                    <span><i class="bi bi-gear"></i> Settings</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link active"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-power"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
            <div class="d-flex align-items-center w-100">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold mb-0">Branch Management</h5>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.branch.archived') }}" class="archived-branches-btn btn btn-sm btn-outline-warning shadow-sm">
                        <i class="bi bi-archive me-1"></i> Archived Branches
                    </a>
                    <button class="btn btn-primary btn-sm rounded-8 px-3" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                        <i class="bi bi-plus-lg me-1"></i> Add New Branch
                    </button>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="card shadow-sm border-0 rounded-15 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4 py-3">ID</th>
                                    <th>Branch Name</th>
                                    <th>Location</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $row)
                                <tr>
                                    <td class="ps-4 text-muted small">#{{ $row->id }}</td>
                                    <td class="fw-bold">{{ $row->branch_name }}</td>
                                    <td><i class="bi bi-geo-alt text-muted me-1"></i>{{ $row->location }}</td>
                                    <td class="text-center">
                                        <button
                                            class="btn btn-outline-primary btn-sm rounded-8 me-1"
                                            data-branch-id="{{ $row->id }}"
                                            data-branch-name="{{ $row->branch_name }}"
                                            data-location="{{ $row->location }}"
                                            onclick="openEditModal(this)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm rounded-8" onclick="confirmDelete({{ $row->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted">No branches found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-15">
            <form action="{{ route('admin.branch.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 mt-2"><h5 class="modal-title fw-bold text-dark px-2"><i class="bi bi-plus-circle text-primary me-2"></i>Register New Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="mb-4"><label class="form-label fw-bold small text-muted text-uppercase">Branch Name</label><input type="text" name="branch_name" class="form-control bg-light border-0 py-2 rounded-8" required></div>
                    <div class="mb-2"><label class="form-label fw-bold small text-muted text-uppercase">Location / Full Address</label><input type="text" name="location" class="form-control bg-light border-0 py-2 rounded-8" required></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-light fw-bold text-muted px-4 rounded-8" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm rounded-8">Register Branch</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-15">
            <form action="{{ route('admin.branch.update') }}" method="POST">
                @csrf
                <div class="modal-header border-0 mt-2"><h5 class="modal-title fw-bold text-dark px-2"><i class="bi bi-pencil-square text-primary me-2"></i>Update Branch Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <input type="hidden" name="branch_id" id="edit_branch_id">
                    <div class="mb-4"><label class="form-label fw-bold small text-muted text-uppercase">Branch Name</label><input type="text" name="branch_name" id="edit_branch_name" class="form-control bg-light border-0 py-2 rounded-8" required></div>
                    <div class="mb-2"><label class="form-label fw-bold small text-muted text-uppercase">Location / Address</label><input type="text" name="location" id="edit_location" class="form-control bg-light border-0 py-2 rounded-8" required></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-light fw-bold text-muted px-4 rounded-8" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm rounded-8">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script> 
<script>
    function openEditModal(button) {
        document.getElementById('edit_branch_id').value = button.dataset.branchId;
        document.getElementById('edit_branch_name').value = button.dataset.branchName;
        document.getElementById('edit_location').value = button.dataset.location;
        new bootstrap.Modal(document.getElementById('editBranchModal')).show();
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Branch?',
            text: "The branch will be archived and automatically deleted permanently after 30 days.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete it!'
        }).then((result) => {
            if (result.isConfirmed) { 
                window.location.href = "{{ url('/admin/branch-management/archive') }}/" + id; 
            }
        });
    }

    @if(session('msg') === 'added')
        Swal.fire({ title: 'Success!', text: 'New branch added.', icon: 'success', timer: 1800, showConfirmButton: false });
    @elseif(session('msg') === 'updated')
        Swal.fire({ title: 'Updated!', text: 'Branch updated successfully.', icon: 'success', timer: 1800, showConfirmButton: false });
    @elseif(session('msg') === 'archived')
        Swal.fire({ title: 'Archived!', text: 'Branch moved to archive and will be deleted after 30 days.', icon: 'warning', timer: 2200, showConfirmButton: false });
    @endif
</script>
</body>
</html>