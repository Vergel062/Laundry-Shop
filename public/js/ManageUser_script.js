document.addEventListener('DOMContentLoaded', function() {
    console.log("Manager JS Loaded and Ready");

    // --- 1. SIDEBAR TOGGLE ---
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const btnToggle = document.getElementById('btnToggle') || document.getElementById('sidebarToggle');
    const btnCloseSidebar = document.getElementById('btnCloseSidebar') || document.getElementById('closeSidebar');

    function toggleMenu() { 
        if (sidebar) sidebar.classList.toggle('active'); 
        if (overlay) overlay.classList.toggle('active'); 
    }

    if (btnToggle) btnToggle.onclick = toggleMenu;
    if (btnCloseSidebar) btnCloseSidebar.onclick = toggleMenu;
    if (overlay) overlay.onclick = toggleMenu;

    // --- 2. AUTOMATIC PRICE CALCULATION (From Staff JS) ---
    const pricePerKilo = 35;
    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'weightInput') {
            const weight = parseFloat(e.target.value) || 0;
            const amountField = document.getElementById('amountInput');
            if (amountField) {
                amountField.value = (weight * pricePerKilo).toFixed(2);
            }
        }
    });

    // --- 3. SAVE ORDER VIA AJAX (From Staff JS) ---
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({ title: 'Processing Order...', didOpen: () => { Swal.showLoading(); } });
            
            const actionUrl = this.getAttribute('action');

            fetch(actionUrl, { 
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: new FormData(this) 
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success!', 'Order added.', 'success').then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Notice',
                        text: data.message || 'Error processing order.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            })
            .catch(err => {
                console.error("Error:", err);
                Swal.fire('Error!', 'Something went wrong with the server.', 'error');
            });
        });
    }

    // --- 4. DELETE CONFIRMATION ---
    document.querySelectorAll('.delete-confirm').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure to delete this record?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // --- 5. AUTO DISMISS ALERT ---
    setTimeout(function() {
        const alert = document.getElementById('status-alert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);

    // --- 6. LOGOUT SWEETALERT CONFIRMATION ---
    const logoutBtn = document.querySelector('.logout-btn') || document.querySelector('.logout-manager-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const logoutForm = this.closest('form');
            const url = this.getAttribute('href');

            Swal.fire({
                title: 'Confirm Logout',
                text: "Are you sure you want to end your manager session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (logoutForm) {
                        logoutForm.submit();
                    } else if (url) {
                        window.location.href = url;
                    }
                }
            });
        });
    }
});