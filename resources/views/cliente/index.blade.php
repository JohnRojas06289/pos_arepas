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
                <h3>{{ $item->persona->razon_social }}</h3>
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
                        onclick="confirmDelete({{$item->id}})"
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


