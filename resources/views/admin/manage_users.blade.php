<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .form-control, .form-select { border-radius: 8px; padding: 10px 15px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        .btn-primary { background-color: #0ea5e9; border: none; border-radius: 8px; font-weight: 600; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe; color: #0ea5e9; font-weight: 700; flex-shrink: 0; }
        .branch-group {
            transition: opacity 0.15s ease;
        }
        .role-filter {
            min-width: 135px;
            width: auto;
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 6px 30px 6px 10px;
            background-color: #f8fafc;
            cursor: pointer;
        }
        .role-filter:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
        }
        .user-filter {
            min-width: 205px;
        }
        .user-search {
            min-width: 175px;
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            color: #475569;
            font-size: 0.78rem;
            padding: 6px 10px;
        }
        .user-search:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
            outline: 0;
        }
        .archived-users-btn {
            height: 34px;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding-top: 0;
            padding-bottom: 0;
        }
        @media (min-width: 577px) and (max-width: 1199px) {
            .main-wrapper {
                overflow-y: auto;
            }
            .users-card-header {
                flex-wrap: wrap;
                row-gap: 10px;
            }
            .users-card-header > div:first-child {
                flex: 1 1 100%;
            }
            .users-card-header > a {
                flex: 0 0 auto;
            }
        }
        @media (max-width: 576px) {
            .users-card-header {
                align-items: stretch !important;
                flex-direction: column !important;
                gap: 10px;
            }
            .users-card-header > div:first-child {
                width: 100%;
                display: flex !important;
                flex-wrap: wrap;
                gap: 8px !important;
            }
            .users-card-header > div:first-child h6 {
                flex: 1 1 100%;
            }
            .users-card-header form {
                flex: 1 1 100%;
                flex-wrap: nowrap;
                width: 100%;
            }
            .users-card-header form .user-search {
                flex: 1 1 50%;
                width: 50%;
                min-width: 0;
            }
            .users-card-header form .user-filter {
                flex: 1 1 50%;
                width: 50%;
                min-width: 0;
            }
            .users-card-header > a {
                width: 100%;
                justify-content: center;
            }
        }
        body.swal2-shown { overflow-y: auto !important; padding-right: 0px !important; }
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
            <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i>Branch Inventory</a></li>
            <hr class="mx-3 opacity-25">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3"> 
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Reports</a></li>
                        <li class="nav-item"><a href="{{ route('admin.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Tracking</a></li>
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
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link active"><i class="bi bi-person-plus"></i> Manage Users</a></li>
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
                <h5 class="fw-bold mb-0">User Management</h5>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-4 text-dark">{!! $is_edit ? '<i class="bi bi-pencil-square me-2"></i>Edit Account' : '<i class="bi bi-person-plus me-2"></i>Create Account' !!}</h5>
                        
                        <form action="{{ $is_edit ? route('admin.manage.users.update') : route('admin.manage.users.store') }}" method="POST">
                            @csrf
                            @if($is_edit)
                                <input type="hidden" name="user_id" value="{{ $edit_data->id }}">
                            @endif

                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-2">FULL NAME</label>
                                <input type="text" name="fullname" class="form-control" value="{{ $is_edit ? $edit_data->fullname : '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-2">PHONE NUMBER</label>
                                <input type="text" name="phone" class="form-control" value="{{ $is_edit ? $edit_data->phone : '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-2">EMAIL ADDRESS</label>
                                <input type="email" name="email" class="form-control" value="{{ $is_edit ? $edit_data->email : '' }}" required>
                            </div>
                            
                            @if(!$is_edit)
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-2">PASSWORD</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-2">ACCOUNT ROLE</label>
                                <select name="role" id="roleSelect" class="form-select" onchange="toggleBranchSelect()" required>
                                    @if(!$is_edit)
                                        <option value="" selected disabled>-- Select Account Role --</option>
                                    @endif
                                    <option value="customer" {{ ($is_edit && $edit_data->role == 'customer') ? 'selected' : '' }}>Customer</option>
                                    <option value="staff" {{ ($is_edit && $edit_data->role == 'staff') ? 'selected' : '' }}>Staff</option>
                                    <option value="manager" {{ ($is_edit && $edit_data->role == 'manager') ? 'selected' : '' }}>Branch Manager</option>
                                    <option value="admin" {{ ($is_edit && $edit_data->role == 'admin') ? 'selected' : '' }}>Admin (Owner)</option>
                                </select>
                            </div>

                            <div class="branch-group mb-4 p-3 bg-light rounded-8" id="branchGroup" style="display: {{ ($is_edit && in_array($edit_data->role, ['staff', 'customer', 'manager'])) ? 'block' : 'none' }};">
                                <label class="small fw-bold text-primary mb-2"><i class="bi bi-geo-alt"></i> ASSIGN TO BRANCH</label>
                                <select name="branch_id" id="branchSelect" class="form-select border-primary shadow-sm" {{ ($is_edit && in_array($edit_data->role, ['staff', 'customer', 'manager'])) ? '' : 'disabled' }}>
                                    <option value="" selected disabled>-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ ($is_edit && $edit_data->branch_id == $b->id) ? 'selected' : '' }}>
                                            {{ $b->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm">
                                {{ $is_edit ? 'Update Account Information' : 'Create User Account' }}
                            </button>
                            @if($is_edit)
                                <a href="{{ route('admin.manage.users') }}" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small">Cancel Edit</a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card overflow-hidden h-100">
                        <div class="users-card-header card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0">Active Users</h6>
                                <form action="{{ route('admin.manage.users') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                                    <label for="userSearch" class="visually-hidden">Search user accounts</label>
                                    <input type="search" name="user_search" id="userSearch" class="user-search" placeholder="Search account..." value="{{ $userSearch ?? '' }}" aria-label="Search user accounts">
                                    <label for="userFilter" class="visually-hidden">Filter users</label>
                                    <select name="filter" id="userFilter" class="form-select role-filter user-filter" onchange="this.form.submit()" aria-label="Filter users by role or branch">
                                        <option value="all" {{ ($selectedFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Users</option>
                                        <optgroup label="By Role">
                                            <option value="role:admin" {{ ($selectedFilter ?? '') === 'role:admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="role:staff" {{ ($selectedFilter ?? '') === 'role:staff' ? 'selected' : '' }}>Staff</option>
                                            <option value="role:manager" {{ ($selectedFilter ?? '') === 'role:manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="role:customer" {{ ($selectedFilter ?? '') === 'role:customer' ? 'selected' : '' }}>Customer</option>
                                        </optgroup>
                                        <optgroup label="By Branch">
                                        @foreach($branches as $branch)
                                            <option value="branch:{{ $branch->id }}" {{ ($selectedFilter ?? '') === 'branch:' . $branch->id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                        </optgroup>
                                    </select>
                                </form>
                            </div>
                            <a href="{{ route('admin.archived.index') }}" class="archived-users-btn btn btn-sm btn-outline-warning shadow-sm">
                                <i class="bi bi-archive"></i> Archived Users
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="ps-4">User Info</th>
                                        <th>Role / Branch</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                    @php
                                        $initial = strtoupper(substr($row->fullname, 0, 1));
                                        // Sinisiguro nitong may laman at talagang nag-e-exist ang file sa storage bago i-display bilang picture
                                        $hasValidPic = !empty($row->profile_pic) && Illuminate\Support\Facades\Storage::disk('public')->exists($row->profile_pic);
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                @if($hasValidPic)
                                                    <img src="{{ asset('storage/' . $row->profile_pic) }}" alt="{{ $row->fullname }}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                                @else
                                                    <div class="user-avatar d-flex align-items-center justify-content-center me-3">{{ $initial }}</div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.manage.users.history', $row->id) }}" class="fw-bold text-dark text-decoration-none">{{ $row->fullname }}</a>
                                                    <div class="text-muted extra-small" style="font-size: 0.75rem;">{{ $row->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php 
                                                $badge_class = 'bg-secondary';
                                                if($row->role == 'admin') $badge_class = 'bg-danger';
                                                elseif($row->role == 'manager') $badge_class = 'bg-warning';
                                                elseif($row->role == 'staff') $badge_class = 'bg-info';
                                            @endphp
                                            <span class="badge {{ $badge_class }} bg-opacity-10 text-{{ str_replace('bg-','',$badge_class) }} px-3 mb-1">
                                                {{ ucfirst($row->role) }}
                                            </span>
                                            @if($row->branch_name)
                                                <div class="extra-small text-muted"><i class="bi bi-shop me-1"></i>{{ $row->branch_name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.manage.users', ['edit_id' => $row->id]) }}" class="btn btn-sm btn-light border-0 text-primary me-1"><i class="bi bi-pencil-square"></i></a>
                                            <button onclick="confirmDelete({{ $row->id }})" class="btn btn-sm btn-light border-0 text-danger"><i class="bi bi-trash3"></i></button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No active users found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script> 

<script>
function toggleBranchSelect() {
    const role = document.getElementById('roleSelect').value;
    const branchGroup = document.getElementById('branchGroup');
    const branchSelect = document.getElementById('branchSelect');
    const needsBranch = ['staff', 'customer', 'manager'].includes(role);

    branchGroup.style.display = needsBranch ? 'block' : 'none';
    branchSelect.disabled = !needsBranch;
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "The Account will be archived and permanently deleted automatically after 30 days!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ url('/admin/manage-users/archive') }}/" + id;
        }
    });
}

@if(session('msg') === 'created')
    Swal.fire('Success!', 'New user created successfully!', 'success');
@elseif(session('msg') === 'updated')
    Swal.fire('Updated!', 'User updated successfully.', 'success');
@elseif(session('msg') === 'archived')
    Swal.fire('Archived!', 'User has been archived.', 'warning');
@elseif(session('msg') === 'self_delete')
    Swal.fire('Error!', 'You cannot archive your own admin account!', 'error');
@endif
</script>

</body>
</html>