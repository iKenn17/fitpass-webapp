{{-- Expects $paginator (LengthAwarePaginator). Styled to match the portal instead of Laravel's Tailwind default. --}}
@if ($paginator->total() > 0)
    <div class="pager">
        <span>Showing {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }}</span>

        @if ($paginator->hasPages())
            <div class="pager-links">
                @if ($paginator->previousPageUrl())
                    <a href="{{ $paginator->previousPageUrl() }}">&larr; Prev</a>
                @else
                    <span class="off">&larr; Prev</span>
                @endif

                <span>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

                @if ($paginator->nextPageUrl())
                    <a href="{{ $paginator->nextPageUrl() }}">Next &rarr;</a>
                @else
                    <span class="off">Next &rarr;</span>
                @endif
            </div>
        @endif
    </div>
@endif
