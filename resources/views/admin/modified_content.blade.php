<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modified Content | LaundryCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/Admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .form-control { border-radius: 8px; padding: 10px 15px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
        .btn-primary { background-color: #0ea5e9; border: none; border-radius: 8px; font-weight: 600; }
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
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Reports</span><i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="reportsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.transaction.report') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Transaction Report</a></li>
                        <li class="nav-item"><a href="{{ route('admin.financial.reports') }}" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Financial Reports</a></li>
                        <li class="nav-item"><a href="{{ route('admin.utility.tracking') }}" class="nav-link"><i class="bi bi-droplet"></i> Utility Report</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#settingsDropdown" role="button" aria-expanded="true">
                    <span><i class="bi bi-gear"></i> Settings</span><i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="settingsDropdown">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item"><a href="{{ route('admin.branch.management') }}" class="nav-link"><i class="bi bi-shop"></i> Branch Management</a></li>
                        <li class="nav-item"><a href="{{ route('admin.manage.users') }}" class="nav-link"><i class="bi bi-person-plus"></i> Manage Users</a></li>
                        <li class="nav-item"><a href="{{ route('admin.modified.content') }}" class="nav-link active"><i class="bi bi-pencil-square"></i> Modified Content</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="{{ route('admin.loyalty.program') }}" class="nav-link"><i class="bi bi-star"></i> Loyalty Program</a></li>
            <li class="nav-item mt-2"><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="nav-link text-danger logout-btn border-0 bg-transparent w-100 text-start"><i class="bi bi-power"></i> Logout</button></form></li>
        </ul>
    </nav>
    <div class="main-wrapper">
        <nav class="navbar navbar-light px-4 py-3 sticky-header bg-white border-bottom">
            <div class="d-flex align-items-center w-100"><button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button><h5 class="fw-bold mb-0">Modified Content</h5></div>
        </nav>
        <div class="container-fluid p-4">
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('admin.modified.content.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-6"><div class="card p-4 h-100"><h5 class="fw-bold mb-4"><i class="bi bi-info-circle text-primary me-2"></i>About Us Content</h5>
                        <label class="small fw-bold text-muted mb-2">TITLE</label><input name="about_title" class="form-control mb-3" value="{{ old('about_title', $content['about_title']) }}" required>
                        <label class="small fw-bold text-muted mb-2">MAIN CONTENT</label><textarea name="about_content" class="form-control mb-3" rows="6" required>{{ old('about_content', $content['about_content']) }}</textarea>
                        <label class="small fw-bold text-muted mb-2">SECOND PARAGRAPH</label><textarea name="about_secondary" class="form-control" rows="6" required>{{ old('about_secondary', $content['about_secondary']) }}</textarea>
                        @php
                            $aboutImage = $content['about_image'] ?? 'image/laundry.jpg';
                            $aboutImageUrl = str_starts_with($aboutImage, 'http://') || str_starts_with($aboutImage, 'https://')
                                ? $aboutImage
                                : (str_starts_with($aboutImage, 'image/') ? asset($aboutImage) : asset('storage/' . $aboutImage));
                        @endphp
                        <label class="small fw-bold text-muted mb-2 mt-3">ABOUT US IMAGE</label>
                        <img src="{{ $aboutImageUrl }}" id="aboutImagePreview" alt="About Us preview" class="img-fluid rounded-3 border mb-2" style="height: 180px; width: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='{{ asset('image/laundry.jpg') }}';">
                        <input type="file" name="about_image" id="aboutImageInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                        <div class="form-text">Optional. JPG, PNG, GIF, or WEBP up to 5 MB.</div>
                        @error('about_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div></div>
                    <div class="col-lg-6"><div class="card p-4 h-100"><h5 class="fw-bold mb-4"><i class="bi bi-envelope text-primary me-2"></i>Contact Us Content</h5>
                        <label class="small fw-bold text-muted mb-2">DESCRIPTION</label><textarea name="contact_description" class="form-control mb-3" rows="3" required>{{ old('contact_description', $content['contact_description']) }}</textarea>
                        <label class="small fw-bold text-muted mb-2">ADDRESS</label><input name="contact_address" class="form-control mb-3" value="{{ old('contact_address', $content['contact_address']) }}" required>
                        <label class="small fw-bold text-muted mb-2">EMAIL</label><input type="email" name="contact_email" class="form-control mb-3" value="{{ old('contact_email', $content['contact_email']) }}" required>
                        <label class="small fw-bold text-muted mb-2">PHONE</label><input name="contact_phone" class="form-control mb-3" value="{{ old('contact_phone', $content['contact_phone']) }}" required>
                        <label class="small fw-bold text-muted mb-2">OPERATING HOURS</label><input name="contact_hours" class="form-control" value="{{ old('contact_hours', $content['contact_hours']) }}" required>
                    </div></div>
                </div>
                <button type="submit" class="btn btn-primary px-4 py-2 mt-4"><i class="bi bi-save me-2"></i>Save Modified Content</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/Admin_script.js') }}"></script>
<script>
    document.getElementById('aboutImageInput')?.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('aboutImagePreview').src = URL.createObjectURL(file);
        }
    });
</script>
@if(session('success'))
<script>
    Swal.fire({
        title: 'Updated!',
        text: @json(session('success')),
        icon: 'success',
        timer: 1800,
        showConfirmButton: false
    });
</script>
@endif
</body>
</html>
