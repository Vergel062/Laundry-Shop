<div class="modal fade no-print" id="reportPageSelectionModal" tabindex="-1" aria-labelledby="reportPageSelectionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="reportPageSelectionForm">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="reportPageSelectionTitle">Choose Report Pages</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="reportPageSelectionInput" class="form-label">Page numbers</label>
                    <input type="text" class="form-control" id="reportPageSelectionInput" placeholder="For example: 2,3,5" inputmode="numeric" autocomplete="off">
                    <div class="form-text">Enter page numbers separated by commas. This report has {{ $pageCount }} {{ \Illuminate\Support\Str::plural('page', $pageCount) }} (20 records per page).</div>
                    <div class="invalid-feedback" id="reportPageSelectionError">Enter valid page numbers within the available range.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="reportPageSelectionSubmit">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>
