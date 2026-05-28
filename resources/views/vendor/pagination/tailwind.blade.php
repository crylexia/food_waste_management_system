@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" style="display:flex; align-items:center; justify-content:space-between; margin-top:1rem; font-size:14px;">

    <p style="color:#6b7280; margin:0;">
        Showing <strong>{{ $paginator->firstItem() }}</strong>
        to <strong>{{ $paginator->lastItem() }}</strong>
        of <strong>{{ $paginator->total() }}</strong> results
    </p>

    <div style="display:flex; gap:4px; align-items:center;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; color:#9ca3af; cursor:default;">«</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; color:#374151; text-decoration:none;" rel="prev">«</a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:6px 12px; color:#9ca3af;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:6px 12px; border:1px solid #16a34a; border-radius:6px; background:#16a34a; color:white; font-weight:500;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; color:#374151; text-decoration:none;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; color:#374151; text-decoration:none;" rel="next">»</a>
        @else
            <span style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; color:#9ca3af; cursor:default;">»</span>
        @endif

    </div>
</nav>
@endif