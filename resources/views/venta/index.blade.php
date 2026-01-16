@extends('layouts.app')

@section('title','Ventas')

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header" style="background: var(--color-primary); color: white;">
        <h1 style="color: white;"><i class="fas fa-cash-register"></i> Historial de Ventas</h1>
        <div class="d-flex gap-3">
            @can('crear-venta')
            <a href="{{route('ventas.create')}}">
                <button type="button" class="btn-action-large btn-success">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Venta</span>
                </button>
            </a>
            <a href="{{ route('export.excel-ventas-all') }}">
                <button type="button" class="btn-action-large btn-info">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Excel</span>
                </button>
            </a>
            @endcan
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-bar-large">
        <input type="text" id="searchSales" placeholder="Buscar por cliente o número de comprobante..." onkeyup="searchSales()">
        <button onclick="document.getElementById('searchSales').value = ''; searchSales();">
            <i class="fas fa-times me-2"></i> Limpiar
        </button>
    </div>

    <!-- Sales List -->
    <div id="salesList">
        @forelse ($ventas as $item)
        <div class="item-card" data-search="{{ strtolower($item->cliente->persona->razon_social . ' ' . $item->numero_comprobante) }}">
            <!-- Sale Icon -->
            <div class="item-image d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <i class="fas fa-receipt fa-3x text-white"></i>
            </div>

            <!-- Sale Info -->
            <div class="item-info">
                <h3>{{ $item->cliente->persona->razon_social }}</h3>
                <p class="price">${{ number_format($item->total, 0, ',', '.') }}</p>
                <div class="d-flex gap-4 mt-2">
                    <span class="text-muted">
                        <i class="fas fa-file-invoice me-1"></i>
                        <strong>{{ $item->comprobante->tipo_comprobante }}:</strong> {{ $item->numero_comprobante }}
                    </span>
                    <span class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $item->fecha }} {{ $item->hora }}
                    </span>
                    <span class="text-muted">
                        <i class="fas fa-user me-1"></i>
                        {{ $item->user->name }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="item-actions">
                @can('mostrar-venta')
                <form action="{{route('ventas.show', ['venta'=> $item]) }}" method="get" class="d-inline">
                    <button type="submit" class="btn-icon-large btn-view" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                </form>
                @endcan

                <a href="{{ route('export.pdf-comprobante-venta',['id' => Crypt::encrypt($item->id)]) }}" target="_blank">
                    <button class="btn-icon-large btn-info" title="Descargar PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </a>
            </div>
        </div>

        @empty
        <div class="empty-state">
            <i class="fas fa-cash-register"></i>
            <h3>No hay ventas registradas</h3>
            <p>Las ventas aparecerán aquí</p>
            @can('crear-venta')
            <a href="{{route('ventas.create')}}">
                <button class="btn-action-large btn-success">
                    <i class="fas fa-plus-circle"></i>
                    <span>Crear Primera Venta</span>
                </button>
            </a>
            @endcan
        </div>
        @endforelse
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="empty-state" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>No se encontraron ventas</h3>
        <p>Intenta con otro término de búsqueda</p>
    </div>
</div>

@endsection

@push('js')
<script>
    function searchSales() {
        const searchTerm = document.getElementById('searchSales').value.toLowerCase();
        const sales = document.querySelectorAll('.item-card');
        let visibleCount = 0;

        sales.forEach(sale => {
            const searchData = sale.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                sale.style.display = 'flex';
                visibleCount++;
            } else {
                sale.style.display = 'none';
            }
        });

        document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
    }
</script>
@endpush
