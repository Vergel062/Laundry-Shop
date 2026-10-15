document.addEventListener('DOMContentLoaded', () => {
    window.printCurrentReport = window.printCurrentReport || (() => window.print());

    const controls = document.querySelector('.report-export-controls');
    if (!controls) return;

    const modalElement = document.getElementById('reportPageSelectionModal');
    const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    const pageInput = document.getElementById('reportPageSelectionInput');
    const pageError = document.getElementById('reportPageSelectionError');
    const isFinancialPdf = controls.dataset.pdfLayout === 'financial-report';
    let selectedMode = null;

    const saveReportPdf = async () => {
        const report = document.querySelector(controls.dataset.pdfTarget);
        if (!report) {
            alert('Hindi makita ang report na ise-save. I-refresh ang page at subukan muli.');
            return;
        }

        if (typeof html2pdf !== 'function') {
            alert('Hindi ma-load ang PDF generator. I-refresh ang page at subukan muli.');
            return;
        }

        document.body.classList.add('is-exporting-pdf');
        if (isFinancialPdf) {
            document.body.classList.add('is-exporting-financial-pdf');
            window.updatePrintTimestamp?.();
        }
        let saved = false;
        try {
            if (document.fonts?.ready) await document.fonts.ready;

            const pdfMargin = Number(controls.dataset.pdfMargin) || 10;
            const pdfWorker = html2pdf().set({
                margin: [pdfMargin, pdfMargin, pdfMargin, pdfMargin],
                filename: `${controls.dataset.pdfFilename}_${new Date().toISOString().slice(0, 10)}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    scrollY: 0
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: controls.dataset.pdfMargin === '8'
                    ? { mode: ['css', 'legacy'] }
                    : { mode: ['css', 'legacy'], avoid: ['tr'] }
            }).from(report).toPdf();

            if (isFinancialPdf) {
                await pdfWorker.get('pdf').then((pdf) => {
                    const pageCount = pdf.internal.getNumberOfPages();
                    const generatedAt = new Intl.DateTimeFormat('en-US', {
                        month: 'numeric',
                        day: 'numeric',
                        year: '2-digit',
                        hour: 'numeric',
                        minute: '2-digit'
                    }).format(new Date());
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const pageUrl = window.location.href;

                    for (let page = 1; page <= pageCount; page += 1) {
                        pdf.setPage(page);
                        pdf.setFont('helvetica', 'normal');
                        pdf.setFontSize(5);
                        pdf.setTextColor(55, 65, 81);
                        pdf.text(generatedAt, 8, 4);
                        pdf.text('Financial Reports | LaundryCare', pageWidth / 2, 4, { align: 'center' });
                        pdf.text(pageUrl, 8, pageHeight - 3, { maxWidth: pageWidth - 50 });
                        pdf.text(`${page}/${pageCount}`, pageWidth - 8, pageHeight - 3, { align: 'right' });
                    }
                });
            }

            await pdfWorker.save();
            saved = true;
        } catch (error) {
            console.error('Report PDF Export Error:', error);
            alert('Nagkaroon ng problema sa pag-save ng PDF.');
        } finally {
            document.body.classList.remove('is-exporting-pdf');
            if (isFinancialPdf) document.body.classList.remove('is-exporting-financial-pdf');
        }

        return saved;
    };

    const runCurrentExport = async (mode, afterPrint = null) => {
        if (mode === 'pdf') {
            const saved = await saveReportPdf();
            if (saved && afterPrint) afterPrint();
            return;
        }

        const functionName = controls.dataset.printFunction;
        const exportFunction = window[functionName];

        if (typeof exportFunction === 'function') {
            if (afterPrint) {
                window.addEventListener('afterprint', () => {
                    afterPrint();
                }, { once: true });
            }

            exportFunction();
        } else {
            alert('Hindi available ang print action. I-refresh ang page at subukan muli.');
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

        window.location.assign(url.toString());
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
        const returnUrl = controls.dataset.returnUrl;
        window.history.replaceState(null, '', returnUrl);
        window.setTimeout(() => {
            runCurrentExport(exportMode, () => window.location.replace(returnUrl));
        }, 500);
    }
});
