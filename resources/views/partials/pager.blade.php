{{-- Phan trang toi gian theo phong cach Classical: $p = paginator --}}
@if ($p->hasPages())
    <div style="display: flex; gap: var(--space-3); align-items: center; justify-content: center; padding-top: var(--space-2)">
        @if ($p->onFirstPage())
            <span class="btn btn-secondary btn-xs" style="opacity: .4; cursor: default">← Trước</span>
        @else
            <a class="btn btn-secondary btn-xs" href="{{ $p->previousPageUrl() }}">← Trước</a>
        @endif
        <span class="meta">Trang {{ $p->currentPage() }} / {{ $p->lastPage() }}</span>
        @if ($p->hasMorePages())
            <a class="btn btn-secondary btn-xs" href="{{ $p->nextPageUrl() }}">Sau →</a>
        @else
            <span class="btn btn-secondary btn-xs" style="opacity: .4; cursor: default">Sau →</span>
        @endif
    </div>
@endif
