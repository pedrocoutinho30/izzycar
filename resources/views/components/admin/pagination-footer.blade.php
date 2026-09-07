@if ($items->total() > 0)
    <div class="pagination-footer">
        <div class="pagination-footer-info text-muted">
            Mostrando {{ $items->firstItem() }} a {{ $items->lastItem() }}
            de {{ $items->total() }} {{ $label }}
        </div>

        <div class="pagination-footer-links">
            {{ $items->links() }}
        </div>
    </div>

    @push('styles')
    <style>
        .pagination-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        /* A faixa de números de página pode ser mais larga que o ecrã (ex.:
           19 páginas) — faz scroll horizontal só aqui em vez de alargar a
           página toda. */
        .pagination-footer-links {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .pagination-footer-links .pagination {
            margin-bottom: 0;
            flex-wrap: nowrap;
        }

        @media (max-width: 576px) {
            .pagination-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .pagination-footer-info {
                font-size: 0.85rem;
                text-align: center;
            }
        }
    </style>
    @endpush
@endif
