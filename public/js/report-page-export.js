document.addEventListener('DOMContentLoaded', () => {
    window.printCurrentReport = window.printCurrentReport || (() => window.print());

    const controls = document.querySelector('.report-export-controls');
    if (!controls) return;

    const modalElement = document.getElementById('reportPageSelectionModal');
    const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    const pageInput = document.getElementById('reportPageSelectionInput');
    const pageError = document.getElementById('reportPageSelectionError');
    let selectedMode = null;

    const runCurrentExport = (mode) => {
        const functionName = mode === 'pdf'
            ? controls.dataset.pdfFunction
            : controls.dataset.printFunction;
        const exportFunction = window[functionName];

        if (typeof exportFunction === 'function') {
            exportFunction();
        } else {
            alert('Hindi available ang napiling export action. I-refresh ang page at subukan muli.');
        }
    };

    const openExport = (mode, pages) => {
        const query = JSON.parse(controls.dataset.exportQuery || '{}');
        query.export_mode = mode;
        query.export_pages = pages;

        const url = new URL(controls.dataset.exportUrl, window.location.origin);
        Object.entries(query).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        });

        window.open(url.toString(), '_blank');
    };

    controls.addEventListener('click', (event) => {
        const current = event.target.closest('.report-export-current');
        if (current) {
            runCurrentExport(current.dataset.exportMode);
            return;
        }

        const all = event.target.closest('.report-export-all');
        if (all) {
            openExport(all.dataset.exportMode, 'all');
            return;
        }

        const choose = event.target.closest('.report-export-choose');
        if (choose && modal) {
            selectedMode = choose.dataset.exportMode;
            pageInput.value = '';
            pageInput.classList.remove('is-invalid');
            pageError.textContent = `Enter page numbers from 1 to ${controls.dataset.pageCount}.`;
            modal.show();
            pageInput.focus();
        }
    });

    document.getElementById('reportPageSelectionForm')?.addEventListener('submit', (event) => {
        event.preventDefault();

        const requestedPages = pageInput.value.trim();
        const pageCount = Number(controls.dataset.pageCount);
        const validFormat = /^[1-9]\d*(\s*,\s*[1-9]\d*)*$/.test(requestedPages);
        const pages = validFormat ? [...new Set(requestedPages.split(',').map((page) => Number(page.trim())))] : [];
        const validRange = pages.length > 0 && pages.every((page) => page <= pageCount);

        if (!validFormat || !validRange) {
            pageInput.classList.add('is-invalid');
            pageError.textContent = `Enter comma-separated page numbers between 1 and ${pageCount}.`;
            return;
        }

        modal.hide();
        openExport(selectedMode, pages.join(','));
    });

    const exportMode = document.body.dataset.reportExportMode;
    if (exportMode) {
        window.setTimeout(() => runCurrentExport(exportMode), 500);
    }
});
