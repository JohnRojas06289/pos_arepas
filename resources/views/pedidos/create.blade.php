@extends('layouts.app')

@section('title', 'Tomar Pedido')

@push('css')
<style>
    body { overflow: hidden; }
    #layoutSidenav_content { padding-top: 60px !important; }
    .pos-container { height: calc(100vh - 60px); overflow: hidden; }
    #agente-ia-btn, #agente-ia-panel { display: none !important; }

    #layoutSidenav_content main { padding: 0 !important; margin: 0 !important; }

    /* Sidebar de categorías */
    .category-sidebar {
        height: 100%;
        overflow-y: auto;
        background: var(--bg-sidebar);
        border-right: 1px solid var(--border-sidebar);
        box-shadow: 2px 0 10px rgba(0,0,0,0.12);
        max-width: 15%;
    }

    .product-grid {
        height: 100%;
        overflow-y: auto;
        padding: 1rem;
        padding-top: 0.5rem;
        background: var(--bg-primary);
    }

    .cart-section {
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: var(--bg-card);
        border-left: 1px solid var(--border-color);
        box-shadow: -2px 0 10px rgba(0,0,0,0.04);
        position: relative;
    }

    /* Botones de categoría */
    .category-btn {
        width: 100%;
        text-align: left;
        padding: 13px 12px;
        background: transparent;
        border: none;
        border-bottom: 1px solid var(--border-sidebar);
        color: var(--text-sidebar);
        transition: all 0.25s ease;
        font-weight: 500;
        font-size: 0.92rem;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

    .category-btn::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        height: 100%; width: 4px;
        background-color: var(--color-accent);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .category-btn:hover, .category-btn.active {
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
        color: #fff;
        font-weight: bold;
        transform: translateX(5px);
    }

    .category-btn.active::before { transform: scaleY(1); }

    .category-btn i {
        width: 30px;
        text-align: center;
        margin-right: 8px;
        font-size: 1.1rem;
    }

    /* Tarjetas de producto */
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid var(--border-color);
        overflow: hidden;
        border-radius: 12px;
        background: var(--bg-card);
        box-shadow: var(--card-shadow);
    }

    .product-card:hover {
        transform: translateY(-4px) scale(1.02);
        border-color: var(--color-accent);
        box-shadow: 0 8px 16px rgba(240,199,94,0.22);
    }

    .product-card:active { transform: translateY(-2px) scale(0.98); }

    .product-img-container {
        height: 140px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-img { transform: scale(1.1); }

    .product-price {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .product-name {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Items del carrito */
    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0;
        background: var(--bg-primary);
    }

    .cart-item {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-card);
        margin: 6px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
    }

    .cart-item:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.1); transform: translateX(4px); }

    .cart-item-details { min-width: 0; }

    .cart-item-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .cart-item-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .cart-item-field label {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 600;
        margin: 0;
    }

    .cart-item-field .form-control {
        min-height: 34px;
        padding: 6px 10px;
        font-size: 0.9rem;
    }

    .cart-price-input { width: 110px; }
    .cart-qty-input   { width: 72px; }

    .cart-item-subtotal {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 800;
        color: var(--color-primary);
    }

    .cart-item-new { animation: slideIn 0.3s ease; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    .cart-footer {
        padding: 1rem 1.1rem;
        background: var(--bg-card);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
    }

    /* Total */
    .total-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.2rem !important;
        font-weight: 900 !important;
        color: var(--color-success) !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }

    /* Botón enviar */
    #btnEnviar {
        font-size: 1.1rem;
        font-weight: 800;
        padding: 16px !important;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(76,175,125,0.30);
        transition: all 0.25s ease;
        border: none;
        background: linear-gradient(135deg, var(--color-success) 0%, #3a9068 100%);
        color: #fff;
    }

    #btnEnviar:not(:disabled):hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(76,175,125,0.40);
    }

    #btnEnviar:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Inputs */
    .form-control {
        border-radius: 8px;
        border: 1.5px solid var(--border-input);
        padding: 10px 12px;
        font-size: 1rem;
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-subtle);
    }

    #searchInput {
        font-size: 1.1rem;
        padding: 14px 16px;
        border-radius: 12px;
    }

    #cartCount {
        font-size: 1rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%); }

    footer { display: none !important; }

    @media (min-width: 992px) {
        .col-lg-20 { flex: 0 0 auto; width: 20%; }
    }

    /* Barra alfabética */
    #alpha-bar {
        width: 22px;
        flex-shrink: 0;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 4px 0;
        background: rgba(0,0,0,0.03);
        border-left: 1px solid var(--border-color);
        user-select: none;
        touch-action: none;
        z-index: 5;
    }

    [data-theme="dark"] #alpha-bar { background: rgba(255,255,255,0.03); }

    .alpha-letter {
        font-size: 9px;
        font-weight: 700;
        line-height: 1;
        padding: 1px 2px;
        cursor: pointer;
        color: var(--text-muted);
        border-radius: 3px;
        transition: all 0.12s ease;
        width: 100%;
        text-align: center;
        flex-shrink: 0;
    }

    .alpha-letter:hover  { background: var(--color-primary) !important; color: #fff !important; }
    .alpha-letter.active { background: var(--color-primary) !important; color: #fff !important; }
    .alpha-letter.has-items { color: var(--color-primary); font-weight: 800; }
    .alpha-letter.no-items  { opacity: 0.25; cursor: default; }

    /* Cart header */
    .cart-header-pedido {
        background: var(--bg-sidebar);
        border-bottom: 1px solid var(--border-sidebar);
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .cart-header-title {
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')
<form action="{{ route('pedidos.store') }}" method="post" id="pedidoForm" class="h-100">
    @csrf
    <div class="row g-0 pos-container">

        <!-- Column 1: Categories -->
        <div class="col-md-2 category-sidebar d-none d-md-block">
            <div class="p-3 border-bottom" style="border-color:var(--border-sidebar)!important;">
                <h6 class="m-0" style="color:var(--text-sidebar);font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.06em;">
                    <i class="fa-solid fa-layer-group me-2" style="color:var(--color-accent);"></i>Categorías
                </h6>
            </div>
            <button type="button" class="category-btn active" onclick="filterCategory('all', this)">
                <i class="fa-solid fa-border-all"></i> Todo
            </button>
            @foreach ($categorias as $cat)
            <button type="button" class="category-btn" onclick="filterCategory('{{$cat->id}}', this)">
                <i class="fa-solid fa-tag"></i> {{$cat->caracteristica->nombre}}
            </button>
            @endforeach
        </div>

        <!-- Column 2: Products -->
        <div class="col-12 col-md product-grid" id="productGrid">
            <div class="sticky-top pb-3 pt-1 mb-2" style="z-index:10;background:var(--bg-primary);">
                <div class="input-group input-group-lg" style="border-radius:12px;overflow:hidden;box-shadow:var(--card-shadow);">
                    <span class="input-group-text" style="background:var(--bg-input);border:1.5px solid var(--border-input);border-right:none;border-radius:12px 0 0 12px;">
                        <i class="fa-solid fa-search" style="color:var(--text-muted);"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o código..." autofocus
                           style="border-radius:0 12px 12px 0;border:1.5px solid var(--border-input);border-left:none;">
                </div>
            </div>

            <div class="row g-3" id="productsContainer">
                @foreach ($productos as $item)
                <div class="col-6 col-md-3 col-lg-20 product-item"
                     id="product-{{$item->id}}"
                     data-stock="{{$item->cantidad}}"
                     data-category="{{$item->categoria_id}}"
                     data-nombre="{{ strtoupper(substr(trim($item->nombre), 0, 1)) }}"
                     data-search="{{ strtolower($item->nombre . ' ' . $item->codigo) }}">
                    <div class="card h-100 product-card shadow-sm border-0"
                         onclick="addToCart('{{$item->id}}', '{{addslashes($item->nombre)}}', {{$item->precio ?? 0}}, parseInt(this.closest('.product-item').getAttribute('data-stock')), '{{$item->sigla ?? 'UND'}}')">
                        <div class="product-img-container">
                            @if($item->img_path)
                                <img src="{{ $item->image_url }}"
                                     data-fallback="{{ $item->image_url }}"
                                     class="product-img"
                                     alt="{{$item->nombre}}"
                                     onerror="if(this.dataset.fallback && this.src !== this.dataset.fallback){ this.src = this.dataset.fallback; this.dataset.fallback=''; } else { this.parentElement.innerHTML='<div class=\'text-muted text-center p-3\'><i class=\'fa-solid fa-image fa-3x mb-2 opacity-25\'></i><br><small>Sin imagen</small></div>'; }">
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="fa-solid fa-image fa-3x mb-2 opacity-25"></i>
                                    <br><small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <div class="product-price">{{$empresa->moneda->simbolo ?? '$'}} {{ number_format($item->precio ?? 0, 0, ',', '.') }}</div>
                            <h6 class="card-title mb-1 text-truncate product-name" title="{{$item->nombre}}">{{$item->nombre}}</h6>
                            <small class="text-{{ $item->cantidad > 5 ? 'success' : ($item->cantidad > 0 ? 'warning' : 'danger') }} d-block" style="font-size:0.7rem;">
                                Stock: {{$item->cantidad}}
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Barra alfabética -->
        <div id="alpha-bar">
            @foreach(range('A','Z') as $letter)
            <span class="alpha-letter" data-letter="{{ $letter }}">{{ $letter }}</span>
            @endforeach
        </div>

        <!-- Column 3: Cart / Pedido -->
        <div class="col-md-3 cart-section shadow-lg">
            <!-- Header del carrito -->
            <div class="cart-header-pedido">
                <div class="cart-header-title">
                    <i class="fa-solid fa-clipboard-list" style="color:var(--color-accent);"></i>
                    Pedido
                    <span class="badge ms-1" id="cartCount"
                          style="background:var(--color-accent);color:var(--color-secondary);font-family:'JetBrains Mono',monospace;font-weight:700;font-size:0.9rem;">0</span>
                </div>
                <div style="font-size:0.78rem;color:rgba(255,255,255,0.65);">
                    <i class="fa-solid fa-user me-1"></i> {{ auth()->user()->name }}
                </div>
            </div>

            <div class="cart-items" id="cartItemsContainer">
                <div class="text-center text-muted mt-5" id="emptyCartMessage">
                    <i class="fa-solid fa-clipboard-list fa-3x mb-3 opacity-50"></i>
                    <p>Pedido vacío</p>
                </div>
                <div id="cartList"></div>
            </div>

            <div class="cart-footer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fs-6 fw-bold text-secondary">TOTAL:</span>
                    <span class="total-display">{{$empresa->moneda->simbolo ?? '$'}} <span id="totalDisplay">0</span></span>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg fw-bold w-75" id="btnEnviar" disabled data-no-spinner="true">
                        <i class="fa-solid fa-paper-plane me-2"></i> Enviar pedido
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg fw-bold w-25" onclick="limpiarPedido()">
                        <i class="fa-solid fa-broom"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script>
    var cart  = [];
    var total = 0;
    var SIMBOLO = '{{ $empresa->moneda->simbolo ?? "$" }}';

    function playSound(frequency, duration) {
        try {
            var audioContext = new (window.AudioContext || window.webkitAudioContext)();
            var oscillator  = audioContext.createOscillator();
            var gainNode    = audioContext.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + duration);
        } catch(e) {}
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function normalizeMoney(value) {
        var parsed = parseFloat(value);
        if (isNaN(parsed) || parsed < 0) return 0;
        return Math.round(parsed * 100) / 100;
    }

    function recalculateItem(item) {
        item.precio   = normalizeMoney(item.precio);
        item.subtotal = normalizeMoney(item.cantidad * item.precio);
    }

    function updateCartTotals() {
        total = 0;
        cart.forEach(function(item) { total += item.subtotal; });

        document.getElementById('totalDisplay').innerText = formatNumber(total);

        var totalItems = cart.reduce(function(acc, item) { return acc + item.cantidad; }, 0);
        document.getElementById('cartCount').innerText = totalItems;

        document.getElementById('btnEnviar').disabled = (cart.length === 0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderCart();

        // Auto-collapse sidebar
        if (!document.body.classList.contains('sb-sidenav-toggled')) {
            setTimeout(function() {
                document.body.classList.add('sb-sidenav-toggled');
            }, 500);
        }

        var searchInput = document.getElementById('searchInput');
        searchInput.focus();

        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                var visible = document.querySelectorAll('.product-item[style=""], .product-item:not([style*="display: none"])');
                if (visible.length === 1) {
                    visible[0].querySelector('.product-card').click();
                    this.value = '';
                    this.dispatchEvent(new Event('keyup'));
                }
                return;
            }
            var value = this.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(function(item) {
                item.style.display = item.getAttribute('data-search').indexOf(value) > -1 ? '' : 'none';
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.cart-section') && !e.target.closest('input')) {
                setTimeout(function() { searchInput.focus(); }, 100);
            }
        });
    });

    function filterCategory(catId, btn) {
        document.querySelectorAll('.category-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        document.querySelectorAll('.product-item').forEach(function(item) {
            item.style.display = (catId === 'all' || item.getAttribute('data-category') == catId) ? '' : 'none';
        });
        setTimeout(function() { document.getElementById('searchInput').focus(); }, 100);
    }

    function addToCart(id, nombre, precio, stock, sigla) {
        id = id.toString();
        var existing = cart.find(function(i) { return i.id === id; });
        var precioBase = normalizeMoney(precio);

        playSound(800, 0.1);

        if (existing) {
            existing.cantidad++;
            recalculateItem(existing);
        } else {
            cart.push({
                id: id,
                nombre: nombre,
                precio: precioBase,
                cantidad: 1,
                sigla: sigla,
                stock: parseInt(stock),
                subtotal: precioBase,
                isNew: true
            });
        }
        renderCart();

        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            text: nombre,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1200,
            timerProgressBar: true
        });
    }

    function updateQuantityManual(id, newQty) {
        newQty = parseInt(newQty);
        if (isNaN(newQty) || newQty < 1) {
            Swal.fire({ icon: 'warning', title: 'Cantidad mínima: 1', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            renderCart();
            return;
        }
        var item = cart.find(function(i) { return i.id === id; });
        if (item) {
            item.cantidad = newQty;
            recalculateItem(item);
            renderCart();
        }
    }

    function updatePriceManual(id, newPrice) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;
        var precio = parseInt(String(newPrice || '').replace(/\D/g, ''), 10) || 0;
        if (precio <= 0) {
            Swal.fire({ icon: 'warning', title: 'Precio inválido', text: 'El precio debe ser mayor a cero.', toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
            renderCart();
            return;
        }
        item.precio = precio;
        recalculateItem(item);
        renderCart();
    }

    function syncPriceInput(input, id) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;
        var precio = parseInt(String(input.value || '').replace(/\D/g, ''), 10) || 0;
        item.precio = precio;
        recalculateItem(item);
        input.value = precio > 0 ? formatNumber(precio) : '';
        var subtotalNode = document.getElementById('cart-subtotal-' + id);
        if (subtotalNode) subtotalNode.innerText = formatNumber(item.subtotal);
        updateCartTotals();
    }

    function removeFromCart(id) {
        playSound(400, 0.15);
        Swal.fire({
            title: '¿Eliminar producto?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                cart = cart.filter(function(i) { return i.id !== id; });
                renderCart();
            }
        });
    }

    function renderCart() {
        var container = document.getElementById('cartList');
        container.innerHTML = '';
        total = 0;

        if (cart.length === 0) {
            document.getElementById('emptyCartMessage').style.display = 'block';
        } else {
            document.getElementById('emptyCartMessage').style.display = 'none';
            cart.forEach(function(item) {
                var itemClass = item.isNew ? 'cart-item cart-item-new' : 'cart-item';
                item.isNew = false;

                var row = '<div class="' + itemClass + '" id="cart-row-' + item.id + '">' +
                    '<div class="flex-grow-1 cart-item-details">' +
                        '<div class="fw-bold text-truncate" style="max-width:170px;">' + item.nombre + '</div>' +
                        '<div class="cart-item-controls">' +
                            '<div class="cart-item-field">' +
                                '<label>Cantidad</label>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<input type="number" class="form-control form-control-sm cart-qty-input" ' +
                                        'value="' + item.cantidad + '" min="1" ' +
                                        'onchange="updateQuantityManual(\'' + item.id + '\', this.value)" ' +
                                        'onclick="this.select()">' +
                                    '<small class="text-muted">' + item.sigla + '</small>' +
                                '</div>' +
                            '</div>' +
                            '<div class="cart-item-field">' +
                                '<label>Precio</label>' +
                                '<input type="text" class="form-control form-control-sm cart-price-input" ' +
                                    'value="' + formatNumber(item.precio) + '" ' +
                                    'inputmode="numeric" autocomplete="off" ' +
                                    'oninput="syncPriceInput(this, \'' + item.id + '\')" ' +
                                    'onblur="updatePriceManual(\'' + item.id + '\', this.value)" ' +
                                    'onclick="this.select()">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="text-end ms-2">' +
                        '<div class="cart-item-subtotal mb-2" id="cart-subtotal-' + item.id + '">' + formatNumber(item.subtotal) + '</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeFromCart(\'' + item.id + '\')" title="Eliminar">' +
                            '<i class="fa-solid fa-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
                container.insertAdjacentHTML('beforeend', row);
            });
        }

        updateCartTotals();
    }

    function limpiarPedido() {
        if (cart.length === 0) return;
        Swal.fire({
            title: '¿Limpiar pedido?',
            text: 'Se eliminarán todos los productos del pedido.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
                document.getElementById('searchInput').focus();
            }
        });
    }

    // ── Envío AJAX ──
    document.getElementById('pedidoForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Pedido vacío', text: 'Agrega productos antes de enviar.' });
            return;
        }

        var btn = document.getElementById('btnEnviar');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Enviando...';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: cart, total: total })
        })
        .then(function(r) {
            if (r.ok) return r.json();
            return r.json().then(function(d) { throw d; });
        })
        .then(function(data) {
            cart = [];
            renderCart();
            btn.innerHTML = originalHtml;

            Swal.fire({
                icon: 'success',
                title: '¡Pedido enviado!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            document.getElementById('searchInput').focus();
        })
        .catch(function(error) {
            var msg = (error && error.message) ? error.message : 'Error inesperado al enviar el pedido.';
            if (error && error.errors) {
                var msgs = [];
                for (var f in error.errors) { msgs.push(error.errors[f][0]); }
                msg = msgs.join('\n');
            }
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    // ================================================================
    // BARRA ALFABÉTICA VERTICAL
    // ================================================================
    (function initAlphaBar() {
        var alphaBar        = document.getElementById('alpha-bar');
        var productsEl      = document.getElementById('productsContainer');
        var scrollContainer = document.getElementById('productGrid');
        if (!alphaBar || !productsEl || !scrollContainer) return;

        var letters = alphaBar.querySelectorAll('.alpha-letter');

        function updateAvailability() {
            var available = new Set();
            productsEl.querySelectorAll('.product-item').forEach(function(el) {
                if (el.style.display === 'none') return;
                var l = (el.getAttribute('data-nombre') || '').toUpperCase();
                if (l) available.add(l);
            });
            letters.forEach(function(el) {
                var l = el.getAttribute('data-letter');
                el.classList.remove('has-items', 'no-items');
                el.classList.add(available.has(l) ? 'has-items' : 'no-items');
            });
        }

        function scrollToLetter(letter) {
            letter = letter.toUpperCase();
            var ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var allItems = Array.from(productsEl.querySelectorAll('.product-item')).filter(function(el) {
                return el.style.display !== 'none';
            });
            var target = null;
            for (var start = ALPHABET.indexOf(letter); start < ALPHABET.length && !target; start++) {
                var l = ALPHABET[start];
                target = allItems.find(function(el) {
                    return (el.getAttribute('data-nombre') || '').toUpperCase() === l;
                }) || null;
                if (start === ALPHABET.indexOf(letter) && !target) continue;
                if (target) break;
            }
            if (!target) return;

            letters.forEach(function(el) { el.classList.remove('active'); });
            var matchLetter = (target.getAttribute('data-nombre') || '').toUpperCase();
            var activeEl = alphaBar.querySelector('[data-letter="' + matchLetter + '"]');
            if (activeEl) activeEl.classList.add('active');

            var offsetTop = 0;
            var node = target;
            while (node && node !== scrollContainer) {
                offsetTop += node.offsetTop;
                node = node.offsetParent;
            }
            scrollContainer.scrollTo({ top: Math.max(0, offsetTop - 64), behavior: 'smooth' });
        }

        letters.forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                if (el.classList.contains('no-items')) return;
                scrollToLetter(el.getAttribute('data-letter'));
            });
        });

        alphaBar.addEventListener('touchstart', function(e) { handleTouch(e); }, { passive: false });
        alphaBar.addEventListener('touchmove',  function(e) { e.preventDefault(); handleTouch(e); }, { passive: false });

        function handleTouch(e) {
            var touch = e.touches[0];
            var el = document.elementFromPoint(touch.clientX, touch.clientY);
            if (el && el.classList.contains('alpha-letter') && !el.classList.contains('no-items')) {
                scrollToLetter(el.getAttribute('data-letter'));
            }
        }

        scrollContainer.addEventListener('scroll', function() {
            var containerRect = scrollContainer.getBoundingClientRect();
            var midY = containerRect.top + 64 + 10;
            var allItems = Array.from(productsEl.querySelectorAll('.product-item')).filter(function(el) {
                return el.style.display !== 'none';
            });
            var current = null;
            for (var i = 0; i < allItems.length; i++) {
                var rect = allItems[i].getBoundingClientRect();
                if (rect.top <= midY) current = allItems[i]; else break;
            }
            if (current) {
                var l = (current.getAttribute('data-nombre') || '').toUpperCase();
                letters.forEach(function(el) { el.classList.remove('active'); });
                var activeEl = alphaBar.querySelector('[data-letter="' + l + '"]');
                if (activeEl) activeEl.classList.add('active');
            }
        }, { passive: true });

        var _origFilter = window.filterCategory;
        window.filterCategory = function(catId, btn) {
            if (_origFilter) _origFilter(catId, btn);
            setTimeout(updateAvailability, 60);
        };

        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() { setTimeout(updateAvailability, 60); });
        }

        updateAvailability();
    })();
</script>
@endpush
