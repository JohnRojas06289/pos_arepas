@extends('layouts.app')

@section('title', 'Registrar Gasto')

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
                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <input type="text" name="descripcion" id="descripcion"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                value="{{ old('descripcion') }}"
                                placeholder="Ej: Arriendo mes de abril"
                                required>
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
                                            <label class="form-label">Precio unit.</label>
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
                                                    <th class="text-end" style="width:110px">Precio</th>
                                                    <th class="text-center" style="width:110px">Vencimiento</th>
                                                    <th class="text-end" style="width:110px">Subtotal</th>
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
                                                    <td colspan="4" class="text-end">Total surtido:</td>
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

@push('js')
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
            return acc + p.cantidad * p.precio;
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
                var subtotal = p.cantidad * p.precio;
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(p.nombre) + '</td>' +
                    '<td class="text-center">' + p.cantidad + '</td>' +
                    '<td class="text-end">' + formatCOP(p.precio) + '</td>' +
                    '<td class="text-center">' + (p.vencimiento || '—') + '</td>' +
                    '<td class="text-end">' + formatCOP(subtotal) + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" data-remove="' + idx + '">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</td>';
                sTbody.appendChild(tr);

                sHiddenInputs.innerHTML +=
                    '<input type="hidden" name="arrayidproducto[]" value="' + escapeAttr(p.id) + '">' +
                    '<input type="hidden" name="arraycantidad[]" value="' + p.cantidad + '">' +
                    '<input type="hidden" name="arraypreciocompra[]" value="' + p.precio + '">' +
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

    sBtnAgregar.addEventListener('click', function () {
        var productoId = sProductoId.value;
        var cantidad   = parseFloat(sCantidad.value);
        var precio     = parseFloat(sPrecio.value);
        var venc       = sVencimiento.value;
        var opt        = sProductoId.options[sProductoId.selectedIndex];
        var nombre     = opt ? (opt.dataset.nombre || opt.text) : '';

        if (!productoId)           { showAlert('Selecciona un producto.'); return; }
        if (!cantidad || cantidad < 1) { showAlert('Ingresa una cantidad válida (mínimo 1).'); return; }
        if (isNaN(precio) || precio < 0) { showAlert('Ingresa un precio válido.'); return; }

        productos.push({ id: productoId, nombre: nombre, cantidad: cantidad, precio: precio, vencimiento: venc });
        renderTable();

        sCantidad.value    = '';
        sPrecio.value      = '';
        sVencimiento.value = '';
        sProductoId.value  = '';
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
</script>
@endpush
@endsection
