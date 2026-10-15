@if($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->total() > 0)
    <div class="card-footer bg-white border-0 px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2 no-print">
        <small class="text-muted">Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }} records</small>
        {{ $paginator->links('pagination::bootstrap-5') }}
    </div>
@endif
