@extends('layouts.app')

@section('title','Inventario')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
@endpush

@section('content')

<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Inventario</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item active='true' content="Inventario" />
    </x-breadcrumb.template>



    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <form action="{{ route('inventario.index') }}" method="GET">
                        <label for="categoria_id" class="form-label">Filtrar por Categoría</label>
                        <div class="input-group">
                            <select name="categoria_id" id="categoria_id" class="form-select">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->caracteristica->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 offset-md-4">
                     <label for="searchInput" class="form-label">Buscar</label>
                     <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla inventario
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table-striped fs-6">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock</th>

                        <th>Fecha de Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $item)
                    <tr>
                        <td>
                            {{$item->codigo}}
                        </td>
                        <td>
                             {{$item->nombre}} - Presentación: {{$item->presentacione->sigla}}
                        </td>
                        <td>
                            {{$item->inventario->cantidad ?? 0}}
                        </td>

                        <td>
                            {{$item->inventario?->fecha_vencimiento_format ?? 'N/A'}}
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                @if($item->inventario)
                                    <a href="{{ route('inventario.edit', $item->inventario->id) }}" class="btn btn-warning">Editar</a>
                                    <form action="{{ route('inventario.destroy', $item->inventario->id) }}" method="POST"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar este elemento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary">Sin Inventario</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>



</div>
@endsection

@push('js')
<script src="{{ asset('js/simple-datatables.min.js') }}" type="text/javascript"></script>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        const datatablesSimple = document.getElementById('datatablesSimple');
        if (datatablesSimple) {
            const dataTable = new simpleDatatables.DataTable(datatablesSimple, {
                paging: false,
                searchable: true,
                labels: {
                    placeholder: "Buscar...",
                    perPage: "Registros por página:",
                    noRows: "No se encontraron registros",
                    info: "Mostrando {start} a {end} de {rows} registros",
                    noResults: "No se encontraron resultados para tu búsqueda",
                }
            });

            // Hide default search input container
            // Simple-datatables puts the search input in a div with class 'datatable-search'
            setTimeout(() => {
                const searchWrapper = datatablesSimple.closest('.datatable-wrapper');
                if(searchWrapper) {
                     const defaultSearch = searchWrapper.querySelector('.datatable-search');
                     if(defaultSearch) defaultSearch.style.display = 'none';
                }
            }, 100);

            // Custom search input
            const searchInput = document.getElementById('searchInput');
            if(searchInput){
                searchInput.addEventListener('keyup', function(e) {
                    dataTable.search(e.target.value);
                });
            }
        }
    });
</script>
@endpush


