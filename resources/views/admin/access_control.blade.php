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
    <style>
        .access-control-page { background: #f1f5f9; min-height: 100%; }
        .access-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            background: linear-gradient(120deg, #fff 0%, #f8fafc 70%, #eff6ff 100%);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
        }
        .access-hero-icon {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            border-radius: 17px;
            color: #2563eb;
            background: #dbeafe;
            font-size: 1.55rem;
        }
        .access-stat {
            height: 100%;
            padding: 1.15rem 1.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        }
        .access-stat-icon {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            color: #2563eb;
            background: #eff6ff;
            font-size: 1.1rem;
        }
        .branch-filter {
            display: flex;
            align-items: end;
            gap: .75rem;
            padding: 1rem 1.15rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
        }
        .branch-filter-select { min-width: 230px; }
        .access-table-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 1.15rem;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
        }
        .access-table { min-width: 760px; }
        .access-table thead th {
            padding-top: 1rem;
            padding-bottom: 1rem;
            color: #64748b;
            background: #f8fafc;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .access-table tbody td { padding-top: 1rem; padding-bottom: 1rem; border-color: #f1f5f9; }
        .access-person { display: flex; align-items: center; gap: .75rem; }
        .access-avatar {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 14px;
            color: #1d4ed8;
            background: linear-gradient(145deg, #dbeafe, #ede9fe);
            font-weight: 700;
        }
        .branch-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .38rem .65rem;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #475569;
            background: #f8fafc;
            font-size: .8rem;
            white-space: nowrap;
        }
        .access-settings { display: grid; gap: .5rem; }
        .access-setting {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .7rem;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: #fff;
        }
        .access-setting .form-check { min-height: 0; margin: 0; }
        .access-setting .form-check-input { cursor: pointer; }
        .access-setting-label { color: #334155; font-size: .82rem; font-weight: 600; }
        .access-save {
            width: 100%;
            border: 0;
            border-radius: .75rem;
            color: #fff;
            background: #1d4ed8;
            font-weight: 600;
            transition: background .2s ease, transform .2s ease;
        }
        .access-save:hover { color: #fff; background: #1e40af; transform: translateY(-1px); }
        @media (max-width: 768px) {
            .access-hero { align-items: flex-start; padding: 1.25rem; }
            .access-hero-icon { display: none; }
        }
    </style>
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

            <div class="container-fluid p-4 access-control-page">
                <section class="access-hero mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="access-hero-icon"><i class="bi bi-shield-lock"></i></div>
                        <div>
                            <span class="text-primary small fw-bold text-uppercase">Permissions</span>
                            <h4 class="fw-bold mb-1">Staff Access Control</h4>
                            <p class="text-muted mb-0">Choose which tools each Staff account can use.</p>
                        </div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border px-3 py-2">
                        <i class="bi bi-people me-1"></i> {{ $staff->count() }} staff shown
                    </span>
                </section>

                <form method="GET" action="{{ route('admin.access-control') }}" class="branch-filter mb-4">
                    <div class="branch-filter-select">
                        <label for="accessBranchFilter" class="form-label small fw-semibold text-muted mb-1">Filter by branch</label>
                        <select id="accessBranchFilter" name="branch_id" class="form-select">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i> Apply</button>
                    @if($selectedBranchId)
                        <a href="{{ route('admin.access-control') }}" class="btn btn-light border">Clear</a>
                    @endif
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="access-stat d-flex align-items-center gap-3">
                            <span class="access-stat-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                            <div><small class="text-muted d-block">Report access enabled</small><strong class="fs-5" id="reportAccessCount">{{ $staff->where('can_view_reports', 1)->count() }}</strong></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="access-stat d-flex align-items-center gap-3">
                            <span class="access-stat-icon text-success bg-success-subtle"><i class="bi bi-patch-check"></i></span>
                            <div><small class="text-muted d-block">Payment verification enabled</small><strong class="fs-5" id="paymentAccessCount">{{ $staff->where('can_verify_payments', 1)->count() }}</strong></div>
                        </div>
                    </div>
                    <div class="col-xl-4 d-none d-xl-block">
                        <div class="access-stat d-flex align-items-center gap-3">
                            <span class="access-stat-icon text-info bg-info-subtle"><i class="bi bi-diagram-3"></i></span>
                            <div><small class="text-muted d-block">Branch-scoped report access</small><strong class="fs-6">Enabled per staff account</strong></div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="access-table-card">
                    <div class="table-responsive">
                        <table class="table access-table align-middle mb-0">
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
                                        <td class="ps-4">
                                            <div class="access-person">
                                                <span class="access-avatar">{{ strtoupper(substr($member->fullname ?? 'S', 0, 1)) }}</span>
                                                <div><div class="fw-semibold text-dark">{{ $member->fullname }}</div><small class="text-muted">Staff account</small></div>
                                            </div>
                                        </td>
                                        <td><span class="branch-pill"><i class="bi bi-geo-alt"></i>{{ $member->branch_name ?? 'No branch assigned' }}</span></td>
                                        <td class="text-muted">{{ $member->email }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.access-control.staff.update', $member->id) }}" method="POST" class="access-settings mx-auto" style="max-width: 265px;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="can_view_reports" value="0">
                                                <input type="hidden" name="can_verify_payments" value="0">
                                                @if($selectedBranchId)
                                                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                                @endif
                                                <div class="access-setting">
                                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                                    <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="can_view_reports" value="1" id="staffReportAccess{{ $member->id }}" @checked($member->can_view_reports)>
                                                    <label class="form-check-label access-setting-label" for="staffReportAccess{{ $member->id }}">Reports</label>
                                                    </div>
                                                    <span class="badge rounded-pill {{ $member->can_view_reports ? 'text-bg-primary' : 'text-bg-light text-secondary' }}">{{ $member->can_view_reports ? 'On' : 'Off' }}</span>
                                                </div>
                                                <div class="access-setting">
                                                    <i class="bi bi-patch-check text-success"></i>
                                                    <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="can_verify_payments" value="1" id="staffPaymentVerification{{ $member->id }}" @checked($member->can_verify_payments)>
                                                    <label class="form-check-label access-setting-label" for="staffPaymentVerification{{ $member->id }}">Payment verification</label>
                                                    </div>
                                                    <span class="badge rounded-pill {{ $member->can_verify_payments ? 'text-bg-success' : 'text-bg-light text-secondary' }}">{{ $member->can_verify_payments ? 'On' : 'Off' }}</span>
                                                </div>
                                                <button type="submit" class="btn access-save py-2"><i class="bi bi-check2-circle me-1"></i> Save access</button>
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
    <script>
        document.querySelectorAll('.access-setting input[type="checkbox"]').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                var badge = toggle.closest('.access-setting').querySelector('.badge');
                var enabled = toggle.checked;
                var enabledClass = toggle.name === 'can_view_reports' ? 'text-bg-primary' : 'text-bg-success';

                badge.textContent = enabled ? 'On' : 'Off';
                badge.className = 'badge rounded-pill ' + (enabled ? enabledClass : 'text-bg-light text-secondary');

                document.getElementById('reportAccessCount').textContent =
                    document.querySelectorAll('input[name="can_view_reports"][value="1"]:checked').length;
                document.getElementById('paymentAccessCount').textContent =
                    document.querySelectorAll('input[name="can_verify_payments"][value="1"]:checked').length;
            });
        });
    </script>
    @include('shared.live-refresh')
</body>
</html>
