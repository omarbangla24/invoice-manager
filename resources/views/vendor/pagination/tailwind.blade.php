@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="load-more-nav">
        @if ($paginator->hasMorePages())
            <button 
                type="button" 
                class="btn load-more-btn" 
                data-next-url="{{ $paginator->nextPageUrl() }}"
                data-current-page="{{ $paginator->currentPage() }}"
            >
                Load More
            </button>
        @else
            <p class="load-more-complete">All items loaded</p>
        @endif
    </nav>
@endif
