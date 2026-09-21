document.addEventListener('DOMContentLoaded', function() {
    console.log("JS Loaded and Ready");

    // 1. Sidebar Toggle (Sinusuportahan pareho ang sidebarCollapse, sidebarToggle, at close/overlay)
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar) sidebar.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
    }

    document.getElementById('sidebarCollapse')?.addEventListener('click', toggleSidebar);
    document.getElementById('sidebarToggle')?.addEventListener('click', toggleSidebar);
    document.getElementById('closeSidebar')?.addEventListener('click', toggleSidebar);
    document.getElementById('overlay')?.addEventListener('click', toggleSidebar);

    // 2. Automatic Price Calculation
    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'weightInput') {
            const weight = parseFloat(e.target.value) || 0;
            const amountField = document.getElementById('amountInput');
            const serviceSelect = document.getElementById('serviceSelect');
            const basePrice = parseFloat(serviceSelect?.selectedOptions[0]?.dataset.basePrice) || 0;
            if (amountField) {
                amountField.value = weight > 0 && basePrice > 0
                    ? (basePrice + Math.max(0, weight - 8) * 10).toFixed(2)
                    : '';
            }
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'serviceSelect') {
            const weight = parseFloat(document.getElementById('weightInput')?.value) || 0;
            const basePrice = parseFloat(e.target.selectedOptions[0]?.dataset.basePrice) || 0;
            const amountField = document.getElementById('amountInput');
            if (amountField) {
                amountField.value = weight > 0 && basePrice > 0
                    ? (basePrice + Math.max(0, weight - 8) * 10).toFixed(2)
                    : '';
            }
        }
    });

    // 3. Save Order via AJAX
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

    // 4. Logout Logic
    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        const logoutBtn = logoutForm.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Confirm Logout',
                    text: "Are you sure you want to end your staff session?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Logout'
                }).then((result) => {
                    if (result.isConfirmed) { 
                        logoutForm.submit(); 
                    }
                });
            });
        }
    }
});