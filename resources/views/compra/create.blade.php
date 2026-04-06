@extends('layouts.app')

@section('title', 'Realizar compra')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    .card-custom {
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .card-header-custom {
        background: var(--color-primary);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }

    .btn-add {
        background: var(--color-secondary);
        color: white;
        font-weight: bold;
    }

    .input-group-text {
        background-color: #f8f9fa;
    }

    .purchase-helper {
        background: var(--color-primary-subtle);
        border: 1px solid rgba(200, 85, 61, 0.18);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        color: var(--text-primary);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .empty-detail-state {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-secondary);
    }

    .empty-detail-state i {
        display: block;
        font-size: 2rem;
        margin-bottom: 0.75rem;
        opacity: 0.45;
    }

    .line-subtotal {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--color-success);
    }

    .line-input {
        min-width: 90px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4 flex-wrap gap-2">
        <div>
            <h1>Crear compra</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </div>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="{{ route('compras.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-custom mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Detalle de productos</h5>
                    </div>
                    <div class="card-body">
                        <div class="purchase-helper">
                            Agrega productos y, si te equivocas, corrige la cantidad, el costo o el vencimiento directamente en la tabla.
                        </div>

                        <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label for="producto_id" class="form-label">Producto</label>
                                <select id="producto_id" class="form-control selectpicker" data-live-search="true" title="Buscar producto...">
                                    @foreach ($productos as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre_completo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" id="cantidad" class="form-control" placeholder="0" min="1">
                            </div>
                            <div class="col-md-2">
                                <label for="precio_compra" class="form-label">Costo unitario</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="precio_compra" class="form-control" step="0.01" min="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="fecha_vencimiento" class="form-label">Vencimiento</label>
                                <input type="date" id="fecha_vencimiento" class="form-control">
                            </div>
                            <div class="col-md-3 ms-auto">
                                <button id="btn_agregar" class="btn btn-add w-100" type="button">
                                    <i class="fas fa-plus me-1"></i> Agregar al detalle
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tabla_detalle" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th style="width:110px;">Cantidad</th>
                                        <th style="width:150px;">Costo</th>
                                        <th style="width:160px;">Vencimiento</th>
                                        <th style="width:140px;">Subtotal</th>
                                        <th style="width:90px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleBody">
                                    <tr id="emptyDetailRow">
                                        <td colspan="6" class="empty-detail-state">
                                            <i class="fas fa-cart-plus"></i>
                                            Aún no has agregado productos a esta compra.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold fs-5">
                                        <td colspan="4" class="text-end">Total:</td>
                                        <td colspan="2">
                                            $ <span id="total">0</span>
                                            <input type="hidden" name="total" value="0" id="inputTotal">
                                            <input type="hidden" name="subtotal" value="0" id="inputSubtotal">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-custom">
                    <div class="card-header card-header-custom bg-secondary">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Datos de factura</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="proveedore_id" class="form-label">Proveedor <span class="text-danger">*</span></label>
                            <select name="proveedore_id" id="proveedore_id" required class="form-control selectpicker show-tick" data-live-search="true" title="Seleccionar...">
                                @foreach ($proveedores as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre_documento }}</option>
                                @endforeach
                            </select>
                            @error('proveedore_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha_hora" class="form-label">Fecha y hora <span class="text-danger">*</span></label>
                            <input required type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            @error('fecha_hora') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                             <label for="metodo_pago" class="form-label">Método de pago <span class="text-danger">*</span></label>
                             <select required name="metodo_pago" id="metodo_pago" class="form-control selectpicker" title="Seleccionar...">
                                 @foreach ($optionsMetodoPago as $item)
                                 <option value="{{ $item->value }}">{{ $item->name }}</option>
                                 @endforeach
                             </select>
                             @error('metodo_pago') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="comprobante_id" class="form-label">Tipo comp. <span class="text-danger">*</span></label>
                                <select name="comprobante_id" id="comprobante_id" required class="form-control selectpicker" title="Tipo">
                                    @foreach ($comprobantes as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="numero_comprobante" class="form-label">N.° comp.</label>
                                <input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="file_comprobante" class="form-label">Adjuntar PDF</label>
                            <input type="file" name="file_comprobante" id="file_comprobante" class="form-control" accept=".pdf">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="guardar">
                                <i class="fas fa-save me-2"></i> Registrar compra
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="cancelar" onclick="cancelarCompra()">
                                Limpiar detalle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    let lineasCompra = [];

    $(document).ready(function () {
        $('#btn_agregar').on('click', agregarProducto);
        $('#cantidad, #precio_compra').on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                agregarProducto();
            }
        });

        actualizarDetalle();
    });

    function cancelarCompra() {
        lineasCompra = [];
        actualizarDetalle();
        limpiarCampos();
    }

    function agregarProducto() {
        let idProducto = $('#producto_id').val();
        let textProducto = $('#producto_id option:selected').text();
        let cantidad = parseInt($('#cantidad').val(), 10);
        let precioCompra = parseFloat($('#precio_compra').val());
        let fechaVencimiento = $('#fecha_vencimiento').val();

        if (!idProducto || !textProducto) {
            showModal('Selecciona un producto para continuar.', 'warning');
            return;
        }

        if (!Number.isInteger(cantidad) || cantidad < 1) {
            showModal('La cantidad debe ser mayor a 0.', 'warning');
            return;
        }

        if (!Number.isFinite(precioCompra) || precioCompra <= 0) {
            showModal('El costo unitario debe ser mayor a 0.', 'warning');
            return;
        }

        const existente = lineasCompra.find(linea => linea.productoId === idProducto);

        if (existente) {
            existente.cantidad += cantidad;
            existente.precioCompra = precioCompra;
            existente.fechaVencimiento = fechaVencimiento;
            showModal('Producto actualizado en el detalle.', 'success');
        } else {
            lineasCompra.push({
                uid: 'linea-' + Date.now() + '-' + Math.random().toString(16).slice(2),
                productoId: idProducto,
                nombre: textProducto,
                cantidad: cantidad,
                precioCompra: precioCompra,
                fechaVencimiento: fechaVencimiento
            });
        }

        actualizarDetalle();
        limpiarCampos();
    }

    function actualizarLinea(uid, field, value) {
        const linea = lineasCompra.find(item => item.uid === uid);
        if (!linea) {
            return;
        }

        if (field === 'cantidad') {
            const cantidad = parseInt(value, 10);
            linea.cantidad = Number.isInteger(cantidad) && cantidad > 0 ? cantidad : 1;
        }

        if (field === 'precioCompra') {
            const precio = parseFloat(value);
            linea.precioCompra = Number.isFinite(precio) && precio > 0 ? precio : 0.01;
        }

        if (field === 'fechaVencimiento') {
            linea.fechaVencimiento = value;
        }

        actualizarDetalle();
    }

    function eliminarProducto(uid) {
        lineasCompra = lineasCompra.filter(item => item.uid !== uid);
        actualizarDetalle();
    }

    function actualizarDetalle() {
        const body = document.getElementById('detalleBody');
        const total = lineasCompra.reduce((acc, item) => acc + (item.cantidad * item.precioCompra), 0);

        if (lineasCompra.length === 0) {
            body.innerHTML = '' +
                '<tr id="emptyDetailRow">' +
                    '<td colspan="6" class="empty-detail-state">' +
                        '<i class="fas fa-cart-plus"></i>' +
                        'Aún no has agregado productos a esta compra.' +
                    '</td>' +
                '</tr>';
        } else {
            body.innerHTML = lineasCompra.map(item => {
                const subtotal = item.cantidad * item.precioCompra;

                return '' +
                    '<tr id="' + item.uid + '">' +
                        '<td>' +
                            '<div class="fw-semibold">' + escapeHtml(item.nombre) + '</div>' +
                            '<input type="hidden" name="arrayidproducto[]" value="' + item.productoId + '">' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" class="form-control line-input" min="1" value="' + item.cantidad + '"' +
                                ' onchange="actualizarLinea(\'' + item.uid + '\', \'cantidad\', this.value)">' +
                            '<input type="hidden" name="arraycantidad[]" value="' + item.cantidad + '">' +
                        '</td>' +
                        '<td>' +
                            '<div class="input-group">' +
                                '<span class="input-group-text">$</span>' +
                                '<input type="number" class="form-control line-input" min="0.01" step="0.01" value="' + item.precioCompra + '"' +
                                    ' onchange="actualizarLinea(\'' + item.uid + '\', \'precioCompra\', this.value)">' +
                            '</div>' +
                            '<input type="hidden" name="arraypreciocompra[]" value="' + item.precioCompra + '">' +
                        '</td>' +
                        '<td>' +
                            '<input type="date" class="form-control line-input" value="' + (item.fechaVencimiento || '') + '"' +
                                ' onchange="actualizarLinea(\'' + item.uid + '\', \'fechaVencimiento\', this.value)">' +
                            '<input type="hidden" name="arrayfechavencimiento[]" value="' + (item.fechaVencimiento || '') + '">' +
                        '</td>' +
                        '<td><span class="line-subtotal">$' + numberFormat(subtotal) + '</span></td>' +
                        '<td>' +
                            '<button class="btn btn-sm btn-danger" type="button" onclick="eliminarProducto(\'' + item.uid + '\')">' +
                                '<i class="fas fa-trash"></i>' +
                            '</button>' +
                        '</td>' +
                    '</tr>';
            }).join('');
        }

        document.getElementById('total').textContent = numberFormat(total);
        document.getElementById('inputTotal').value = total.toFixed(2);
        document.getElementById('inputSubtotal').value = total.toFixed(2);
        document.getElementById('guardar').disabled = lineasCompra.length === 0;
    }

    function limpiarCampos() {
        $('#producto_id').selectpicker('val', '');
        $('#cantidad').val('');
        $('#precio_compra').val('');
        $('#fecha_vencimiento').val('');
    }

    function numberFormat(num) {
        return Number(num || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function showModal(message, icon = 'error') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });

        Toast.fire({
            icon: icon,
            title: message
        });
    }
</script>
@endpush
