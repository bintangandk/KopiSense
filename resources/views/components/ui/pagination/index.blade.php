@props(['items', 'id' => null])

<div class="card-body pt-3">
    <div class="d-flex justify-content-end">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" {{ $id ? 'id=' . $id : '' }}>
                {{-- Previous Button --}}
                <li class="page-item {{ !$items->onFirstPage() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $items->previousPageUrl() ?? 'javascript:void(0);' }}">
                        <i class="tf-icon bx bx-chevron-left"></i> Previous
                    </a>
                </li>

                {{-- Page Numbers --}}
                @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $items->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Next Button --}}
                <li class="page-item {{ $items->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $items->nextPageUrl() ?? 'javascript:void(0);' }}">
                        Next <i class="tf-icon bx bx-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
