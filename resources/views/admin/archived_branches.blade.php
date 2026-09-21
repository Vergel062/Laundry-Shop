<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Branches | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .rounded-15 { border-radius: 15px; }
        .rounded-8 { border-radius: 8px; }
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
                    <button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start"><i class="bi bi-power"></i> Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle" type="button" aria-label="Open navigation">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="fw-bold mb-0">Archived Branches</h5>
            </div>
            <a href="{{ route('admin.branch.management') }}" class="btn btn-outline-secondary btn-sm rounded-8"><i class="bi bi-arrow-left"></i> Go Back</a>
        </nav>
        <div class="container-fluid p-4">
            <div class="card shadow-sm border-0 rounded-15 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-4 py-3">Branch Name</th>
                                <th>Location</th>
                                <th>Date Archived</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archived as $row)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $row->branch_name }}</td>
                                <td><i class="bi bi-geo-alt text-muted me-1"></i>{{ $row->location }}</td>
                                <td class="text-muted">{{ date('M d, Y h:i A', strtotime($row->archive_date)) }}</td>
                                <td class="text-center">
                                    <form id="restore-form-{{ $row->id }}" action="{{ route('admin.branch.restore', $row->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" class="btn btn-outline-success btn-sm rounded-8 me-1" onclick="confirmRestore({{ $row->id }})"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                    </form>
                                    <form id="delete-form-{{ $row->id }}" action="{{ route('admin.branch.destroy', $row->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-8" onclick="confirmDelete({{ $row->id }})"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No archived branches found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>
<script>
    function confirmRestore(id) {
        Swal.fire({
            title: 'Restore Branch?',
            text: 'This branch will return to Branch Management.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Restore'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('restore-form-' + id).submit();
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Permanently?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        });
    }

    @if(session('msg') === 'restored')
        Swal.fire({ title: 'Restored!', text: 'Branch restored successfully.', icon: 'success', timer: 1800, showConfirmButton: false });
    @elseif(session('msg') === 'deleted')
        Swal.fire({ title: 'Deleted!', text: 'Branch permanently deleted.', icon: 'success', timer: 1800, showConfirmButton: false });
    @endif
</script>
</body>
</html>
