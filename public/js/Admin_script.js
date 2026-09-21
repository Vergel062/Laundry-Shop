// MOBILE SIDEBAR LOGIC
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const toggleBtn = document.getElementById('sidebarToggle');
const closeBtn = document.getElementById('closeSidebar');

function toggleMenu() {
    if(sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
}

toggleBtn?.addEventListener('click', toggleMenu);
closeBtn?.addEventListener('click', toggleMenu);
overlay?.addEventListener('click', toggleMenu);

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && sidebar?.classList.contains('active')) {
        sidebar.classList.remove('active');
        overlay?.classList.remove('active');
    }
});

// ADMIN/MANAGER LOGOUT SWEETALERT
document.addEventListener('click', function(e) {
    const logoutBtn = e.target.closest('.logout-btn, .logout-manager-btn');
    if (logoutBtn) {
        e.preventDefault();
        const logoutForm = logoutBtn.closest('form');
        const isManagerLogout = logoutBtn.classList.contains('logout-manager-btn');
        
        Swal.fire({
            title: 'Confirm Logout',
            text: isManagerLogout
                ? "Are you sure you want to end your manager session?"
                : "Are you sure you want to end your admin session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && logoutForm) {
                logoutForm.submit();
            }
        });
    }
});

// BRANCH FILTER FUNCTION
function filterBranch(branchId) {
    window.location.href = "/admin?branch_id=" + branchId;
}

// PRINT TIMESTAMP UPDATER
function updatePrintTimestamp() {
    const now = new Date();
    const optionsDate = { month: 'long', day: '2-digit', year: 'numeric' };
    const optionsTime = { hour: 'numeric', minute: '2-digit', hour12: true };
    
    const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    const formattedTime = now.toLocaleTimeString('en-US', optionsTime);
    
    const dateString1 = `${formattedDate} ${formattedTime}`;
    const dateString2 = `${formattedDate} - ${formattedTime}`;
    
    document.querySelectorAll('.printGeneratedDate').forEach(el => el.innerText = dateString1);
    document.querySelectorAll('.printGeneratedDateTime').forEach(el => el.innerText = dateString2);
}

// PRINT TRIGGER
function triggerPrint() {
    updatePrintTimestamp();
    window.print();
}

// EXPORT TO PDF FUNCTION
async function exportToPDF() {
    const pdfBtn = document.getElementById('pdfBtn');
    const element = document.getElementById('printableArea');

    if (!element) {
        alert("Error: Element #printableArea not found.");
        return;
    }

    let originalText = "";
    if (pdfBtn) {
        originalText = pdfBtn.innerHTML;
        pdfBtn.disabled = true;
        pdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...';
    }

    updatePrintTimestamp();

    const opt = {
        margin:       [10, 10, 10, 10],
        filename:     'Utility_Expenses_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true, 
            logging: false,
            onclone: function(clonedDoc) {
                const clonedElement = clonedDoc.getElementById('printableArea');
                if (clonedElement) {
                    clonedElement.style.width = '100%';
                    clonedElement.style.maxWidth = '100%';
                    clonedElement.style.margin = '0';
                    clonedElement.style.padding = '0';
                    clonedElement.style.backgroundColor = '#ffffff';

                    // Tanggalin ang navbar sa PDF view
                    const navs = clonedElement.querySelectorAll('.navbar');
                    navs.forEach(n => n.remove());

                    // Ipakita ang print header at footer sa PDF
                    const printHeaders = clonedElement.querySelectorAll('.print-document-header, .print-footer-block');
                    printHeaders.forEach(el => {
                        el.classList.remove('d-none');
                        el.style.display = 'block';
                    });

                    // Itago ang mga elements na may class na no-print
                    const noPrints = clonedElement.querySelectorAll('.no-print');
                    noPrints.forEach(el => {
                        el.style.display = 'none';
                    });
                }
            }
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    try {
        await html2pdf().set(opt).from(element).save();
    } catch (error) {
        console.error('PDF Export Error:', error);
        alert('Nagkaroon ng problema sa pag-save ng PDF.');
    } finally {
        if (pdfBtn) {
            pdfBtn.disabled = false;
            pdfBtn.innerHTML = originalText;
        }
    }
}