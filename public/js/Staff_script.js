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
    function updateOrderAmount() {
        const weight = parseFloat(document.getElementById('weightInput')?.value) || 0;
        const serviceSelect = document.getElementById('serviceSelect');
        const amountField = document.getElementById('amountInput');
        const basePrice = parseFloat(serviceSelect?.selectedOptions[0]?.dataset.basePrice) || 0;
        const detergentQty = parseInt(document.querySelector('[name="det_qty"]')?.value, 10) || 0;
        const softenerQty = parseInt(document.querySelector('[name="soft_qty"]')?.value, 10) || 0;
        const sachetCharge = Math.max(0, detergentQty + softenerQty - 3) * 10;

        if (amountField) {
            amountField.value = weight > 0 && basePrice > 0
                ? (basePrice + Math.max(0, weight - 8) * 10 + sachetCharge).toFixed(2)
                : '';
        }

        const hint = document.querySelector('.sachet-charge-hint');
        if (hint) {
            hint.textContent = `Sachet charge: ₱${sachetCharge.toFixed(2)} (first 3 combined are free).`;
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target && ['weightInput', 'det_qty', 'soft_qty'].includes(e.target.id || e.target.name)) {
            updateOrderAmount();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target?.id === 'serviceSelect') {
            updateOrderAmount();
        }

        if (e.target?.matches('.sachet-inventory-select')) {
            const quantityInput = document.querySelector(`[name="${e.target.dataset.quantity}"]`);
            const stock = parseInt(e.target.selectedOptions[0]?.dataset.stock, 10);
            if (quantityInput) {
                quantityInput.max = Number.isFinite(stock) ? stock : '';
                if (Number.isFinite(stock) && parseInt(quantityInput.value, 10) > stock) {
                    quantityInput.value = stock;
                }
            }
        }
    });

    // 3. Save Order via AJAX
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        document.getElementById('newOrderModal')?.addEventListener('hidden.bs.modal', function () {
            orderForm.reset();
            orderForm.querySelectorAll('.sachet-quantity').forEach(input => {
                input.max = '';
            });
            updateOrderAmount();
        });

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