@extends('layouts.app')

@section('title', 'Registrar Gasto')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1>Registrar Gasto</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('gastos.index') }}">Gastos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </div>
        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header" style="background: var(--color-primary); color: white;">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Datos del Gasto</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('gastos.store') }}" method="POST" enctype="multipart/form-data" id="gasto-form">
                        @csrf

                        {{-- Categoría --}}
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($categorias as $cat)
                                <option value="{{ $cat->value }}" {{ old('categoria') == $cat->value ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                value="{{ old('descripcion') }}"
                                placeholder="Ej: Arriendo mes de abril">
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Fecha (siempre visible) --}}
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="fecha"
                                class="form-control @error('fecha') is-invalid @enderror"
                                value="{{ old('fecha', now()->toDateString()) }}"
                                required>
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Monto (oculto en SURTIDO) --}}
                        <div id="monto-group" class="mb-3">
                            <label for="monto" class="form-label">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" id="monto"
                                    class="form-control @error('monto') is-invalid @enderror"
                                    value="{{ old('monto') }}"
                                    min="1" step="1" placeholder="0"
                                    required>
                            </div>
                            @error('monto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- ══════════════ SECCIÓN SURTIDO ══════════════ --}}
                        <div id="surtido-section" style="display:none;">

                            {{-- Proveedor --}}
                            <div class="mb-3">
                                <label for="proveedore_id" class="form-label">Proveedor</label>
                                <select name="proveedore_id" id="proveedore_id" class="form-select">
                                    <option value="">Sin especificar</option>
                                    @foreach ($proveedores as $prov)
                                    <option value="{{ $prov->id }}" {{ old('proveedore_id') == $prov->id ? 'selected' : '' }}>
                                        {{ $prov->nombre_documento }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Comprobante --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="comprobante_id" class="form-label">Tipo Comprobante</label>
                                    <select name="comprobante_id" id="comprobante_id" class="form-select">
                                        <option value="">Sin especificar</option>
                                        @foreach ($comprobantes as $comp)
                                        <option value="{{ $comp->id }}" {{ old('comprobante_id') == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="numero_comprobante" class="form-label">N° Comprobante</label>
                                    <input type="text" name="numero_comprobante" id="numero_comprobante"
                                        class="form-control"
                                        value="{{ old('numero_comprobante') }}"
                                        placeholder="Ej: FV-001">
                                </div>
                            </div>

                            {{-- Escanear Factura con IA --}}
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary w-100" id="btn-abrir-scanner">
                                    <i class="fas fa-camera me-2"></i> Escanear Factura con IA
                                    <small class="d-block text-muted" style="font-size:.75rem;">Sube una foto de tu factura y la IA extrae los productos automáticamente</small>
                                </button>
                            </div>

                            {{-- Agregar productos --}}
                            <div class="card mb-3">
                                <div class="card-header fw-semibold" style="background:var(--bg-card);">
                                    <i class="fas fa-boxes me-1"></i> Productos del surtido
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 align-items-end mb-3 bg-light p-3 rounded">
                                        <div class="col-md-5">
                                            <label class="form-label">Producto</label>
                                            <select id="s_producto_id" class="form-select">
                                                <option value="">Seleccionar...</option>
                                                @foreach ($productos as $prod)
                                                <option value="{{ $prod->id }}" data-nombre="{{ $prod->nombre }}">
                                                    {{ $prod->nombre_completo }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Cantidad</label>
                                            <input type="number" id="s_cantidad" class="form-control" min="1" step="1" placeholder="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Precio total</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" id="s_precio" class="form-control" min="0" step="1" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Vencimiento</label>
                                            <input type="date" id="s_vencimiento" class="form-control">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" id="s_btn_agregar" class="btn btn-success w-100">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div id="s_alert" class="alert alert-danger py-2 d-none"></div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th class="text-center" style="width:80px">Cant.</th>
                                                    <th class="text-end" style="width:120px">Precio Unit.</th>
                                                    <th class="text-center" style="width:110px">Vencimiento</th>
                                                    <th style="width:50px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="s_tbody">
                                                <tr id="s_empty_row">
                                                    <td colspan="6" class="text-center text-muted py-3">
                                                        <i class="fas fa-box-open me-1"></i> Sin productos agregados
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td colspan="3" class="text-end">Total surtido:</td>
                                                    <td class="text-end" id="s_total_display">$0</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Hidden inputs para arrays de productos --}}
                                    <div id="s_hidden_inputs"></div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                El monto del gasto se calculará automáticamente como la suma total de los productos.
                                <strong>Total: <span id="s_total_info">$0</span></strong>
                            </div>

                        </div>
                        {{-- ══════════════ FIN SURTIDO ══════════════ --}}

                        {{-- Método de pago --}}
                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select">
                                <option value="">Sin especificar</option>
                                @foreach ($optionsMetodoPago as $metodo)
                                <option value="{{ $metodo->value }}" {{ old('metodo_pago') == $metodo->value ? 'selected' : '' }}>
                                    {{ $metodo->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Comprobante (oculto en SURTIDO porque ya tiene su propio campo de comprobante) --}}
                        <div id="comprobante-group" class="mb-3">
                            <label for="comprobante" class="form-label">Comprobante <small class="text-muted">(PDF, JPG, PNG — máx. 4MB)</small></label>
                            <input type="file" name="comprobante" id="comprobante"
                                class="form-control @error('comprobante') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @error('comprobante') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea name="notas" id="notas" rows="3"
                                class="form-control @error('notas') is-invalid @enderror"
                                placeholder="Observaciones adicionales...">{{ old('notas') }}</textarea>
                            @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                                <i class="fas fa-save me-2"></i>Registrar Gasto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Escanear Factura --}}
<div class="modal fade" id="modalScanner" tabindex="-1" aria-labelledby="modalScannerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--color-primary);color:white;">
                <h5 class="modal-title" id="modalScannerLabel">
                    <i class="fas fa-magic me-2"></i> Escanear Factura con IA
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Paso 1: Seleccionar imagen --}}
                <div id="scanner-step-upload">
                    <p class="text-muted mb-3">Toma una foto de tu factura o selecciona una imagen desde tu dispositivo. La IA extraerá los productos automáticamente.</p>
                    <div class="d-flex gap-2 mb-3">
                        <label class="btn btn-outline-secondary flex-fill text-center" for="scanner-input-galeria">
                            <i class="fas fa-images fa-lg d-block mb-1"></i>
                            <span>Galería / Archivo</span>
                            <input type="file" id="scanner-input-galeria" accept="image/jpeg,image/png,image/webp" class="d-none">
                        </label>
                        <label class="btn btn-outline-primary flex-fill text-center" for="scanner-input-camara">
                            <i class="fas fa-camera fa-lg d-block mb-1"></i>
                            <span>Tomar Foto</span>
                            <input type="file" id="scanner-input-camara" accept="image/jpeg,image/png,image/webp" capture="environment" class="d-none">
                        </label>
                    </div>
                    <div id="scanner-preview-container" style="display:none;" class="text-center mb-3">
                        <img id="scanner-preview" src="" alt="Preview" class="img-fluid rounded border" style="max-height:300px;">
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary" id="btn-analizar">
                                <i class="fas fa-robot me-2"></i> Analizar con IA
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="btn-cambiar-imagen">
                                <i class="fas fa-redo me-1"></i> Cambiar imagen
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Paso 2: Analizando --}}
                <div id="scanner-step-loading" style="display:none;" class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                    <p class="fw-semibold">Analizando factura con IA...</p>
                    <small class="text-muted">Esto puede tomar unos segundos</small>
                </div>

                {{-- Paso 3: Resultados --}}
                <div id="scanner-step-results" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="fas fa-list-check me-1"></i> Productos detectados</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-nueva-imagen">
                            <i class="fas fa-redo me-1"></i> Escanear otra
                        </button>
                    </div>
                    <p class="text-muted small mb-2">Revisa y corrige los datos antes de agregar al surtido.</p>
                    <div id="scanner-error-alert" class="alert alert-danger d-none py-2"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px;"><input type="checkbox" id="scanner-check-all" checked></th>
                                    <th>Producto en factura</th>
                                    <th>Producto en sistema</th>
                                    <th class="text-center" style="width:80px;">Cant.</th>
                                    <th class="text-end" style="width:110px;">Precio unit.</th>
                                </tr>
                            </thead>
                            <tbody id="scanner-tbody"></tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 justify-content-end mt-2">
                        <button type="button" class="btn btn-success" id="btn-agregar-seleccionados">
                            <i class="fas fa-cart-plus me-1"></i> Agregar seleccionados al surtido
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    'use strict';

    var categoriaSelect  = document.getElementById('categoria');
    var surtidoSection   = document.getElementById('surtido-section');
    var montoGroup       = document.getElementById('monto-group');
    var comprobanteGroup = document.getElementById('comprobante-group');
    var montoInput       = document.getElementById('monto');
    var btnSubmit        = document.getElementById('btn-submit');

    var sProductoId    = document.getElementById('s_producto_id');
    var tomSelect      = new TomSelect('#s_producto_id', { placeholder: 'Buscar producto...', allowEmptyOption: true });
    var sCantidad      = document.getElementById('s_cantidad');
    var sPrecio        = document.getElementById('s_precio');
    var sVencimiento   = document.getElementById('s_vencimiento');
    var sBtnAgregar    = document.getElementById('s_btn_agregar');
    var sTbody         = document.getElementById('s_tbody');
    var sEmptyRow      = document.getElementById('s_empty_row');
    var sTotalDisplay  = document.getElementById('s_total_display');
    var sTotalInfo     = document.getElementById('s_total_info');
    var sHiddenInputs  = document.getElementById('s_hidden_inputs');
    var sAlert         = document.getElementById('s_alert');

    var productos = [];

    function isSurtido() {
        return categoriaSelect.value === 'SURTIDO';
    }

    function toggleMode() {
        if (isSurtido()) {
            surtidoSection.style.display  = '';
            montoGroup.style.display      = 'none';
            comprobanteGroup.style.display = 'none';
            montoInput.required           = false;
            montoInput.removeAttribute('name');
            btnSubmit.innerHTML = '<i class="fas fa-boxes me-2"></i>Registrar Surtido';
        } else {
            surtidoSection.style.display  = 'none';
            montoGroup.style.display      = '';
            comprobanteGroup.style.display = '';
            montoInput.required           = true;
            montoInput.name               = 'monto';
            btnSubmit.innerHTML = '<i class="fas fa-save me-2"></i>Registrar Gasto';
        }
    }

    function formatCOP(value) {
        return '$' + Math.round(value).toLocaleString('es-CO');
    }

    function calcTotal() {
        return productos.reduce(function (acc, p) {
            return acc + p.precioTotal;
        }, 0);
    }

    function showAlert(msg) {
        sAlert.textContent = msg;
        sAlert.classList.remove('d-none');
        setTimeout(function () { sAlert.classList.add('d-none'); }, 3000);
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    function escapeAttr(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    }

    function renderTable() {
        while (sTbody.firstChild) sTbody.removeChild(sTbody.firstChild);
        sHiddenInputs.innerHTML = '';

        if (productos.length === 0) {
            sTbody.appendChild(sEmptyRow);
        } else {
            productos.forEach(function (p, idx) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(p.nombre) + '</td>' +
                    '<td class="text-center">' + p.cantidad + '</td>' +
                    '<td class="text-end">' + formatCOP(p.cantidad > 0 ? p.precioTotal / p.cantidad : 0) + '</td>' +
                    '<td class="text-center">' + (p.vencimiento || '—') + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" data-remove="' + idx + '">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</td>';
                sTbody.appendChild(tr);

                var precioUnit = p.cantidad > 0 ? p.precioTotal / p.cantidad : 0;
                sHiddenInputs.innerHTML +=
                    '<input type="hidden" name="arrayidproducto[]" value="' + escapeAttr(p.id) + '">' +
                    '<input type="hidden" name="arraycantidad[]" value="' + p.cantidad + '">' +
                    '<input type="hidden" name="arraypreciocompra[]" value="' + precioUnit + '">' +
                    '<input type="hidden" name="arrayfechavencimiento[]" value="' + escapeAttr(p.vencimiento) + '">';
            });

            sTbody.querySelectorAll('[data-remove]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    productos.splice(parseInt(this.dataset.remove), 1);
                    renderTable();
                });
            });
        }

        var total = calcTotal();
        var formatted = formatCOP(total);
        sTotalDisplay.textContent = formatted;
        sTotalInfo.textContent    = formatted;
    }

    // Exponer para el scanner IA
    window.surtidoAgregarProducto = function (p) {
        productos.push(p);
        renderTable();
    };

    sBtnAgregar.addEventListener('click', function () {
        var productoId = sProductoId.value;
        var cantidad   = parseFloat(sCantidad.value);
        var precio     = parseFloat(sPrecio.value);
        var venc       = sVencimiento.value;
        var opt        = sProductoId.options[sProductoId.selectedIndex];
        var nombre     = opt ? (opt.dataset.nombre || opt.text).trim() : '';

        if (!productoId)           { showAlert('Selecciona un producto.'); return; }
        if (!cantidad || cantidad < 1) { showAlert('Ingresa una cantidad válida (mínimo 1).'); return; }
        if (isNaN(precio) || precio < 0) { showAlert('Ingresa un precio válido.'); return; }

        productos.push({ id: productoId, nombre: nombre, cantidad: cantidad, precioTotal: precio, vencimiento: venc });
        renderTable();

        sCantidad.value    = '';
        sPrecio.value      = '';
        sVencimiento.value = '';
        tomSelect.clear();
    });

    document.getElementById('gasto-form').addEventListener('submit', function (e) {
        if (isSurtido() && productos.length === 0) {
            e.preventDefault();
            showAlert('Agrega al menos un producto al surtido antes de guardar.');
        }
    });

    categoriaSelect.addEventListener('change', toggleMode);
    toggleMode(); // init
})();

// ── Scanner IA ────────────────────────────────────────────────────────────
(function () {
    'use strict';

    var modalEl        = document.getElementById('modalScanner');
    var modal          = new bootstrap.Modal(modalEl);
    var stepUpload     = document.getElementById('scanner-step-upload');
    var stepLoading    = document.getElementById('scanner-step-loading');
    var stepResults    = document.getElementById('scanner-step-results');
    var previewCont    = document.getElementById('scanner-preview-container');
    var previewImg     = document.getElementById('scanner-preview');
    var scannerTbody   = document.getElementById('scanner-tbody');
    var errorAlert     = document.getElementById('scanner-error-alert');
    var checkAll       = document.getElementById('scanner-check-all');

    var productosDelSistema = @json($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre_completo ?? $p->nombre]));

    var selectedFile = null;

    document.getElementById('btn-abrir-scanner').addEventListener('click', function () {
        resetScanner();
        modal.show();
    });

    ['scanner-input-galeria', 'scanner-input-camara'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            selectedFile = file;
            var reader = new FileReader();
            reader.onload = function (ev) {
                previewImg.src = ev.target.result;
                previewCont.style.display = '';
            };
            reader.readAsDataURL(file);
        });
    });

    document.getElementById('btn-cambiar-imagen').addEventListener('click', function () {
        resetScanner();
    });

    document.getElementById('btn-nueva-imagen').addEventListener('click', function () {
        resetScanner();
    });

    document.getElementById('btn-analizar').addEventListener('click', function () {
        if (!selectedFile) return;
        mostrarPaso('loading');

        var formData = new FormData();
        formData.append('imagen', selectedFile);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        var controller = new AbortController();
        var timeoutId  = setTimeout(function () { controller.abort(); }, 28000);

        fetch('{{ route("gastos.scan-factura") }}', {
            method: 'POST',
            body: formData,
            signal: controller.signal,
        })
        .then(function (res) {
            clearTimeout(timeoutId);
            if (!res.ok) return res.json().then(function (d) { throw new Error(d.error || 'Error ' + res.status); });
            return res.json();
        })
        .then(function (data) {
            if (data.error) { mostrarPaso('upload'); mostrarError(data.error); return; }
            renderResultados(data.productos || []);
            mostrarPaso('results');
        })
        .catch(function (err) {
            clearTimeout(timeoutId);
            mostrarPaso('upload');
            var msg = err.name === 'AbortError'
                ? 'La solicitud tardó demasiado. Intenta con una imagen más pequeña o de mejor calidad.'
                : 'Error al procesar la imagen: ' + (err.message || 'intenta nuevamente.');
            mostrarError(msg);
        });
    });

    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.scanner-row-check').forEach(function (cb) {
            cb.checked = checkAll.checked;
        });
    });

    document.getElementById('btn-agregar-seleccionados').addEventListener('click', function () {
        var filas = document.querySelectorAll('#scanner-tbody tr');
        var agregados = 0;

        filas.forEach(function (tr) {
            var cb = tr.querySelector('.scanner-row-check');
            if (!cb || !cb.checked) return;

            var select     = tr.querySelector('.scanner-select-producto');
            var inputCant  = tr.querySelector('.scanner-input-cant');
            var inputPrecio= tr.querySelector('.scanner-input-precio');

            var productoId = select ? select.value : '';
            var cantidad   = parseFloat(inputCant.value) || 0;
            var precio     = parseFloat(inputPrecio.value) || 0;
            var nombre     = select && select.value
                ? (select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : tr.querySelector('.scanner-nombre-factura').textContent)
                : tr.querySelector('.scanner-nombre-factura').textContent;

            if (!productoId || cantidad < 1) return;

            // Agregar al carrito principal usando la función global
            window.surtidoAgregarProducto({
                id: productoId,
                nombre: nombre,
                cantidad: cantidad,
                precioTotal: precio * cantidad,
                vencimiento: '',
            });
            agregados++;
        });

        if (agregados > 0) {
            modal.hide();
        } else {
            mostrarError('Selecciona al menos un producto con cantidad válida y producto del sistema asignado.');
        }
    });

    function renderResultados(productos) {
        scannerTbody.innerHTML = '';
        errorAlert.classList.add('d-none');

        if (productos.length === 0) {
            scannerTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No se detectaron productos.</td></tr>';
            return;
        }

        var selectOptions = productosDelSistema.map(function (p) {
            return '<option value="' + escHtml(p.id) + '">' + escHtml(p.nombre) + '</option>';
        }).join('');

        productos.forEach(function (p) {
            var tr = document.createElement('tr');

            var selectedId = p.id || '';
            var selectHtml = '<select class="form-select form-select-sm scanner-select-producto">' +
                '<option value="">-- Sin asignar --</option>' +
                productosDelSistema.map(function (sp) {
                    var sel = sp.id === selectedId ? ' selected' : '';
                    return '<option value="' + escHtml(sp.id) + '"' + sel + '>' + escHtml(sp.nombre) + '</option>';
                }).join('') +
                '</select>';

            tr.innerHTML =
                '<td class="text-center"><input type="checkbox" class="scanner-row-check" checked></td>' +
                '<td><span class="scanner-nombre-factura small">' + escHtml(p.nombre_factura || '') + '</span></td>' +
                '<td>' + selectHtml + '</td>' +
                '<td><input type="number" class="form-control form-control-sm scanner-input-cant text-center" value="' + (p.cantidad || 1) + '" min="1" step="1"></td>' +
                '<td><input type="number" class="form-control form-control-sm scanner-input-precio text-end" value="' + (p.precio_unitario || 0) + '" min="0" step="1"></td>';

            scannerTbody.appendChild(tr);
        });
    }

    function mostrarPaso(paso) {
        stepUpload.style.display  = paso === 'upload'   ? '' : 'none';
        stepLoading.style.display = paso === 'loading'  ? '' : 'none';
        stepResults.style.display = paso === 'results'  ? '' : 'none';
    }

    function mostrarError(msg) {
        errorAlert.textContent = msg;
        errorAlert.classList.remove('d-none');
    }

    function resetScanner() {
        selectedFile = null;
        previewImg.src = '';
        previewCont.style.display = 'none';
        document.getElementById('scanner-input-galeria').value = '';
        document.getElementById('scanner-input-camara').value  = '';
        errorAlert.classList.add('d-none');
        mostrarPaso('upload');
    }

    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str || '')));
        return d.innerHTML;
    }
})();
</script>
@endpush
@endsection
