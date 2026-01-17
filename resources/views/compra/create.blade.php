@extends('layouts.app')

@section('title','Realizar compra')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    .card-custom {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
</style>
@endpush

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1>Crear Compra</h1>
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
            <!-- Left Column: Products -->
            <div class="col-lg-8">
                <div class="card card-custom mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Detalle de Productos</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add Product Form -->
                        <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label for="producto_id" class="form-label">Producto</label>
                                <select id="producto_id" class="form-control selectpicker" data-live-search="true" title="Buscar producto...">
                                    @foreach ($productos as $item)
                                    <option value="{{$item->id}}">{{$item->nombre_completo}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" id="cantidad" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label for="precio_compra" class="form-label">Costo Unitario</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="precio_compra" class="form-control" step="0.1" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_vencimiento" class="form-label">Vencimiento</label>
                                <input type="date" id="fecha_vencimiento" class="form-control">
                            </div>
                            <div class="col-md-3 ms-auto">
                                <button id="btn_agregar" class="btn btn-add w-100" type="button">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table id="tabla_detalle" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cant.</th>
                                        <th>Costo</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows added via JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold fs-5">
                                        <td colspan="3" class="text-end">Total:</td>
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

            <!-- Right Column: Info -->
            <div class="col-lg-4">
                <div class="card card-custom">
                    <div class="card-header card-header-custom bg-secondary">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Datos de Factura</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="proveedore_id" class="form-label">Proveedor <span class="text-danger">*</span></label>
                            <select name="proveedore_id" id="proveedore_id" required class="form-control selectpicker show-tick" data-live-search="true" title="Seleccionar...">
                                @foreach ($proveedores as $item)
                                <option value="{{$item->id}}">{{$item->nombre_documento}}</option>
                                @endforeach
                            </select>
                            @error('proveedore_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha_hora" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input required type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            @error('fecha_hora') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                             <label for="metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                             <select required name="metodo_pago" id="metodo_pago" class="form-control selectpicker" title="Seleccionar...">
                                 @foreach ($optionsMetodoPago as $item)
                                 <option value="{{$item->value}}">{{$item->name}}</option>
                                 @endforeach
                             </select>
                             @error('metodo_pago') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="comprobante_id" class="form-label">Tipo Comp. <span class="text-danger">*</span></label>
                                <select name="comprobante_id" id="comprobante_id" required class="form-control selectpicker" title="Tipo">
                                    @foreach ($comprobantes as $item)
                                    <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="numero_comprobante" class="form-label">N° Comp.</label>
                                <input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control">
                            </div>
                        </div>

                         <div class="mb-4">
                             <label for="file_comprobante" class="form-label">Adjuntar PDF</label>
                             <input type="file" name="file_comprobante" id="file_comprobante" class="form-control" accept=".pdf">
                         </div>

                         <div class="d-grid gap-2">
                             <button type="submit" class="btn btn-primary btn-lg" id="guardar">
                                 <i class="fas fa-save me-2"></i> Registrar Compra
                             </button>
                             <button type="button" class="btn btn-outline-danger" id="cancelar" onclick="cancelarCompra()">
                                 Cancelar
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
    $(document).ready(function() {
        $('#btn_agregar').click(function() {
            agregarProducto();
        });

        disableButtons();
    });

    //Variables
    let cont = 0;
    let subtotal = [];
    let sumas = 0;
    let total = 0;
    let arrayIdProductos = [];

    function cancelarCompra() {
        $('#tabla_detalle tbody').empty();
        cont = 0;
        subtotal = [];
        sumas = 0;
        total = 0;
        arrayIdProductos = [];
        updateTotals();
        limpiarCampos();
        disableButtons();
    }

    function disableButtons() {
        if (total == 0) {
            $('#guardar').prop('disabled', true);
        } else {
            $('#guardar').prop('disabled', false);
        }
    }

    function agregarProducto() {
        let idProducto = $('#producto_id').val();
        let textProducto = $('#producto_id option:selected').text();
        let cantidad = $('#cantidad').val();
        let precioCompra = $('#precio_compra').val();
        let fechaVencimiento = $('#fecha_vencimiento').val();

        if (textProducto != '' && textProducto != undefined && cantidad != '' && precioCompra != '') {
            // Extract simplified name if possible, otherwise use full text
            let nameProducto = textProducto; 
            try {
                 nameProducto = textProducto.split('-')[1].trim(); 
            } catch(e) {}
            
            // Simple validation
            if (parseInt(cantidad) > 0 && parseFloat(precioCompra) > 0) {
                if (!arrayIdProductos.includes(idProducto)) {
                    subtotal[cont] = round(cantidad * precioCompra);
                    sumas = round(sumas + subtotal[cont]);
                    total = round(sumas);

                    let fila = '<tr id="fila' + cont + '">' +
                        '<td><input type="hidden" name="arrayidproducto[]" value="' + idProducto + '">' + textProducto + '</td>' +
                        '<td><input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' + cantidad + '</td>' +
                        '<td><input type="hidden" name="arraypreciocompra[]" value="' + precioCompra + '">$' + precioCompra + '</td>' +
                        '<td><input type="hidden" name="arrayfechavencimiento[]" value="' + fechaVencimiento + '">$' + subtotal[cont] + '</td>' +
                        '<td><button class="btn btn-sm btn-danger" type="button" onClick="eliminarProducto(' + cont + ', ' + idProducto + ')"><i class="fas fa-trash"></i></button></td>' +
                        '</tr>';

                    $('#tabla_detalle tbody').append(fila);
                    limpiarCampos();
                    cont++;
                    updateTotals();
                    arrayIdProductos.push(idProducto);
                    disableButtons();
                } else {
                    showModal('Este producto ya está en la lista. Elimínalo para modificarlo.', 'warning');
                }
            } else {
                showModal('Cantidad y Costo deben ser mayores a 0', 'warning');
            }
        } else {
            showModal('Por favor completa los campos del producto', 'warning');
        }
    }

    function eliminarProducto(indice, idProducto) {
        sumas -= round(subtotal[indice]);
        total = round(sumas);
        $('#fila' + indice).remove();
        let index = arrayIdProductos.indexOf(idProducto.toString());
        if (index > -1) {
            arrayIdProductos.splice(index, 1);
        }
        updateTotals();
        disableButtons();
    }

    function updateTotals() {
        $('#total').html(total);
        $('#inputTotal').val(total);
        $('#inputSubtotal').val(total); // Assuming subtotal = total as tax is 0
    }

    function limpiarCampos() {
        $('#producto_id').selectpicker('val', '');
        $('#cantidad').val('');
        $('#precio_compra').val('');
        $('#fecha_vencimiento').val('');
    }

    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) return signo * Math.round(num);
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }

    function showModal(message, icon = 'error') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: icon,
            title: message
        });
    }
</script>
@endpush


