document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. SIDEBAR LOGIC ---
    const sidebar = document.getElementById('sidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const toggleBtn = document.getElementById('sidebarCollapse');

    function toggleSidebar() {
        sidebar?.classList.toggle('active');
    }

    toggleBtn?.addEventListener('click', toggleSidebar);
    closeBtn?.addEventListener('click', function() {
        sidebar?.classList.remove('active');
    });

    document.addEventListener('click', function(event) {
        if (sidebar) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickInsideToggle = toggleBtn?.contains(event.target);

            if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // --- 2. LOGOUT SCRIPT (SweetAlert2 + Laravel) ---
    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        const logoutBtn = logoutForm.querySelector('.logout-btn') || logoutForm.querySelector('button');
        logoutBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Sign Out?',
                text: "Are you sure you want to logout from your account?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'Stay'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit();
                }
            });
        });
    }

    // --- 3. CLAIM ORDER SCRIPT ---
    document.querySelectorAll('.btn-claim').forEach(button => {
        button.addEventListener('click', function() {
            const ref = this.getAttribute('data-ref');

            Swal.fire({
                title: 'Have you received it?',
                text: "Make sure you have your laundry in hand before confirming.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Yes, I have received it!',
                cancelButtonText: 'Not yet'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/claim-order-route', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: 'ref_number=' + encodeURIComponent(ref)
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thank you!',
                                text: 'Your order has been marked as Claimed.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); 
                            });
                        } else {
                            Swal.fire('Error', 'There was an error updating the status.', 'error');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    });

    // --- 4. 13 DIGIT VALIDATION & COUNTER ---
    const refInput = document.getElementById('payment_ref');
    const refCount = document.getElementById('ref_count');

    refInput?.addEventListener('input', function() {
        const len = this.value.length;
        if(refCount) refCount.innerText = `Characters: ${len}/13`;
        
        if(len === 13) {
            if(refCount) refCount.className = "text-success fw-bold small";
        } else {
            if(refCount) refCount.className = "text-muted small";
        }
    });

    // --- 5. SUBMIT PAYMENT LOGIC ---
    const paymentForm = document.getElementById('paymentForm');

    paymentForm?.addEventListener('submit', function(e) {
        if (paymentForm.dataset.confirmed === "true") {
            return; // Tuloy sa pagsusumite kapag nakumpirma na
        }

        e.preventDefault();

        const fileInput = document.querySelector('input[name="proof_of_payment"]') || document.querySelector('input[name="proof_img"]');

        if (!refInput || refInput.value.length !== 13) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Reference Number',
                text: 'Please enter the exact 13-digit Gcash/Maya reference number.',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        if (!fileInput || fileInput.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Proof',
                text: 'Please upload your payment screenshot.',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        Swal.fire({
            title: 'Submit Payment?',
            text: "Please verify that your reference number and screenshot are correct.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                paymentForm.dataset.confirmed = "true";
                paymentForm.submit();
            }
        });
    });

    // --- 6. PROFILE UPDATE & PREVIEW LOGIC ---
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePreview = document.getElementById('profilePreview');

    profilePicInput?.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (profilePreview) {
                    profilePreview.src = e.target.result;
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Confirm Modal para sa Profile Update (TANGGAL ANG FEAKE SUCCESS ALERT DITO)
    const profileForm = document.getElementById('profileUpdateForm');
    profileForm?.addEventListener('submit', function(e) {
        if (profileForm.dataset.confirmed === "true") {
            return;
        }

        e.preventDefault();

        Swal.fire({
            title: 'Do you want to save the changes?',
            text: "Your profile information will be updated.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, save it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                profileForm.dataset.confirmed = "true";
                profileForm.submit(); // Direct submit pabalik sa Laravel backend!
            }
        });
    });

    // --- 7. CHANGE PASSWORD SWEETALERT LOGIC ---
    const passwordForm = document.getElementById('passwordUpdateForm') || document.querySelector('form[action*="password"]');
    
    passwordForm?.addEventListener('submit', function(e) {
        if (passwordForm.dataset.confirmed === "true") {
            return;
        }

        const newPass = document.getElementById('newPassword')?.value;
        const confirmPass = document.getElementById('confirmPassword')?.value;

        if (newPass && confirmPass && newPass !== confirmPass) {
            return; // Hayaanang humarang muna ang HTML/JS validation kung hindi magkatugma
        }

        e.preventDefault();

        Swal.fire({
            title: 'Update Password?',
            text: "Are you sure you want to change your password?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                passwordForm.dataset.confirmed = "true";
                passwordForm.submit(); // Direct submit pabalik sa Laravel backend!
            }
        });
    });

});

// --- GLOBAL FUNCTIONS ---

function goToStep2() {
    const selectedMethod = document.querySelector('input[name="method"]:checked');
    if (!selectedMethod) return;

    const method = selectedMethod.value;
    const qrImg = document.getElementById('qr_image');
    const adminNum = document.getElementById('admin_num');

    if (method === 'GCash') {
        if(qrImg) qrImg.src = 'Gcash/gcash_qr.jpg'; 
        if(adminNum) adminNum.innerText = '09098256981';
    } else {
        if(qrImg) qrImg.src = 'Gcash/maya_qr.jpg';
        if(adminNum) adminNum.innerText = '09098256981';
    }

    document.getElementById('payment_step1')?.classList.add('d-none');
    document.getElementById('payment_step2')?.classList.remove('d-none');
}

function goToStep1() {
    document.getElementById('payment_step1')?.classList.remove('d-none');
    document.getElementById('payment_step2')?.classList.add('d-none');
}

function viewProof(path) {
    if(!path) return Swal.fire('Error', 'No proof image found.', 'error');
    Swal.fire({
        title: 'Payment Proof',
        imageUrl: path,
        imageAlt: 'Proof of Payment',
        imageWidth: 250,
        imageHeight: 'auto',
        confirmButtonColor: '#0ea5e9',
        customClass: { image: 'rounded shadow-sm border' }
    });
}

function copyReferral() {
    const copyText = document.getElementById("referralCode");
    if(copyText) {
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        
        try {
            navigator.clipboard.writeText(copyText.value);
            const msg = document.getElementById("copyMsg");
            if(msg) {
                msg.style.display = "block";
                setTimeout(() => { msg.style.display = "none"; }, 2000);
            }
        } catch (err) {
            console.error('Hindi ma-copy ang code: ', err);
        }
    }
}