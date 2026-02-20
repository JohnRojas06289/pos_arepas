@extends('layouts.app')

@section('title','Clientes')

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')

<div class="container-fluid px-2">
    <!-- Page Header -->
    <div class="page-header" style="background: var(--color-primary); color: white;">
        <h1 style="color: white;"><i class="fas fa-users"></i> Clientes</h1>
        @can('crear-cliente')
        <a href="{{route('clientes.create')}}">
            <button type="button" class="btn-action-large btn-success">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Cliente</span>
            </button>
        </a>
        @endcan
    </div>

    <!-- Search Bar -->
    <div class="search-bar-large">
        <input type="text" id="searchClients" placeholder="Buscar cliente por nombre o documento..." onkeyup="searchClients()">
        <button onclick="document.getElementById('searchClients').value = ''; searchClients();">
            <i class="fas fa-times me-2"></i> Limpiar
        </button>
    </div>

    <!-- Clients List -->
    <div id="clientsList">
        @forelse ($clientes as $item)
        <div class="item-card" data-search="{{ strtolower($item->persona->razon_social . ' ' . $item->persona->numero_documento) }}">
            <!-- Client Icon -->
            <div class="item-image d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);">
                <i class="fas fa-user fa-3x text-white"></i>
            </div>

            <!-- Client Info -->
            <div class="item-info">
                <div class="d-flex align-items-center gap-2">
                    <h3>{{ $item->persona->razon_social }}</h3>
                    @if($item->tipo_cliente == 'fiado')
                    <span class="badge bg-warning text-dark">Crédito</span>
                    @php $deuda = $item->getSaldoPendiente(); @endphp
                    @if($deuda > 0)
                        <span class="badge bg-danger ms-2">Deuda: ${{number_format($deuda, 0)}}</span>
                    @else
                        <span class="badge bg-success ms-2">Al día</span>
                    @endif
                @elseif($item->tipo_cliente == 'admin')
                    <span class="badge bg-info text-dark">Consumo Interno</span>
                @else
                    <span class="badge bg-success">Contado</span>
                @endif
                </div>
                <div class="d-flex gap-4 mt-2">
                    <span class="text-muted">
                        <i class="fas fa-id-card me-1"></i>
                        <strong>{{ $item->persona->tipo_documento }}:</strong> {{ $item->persona->numero_documento }}
                    </span>
                    @if($item->persona->direccion)
                    <span class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $item->persona->direccion }}
                    </span>
                    @endif
                </div>
                <div class="d-flex gap-4 mt-2">
                    @if($item->persona->telefono)
                    <span class="text-muted">
                        <i class="fas fa-phone me-1"></i>
                        {{ $item->persona->telefono }}
                    </span>
                    @endif
                    @if($item->persona->email)
                    <span class="text-muted">
                        <i class="fas fa-envelope me-1"></i>
                        {{ $item->persona->email }}
                    </span>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="item-actions">
                @if($item->tipo_cliente == 'fiado' && $item->getSaldoPendiente() > 0)
                <button class="btn-icon-large btn-primary" 
                        data-bs-toggle="modal" 
                        data-bs-target="#pagarModal-{{$item->id}}"
                        title="Registrar Abono">
                    <i class="fas fa-hand-holding-dollar"></i>
                </button>
                @endif

                @can('ver-cliente')
                <button class="btn-icon-large btn-view" 
                        data-bs-toggle="modal" 
                        data-bs-target="#verModal-{{$item->id}}"
                        title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>
                @endcan

                @can('editar-cliente')
                <a href="{{route('clientes.edit',['cliente' => $item])}}">
                    <button class="btn-icon-large btn-edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                </a>
                @endcan

                @can('eliminar-cliente')
                <button class="btn-icon-large btn-delete" 
                        onclick="confirmDelete('{{$item->id}}')"
                        title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
                <form action="{{ route('clientes.destroy',['cliente'=>$item->id]) }}" 
                      method="post" 
                      id="delete-form-{{$item->id}}" 
                      style="display: none;">
                    @method('DELETE')
                    @csrf
                </form>
                @endcan
            </div>
        </div>

        @if($item->tipo_cliente == 'fiado')
        <!-- Modal Pagar Deuda -->
        <div class="modal fade" id="pagarModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h1 class="modal-title fs-5">Registrar Abono - {{$item->persona->razon_social}}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('clientes.pagarDeuda', $item) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Deuda Actual:</label>
                                <div class="fs-4 text-danger">${{ number_format($item->getSaldoPendiente(), 0) }}</div>
                            </div>
                            <div class="mb-3">
                                <label for="monto-{{$item->id}}" class="form-label">Monto a Abonar</label>
                                <input type="number" class="form-control" name="monto" id="monto-{{$item->id}}" required min="1" max="{{$item->getSaldoPendiente()}}">
                            </div>
                            <div class="mb-3">
                                <label for="metodo-{{$item->id}}" class="form-label">Método de Pago</label>
                                <select class="form-select" name="metodo_pago" id="metodo-{{$item->id}}" required>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TARJETA">Tarjeta</option>
                                    <option value="NEQUI">Nequi</option>
                                    <option value="DAVIPLATA">Daviplata</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Registrar Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal Ver Cliente -->
        <div class="modal fade" id="verModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%); color: white;">
                        <h1 class="modal-title fs-4">
                            <i class="fas fa-user me-2"></i>
                            Información del Cliente
                        </h1>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group-large">
                                    <label>Nombre / Razón Social</label>
                                    <div class="p-3 bg-light rounded">{{ $item->persona->razon_social }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-large">
                                    <label>Documento</label>
                                    <div class="p-3 bg-light rounded">
                                        {{ $item->persona->tipo_documento }}: {{ $item->persona->numero_documento }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-large">
                                    <label>Teléfono</label>
                                    <div class="p-3 bg-light rounded">{{ $item->persona->telefono ?? 'No registrado' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-large">
                                    <label>Email</label>
                                    <div class="p-3 bg-light rounded">{{ $item->persona->email ?? 'No registrado' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group-large">
                                    <label>Dirección</label>
                                    <div class="p-3 bg-light rounded">{{ $item->persona->direccion ?? 'No registrada' }}</div>
                                </div>
                            </div>
                        </div>

                        @if($item->tipo_cliente == 'fiado')
                        <!-- Sección Detalle de Deuda -->
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Detalle de Deuda</h5>
                        @php
                            $ventasPendientes = $item->ventas()->whereRaw('"pagado" = false')->orderBy('created_at', 'desc')->get();
                            $totalDeuda = $ventasPendientes->sum('saldo_pendiente');
                        @endphp
                        @if($ventasPendientes->count() > 0)
                        <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                            <div>
                                <strong>Deuda Total: ${{ number_format($totalDeuda, 0) }}</strong>
                                <span class="ms-2 text-muted">({{ $ventasPendientes->count() }} {{ $ventasPendientes->count() == 1 ? 'venta' : 'ventas' }} pendientes)</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th class="text-end">Total Venta</th>
                                        <th class="text-end">Saldo Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventasPendientes as $vp)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($vp->fecha_hora)->format('d/m/Y H:i') }}</td>
                                        <td><span class="badge bg-secondary">{{ $vp->numero_comprobante }}</span></td>
                                        <td class="text-end">${{ number_format($vp->total, 0) }}</td>
                                        <td class="text-end fw-bold text-danger">${{ number_format($vp->saldo_pendiente, 0) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-warning">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total Deuda:</td>
                                        <td class="text-end fw-bold text-danger fs-5">${{ number_format($totalDeuda, 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Este cliente no tiene deudas pendientes.
                        </div>
                        @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-modern-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @empty
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No hay clientes registrados</h3>
            <p>Comienza agregando tu primer cliente</p>
            @can('crear-cliente')
            <a href="{{route('clientes.create')}}">
                <button class="btn-action-large btn-success">
                    <i class="fas fa-user-plus"></i>
                    <span>Crear Primer Cliente</span>
                </button>
            </a>
            @endcan
        </div>
        @endforelse
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="empty-state" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>No se encontraron clientes</h3>
        <p>Intenta con otro término de búsqueda</p>
    </div>
</div>

@endsection

@push('js')
<script>
    function searchClients() {
        const searchTerm = document.getElementById('searchClients').value.toLowerCase();
        const clients = document.querySelectorAll('.item-card');
        let visibleCount = 0;

        clients.forEach(client => {
            const searchData = client.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                client.style.display = 'flex';
                visibleCount++;
            } else {
                client.style.display = 'none';
            }
        });

        document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger btn-lg me-2',
                cancelButton: 'btn btn-secondary btn-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush


