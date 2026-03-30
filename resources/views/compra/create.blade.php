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
                        <!-- Scan Invoice Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="abrirScanModal()">
                                <i class="fas fa-camera me-2"></i>Escanear Factura con IA
                            </button>
                        </div>

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
<!-- Modal Escanear Factura -->
<div class="modal fade" id="scanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-camera me-2"></i>Escanear Factura con IA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Upload area -->
                <div id="scanUploadArea" class="text-center py-3">
                    <div class="d-flex gap-2 justify-content-center mb-3">
                        <label for="scanImagenCamara" class="btn btn-outline-primary">
                            <i class="fas fa-camera me-1"></i>Tomar Foto
                            <input type="file" id="scanImagenCamara" accept="image/*" capture="environment" style="display:none" onchange="previewImagen(this)">
                        </label>
                        <label for="scanImagenGaleria" class="btn btn-outline-secondary">
                            <i class="fas fa-image me-1"></i>Subir de Galería
                            <input type="file" id="scanImagenGaleria" accept="image/*" style="display:none" onchange="previewImagen(this)">
                        </label>
                    </div>
                    <div id="scanPreview" style="display:none;" class="mb-3">
                        <img id="scanPreviewImg" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #ddd;">
                        <div class="mt-2">
                            <button type="button" class="btn btn-success" id="btnProcesar" onclick="procesarFactura()">
                                <i class="fas fa-magic me-1"></i>Procesar con IA
                            </button>
                        </div>
                    </div>
                    <div id="scanLoading" style="display:none;" class="py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Analizando factura...</p>
                    </div>
                </div>

                <!-- Results -->
                <div id="scanResultados" style="display:none;">
                    <h6 class="mb-2 fw-bold">Productos detectados:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="scanTablaResultados">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre en factura</th>
                                    <th>Producto del sistema</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio unit.</th>
                                    <th class="text-center">Agregar</th>
                                </tr>
                            </thead>
                            <tbody id="scanTbody"></tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-success" onclick="agregarTodosDetectados()">
                            <i class="fas fa-check-double me-1"></i>Agregar todos
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetScan()">
                            <i class="fas fa-redo me-1"></i>Escanear otra
                        </button>
                    </div>
                </div>

                <div id="scanError" style="display:none;" class="alert alert-danger mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
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
        Toast.fire({ icon: icon, title: message });
    }

    // ── Scan Factura ──────────────────────────────────────────────────────
    let scanModal = null;
    let scanFile  = null;

    function abrirScanModal() {
        if (!scanModal) scanModal = new bootstrap.Modal(document.getElementById('scanModal'));
        resetScan();
        scanModal.show();
    }

    function previewImagen(input) {
        if (!input.files || !input.files[0]) return;
        scanFile = input.files[0];
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('scanPreviewImg').src = e.target.result;
            document.getElementById('scanPreview').style.display = 'block';
            document.getElementById('scanResultados').style.display = 'none';
            document.getElementById('scanError').style.display = 'none';
        };
        reader.readAsDataURL(scanFile);
    }

    function procesarFactura() {
        if (!scanFile) return;
        document.getElementById('scanPreview').style.display = 'none';
        document.getElementById('scanLoading').style.display = 'block';
        document.getElementById('scanError').style.display = 'none';

        const formData = new FormData();
        formData.append('imagen', scanFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("compras.scan-factura") }}', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                document.getElementById('scanLoading').style.display = 'none';
                if (data.error) {
                    document.getElementById('scanError').textContent = data.error;
                    document.getElementById('scanError').style.display = 'block';
                    document.getElementById('scanPreview').style.display = 'block';
                    return;
                }
                renderScanResultados(data.items || []);
            })
            .catch(() => {
                document.getElementById('scanLoading').style.display = 'none';
                document.getElementById('scanError').textContent = 'Error de conexión. Intente de nuevo.';
                document.getElementById('scanError').style.display = 'block';
                document.getElementById('scanPreview').style.display = 'block';
            });
    }

    // Productos disponibles para el select del scan
    const productosDisponibles = @json($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre_completo]));

    function renderScanResultados(items) {
        const tbody = document.getElementById('scanTbody');
        tbody.innerHTML = '';

        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No se detectaron productos</td></tr>';
            document.getElementById('scanResultados').style.display = 'block';
            return;
        }

        items.forEach((item, i) => {
            const optionsHtml = productosDisponibles.map(p =>
                `<option value="${p.id}" ${p.id === item.producto_id ? 'selected' : ''}>${p.nombre}</option>`
            ).join('');

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><small class="text-muted">${item.nombre_factura ?? '-'}</small></td>
                <td>
                    <select class="form-select form-select-sm scan-producto-select" data-idx="${i}" style="min-width:160px;">
                        <option value="">-- Sin coincidencia --</option>
                        ${optionsHtml}
                    </select>
                </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center scan-cantidad" value="${item.cantidad ?? 1}" min="1" style="width:70px;margin:auto;">
                </td>
                <td class="text-end">
                    <input type="number" class="form-control form-control-sm text-end scan-precio" value="${item.precio_unitario ?? 0}" min="0" step="0.01" style="width:90px;margin-left:auto;">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-success" onclick="agregarDescanScan(${i})">
                        <i class="fas fa-plus"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('scanResultados').style.display = 'block';
    }

    function agregarDescanScan(idx) {
        const rows = document.querySelectorAll('#scanTbody tr');
        const row  = rows[idx];
        if (!row) return;

        const productoId  = row.querySelector('.scan-producto-select').value;
        const productoNom = row.querySelector('.scan-producto-select option:checked').text;
        const cantidad    = row.querySelector('.scan-cantidad').value;
        const precio      = row.querySelector('.scan-precio').value;

        if (!productoId) { showModal('Selecciona un producto del sistema', 'warning'); return; }
        if (!cantidad || parseInt(cantidad) <= 0) { showModal('La cantidad debe ser mayor a 0', 'warning'); return; }
        if (!precio  || parseFloat(precio)  <= 0) { showModal('El precio debe ser mayor a 0', 'warning'); return; }

        if (arrayIdProductos.includes(productoId)) {
            showModal('Este producto ya está en la lista', 'warning'); return;
        }

        subtotal[cont] = round(cantidad * precio);
        sumas = round(sumas + subtotal[cont]);
        total = round(sumas);

        const fila = '<tr id="fila' + cont + '">' +
            '<td><input type="hidden" name="arrayidproducto[]" value="' + productoId + '">' + productoNom + '</td>' +
            '<td><input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' + cantidad + '</td>' +
            '<td><input type="hidden" name="arraypreciocompra[]" value="' + precio + '">$' + precio + '</td>' +
            '<td><input type="hidden" name="arrayfechavencimiento[]" value="">$' + subtotal[cont] + '</td>' +
            '<td><button class="btn btn-sm btn-danger" type="button" onClick="eliminarProducto(' + cont + ', \'' + productoId + '\')"><i class="fas fa-trash"></i></button></td>' +
            '</tr>';

        $('#tabla_detalle tbody').append(fila);
        cont++;
        updateTotals();
        arrayIdProductos.push(productoId);
        disableButtons();
        showModal(productoNom + ' agregado', 'success');
    }

    function agregarTodosDetectados() {
        const rows = document.querySelectorAll('#scanTbody tr');
        let agregados = 0;
        rows.forEach((row, idx) => {
            const productoId = row.querySelector('.scan-producto-select')?.value;
            if (!productoId || arrayIdProductos.includes(productoId)) return;
            agregarDescanScan(idx);
            agregados++;
        });
        if (agregados > 0) {
            bootstrap.Modal.getInstance(document.getElementById('scanModal')).hide();
        }
    }

    function resetScan() {
        scanFile = null;
        document.getElementById('scanPreview').style.display    = 'none';
        document.getElementById('scanLoading').style.display    = 'none';
        document.getElementById('scanResultados').style.display = 'none';
        document.getElementById('scanError').style.display      = 'none';
        document.getElementById('scanPreviewImg').src           = '';
        document.getElementById('scanImagenCamara').value       = '';
        document.getElementById('scanImagenGaleria').value      = '';
        document.getElementById('scanTbody').innerHTML          = '';
    }
</script>
@endpush


