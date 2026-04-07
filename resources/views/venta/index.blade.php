@extends('layouts.app')

@section('title','Ventas')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Ventas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    @can('crear-venta')
    <div class="mb-4">
        <a href="{{route('pos.index')}}">
            <button type="button" class="btn btn-primary">Añadir nuevo registro</button>
        </a>
    </div>
    @endcan

    {{-- Filtro de rango de fechas --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('ventas.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0 small">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">Filtrar</button>
                    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Últimos 90 días</a>
                </div>
                <div class="col-auto ms-auto text-muted small align-self-center">
                    Mostrando {{ $ventas->count() }} venta(s) del {{ $desde }} al {{ $hasta }}
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla ventas
        </div>
        <div class="card-body table-responsive">
            <table id="datatablesSimple" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th class="d-none d-md-table-cell">Fecha y hora</th>
                        <th class="d-none d-lg-table-cell">Vendedor</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $item)
                    <tr>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->comprobante?->tipo_comprobante ?? 'N/A'}}</p>
                            <p class="text-muted mb-0">{{$item->numero_comprobante}}</p>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{ ucfirst($item->cliente?->persona?->tipo_persona ?? 'Cliente') }}</p>
                            <p class="text-muted mb-0">{{$item->cliente?->persona?->razon_social ?? 'Cliente general'}}</p>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <div class="row-not-space">
                                <p class="fw-semibold mb-1">
                                    <span class="m-1"><i class="fa-solid fa-calendar-days"></i>
                                    </span>{{$item->fecha}}
                                </p>
                                <p class="fw-semibold mb-0">
                                    <span class="m-1"><i class="fa-solid fa-clock"></i>
                                    </span>{{$item->hora}}
                                </p>
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            {{$item->user?->name ?? 'N/A'}}
                        </td>
                        <td>
                            {{$item->total}}
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                                @can('mostrar-venta')
                                <form action="{{route('ventas.show', ['venta'=> $item]) }}" method="get">
                                    <button type="submit" class="btn btn-success">
                                        Ver
                                    </button>
                                </form>
                                @endcan

                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verPDFModal-{{$item->id}}">
                                    PDF
                                </button>

                            </div>
                        </td>

                    </tr>

                    <!-- Modal -->
                    <div class="modal fade" id="verPDFModal-{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">PDF de la venta</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <iframe src="{{ route('export.pdf-comprobante-venta', ['id' => Crypt::encrypt($item->id)]) }}" style="width: 100%; height:500px;" frameborder="0"></iframe>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('js')
<script src="{{ asset('js/simple-datatables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush


