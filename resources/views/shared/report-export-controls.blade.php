<div
    class="d-flex gap-2 report-export-controls no-print"
    data-export-url="{{ $exportRoute }}"
    data-export-query="{{ json_encode(request()->except(['page', 'export_mode', 'export_pages']), JSON_HEX_APOS | JSON_HEX_QUOT) }}"
    data-return-url="{{ request()->fullUrlWithoutQuery(['export_mode', 'export_pages']) }}"
    data-page-count="{{ $pageCount }}"
    data-pdf-target="{{ $pdfTarget }}"
    data-pdf-filename="{{ $pdfFilename }}"
    data-pdf-margin="{{ $pdfMargin ?? 10 }}"
    data-print-function="{{ $printFunction }}"
>
    @foreach(['pdf' => ['Save PDF', 'file-earmark-pdf', 'btn-outline-danger'], 'print' => ['Print Report', 'printer', 'btn-dark']] as $mode => [$label, $icon, $buttonClass])
        <div class="btn-group">
            <button type="button" class="btn {{ $buttonClass }} btn-sm rounded-8 px-3 fw-bold report-export-current" data-export-mode="{{ $mode }}" {{ $canExport ? '' : 'disabled' }}>
                <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
            </button>
            <button type="button" class="btn {{ $buttonClass }} btn-sm dropdown-toggle dropdown-toggle-split rounded-8" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ $label }} options" {{ $canExport ? '' : 'disabled' }}>
                <span class="visually-hidden">Toggle {{ $label }} options</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button type="button" class="dropdown-item report-export-current" data-export-mode="{{ $mode }}">Current Page</button></li>
                <li><button type="button" class="dropdown-item report-export-all" data-export-mode="{{ $mode }}">All Results</button></li>
                <li><button type="button" class="dropdown-item report-export-choose" data-export-mode="{{ $mode }}">Choose Pages…</button></li>
            </ul>
        </div>
    @endforeach
</div>
