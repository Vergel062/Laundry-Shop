<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Control | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
</head>
<body class="bg-light">
    <div class="overlay" id="overlay"></div>
    <div class="app-container">
        <nav id="sidebar">
            <div class="sidebar-brand p-4 text-center">
                <h4 class="fw-bold text-info mb-0"><i class="bi bi-droplet-half"></i> Laundry<span class="text-white">Care</span></h4>
            </div>
            <ul class="nav flex-column mt-2">
                <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="nav-item"><a href="{{ route('admin.service.history') }}" class="nav-link"><i class="bi bi-clock-history"></i> Transaction History</a></li>
                <li class="nav-item"><a href="{{ route('admin.inventory') }}" class="nav-link"><i class="bi bi-box-seam"></i> Branch Inventory</a></li>
                <hr class="mx-3 opacity-25">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="true">
                        <span><i class="bi bi-gear"></i> Settings</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse show" id="settingsDropdown">
                        <ul class="nav flex-column ps-3">
                            <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                            <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                            <li class="nav-item"><a href="{{ route('admin.access-control') }}" class="nav-link active"><i class="bi bi-shield-lock"></i> Access Control</a></li>
                            <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item mt-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-power"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </nav>

        <div class="main-wrapper">
            <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                    <h5 class="fw-bold mb-0">Access Control</h5>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div class="mb-4">
                    <h4 class="fw-bold">Staff Access Control</h4>
                    <p class="text-muted mb-0">Manage report access and payment verification for each Staff account.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Staff</th>
                                    <th>Branch</th>
                                    <th>Email</th>
                                    <th class="text-center">Access</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $member)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $member->fullname }}</td>
                                        <td>{{ $member->branch_name ?? 'No branch assigned' }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.access-control.staff.update', $member->id) }}" method="POST" class="d-inline-flex flex-column align-items-start gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="can_view_reports" value="0">
                                                <input type="hidden" name="can_verify_payments" value="0">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="can_view_reports" value="1" id="staffReportAccess{{ $member->id }}" @checked($member->can_view_reports)>
                                                    <label class="form-check-label" for="staffReportAccess{{ $member->id }}">
                                                        Reports: {{ $member->can_view_reports ? 'On' : 'Off' }}
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="can_verify_payments" value="1" id="staffPaymentVerification{{ $member->id }}" @checked($member->can_verify_payments)>
                                                    <label class="form-check-label" for="staffPaymentVerification{{ $member->id }}">
                                                        Payment Verification: {{ $member->can_verify_payments ? 'On' : 'Off' }}
                                                    </label>
                                                </div>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-5">No active Staff accounts found.</td></tr>
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
    @include('shared.live-refresh')
</body>
</html>
