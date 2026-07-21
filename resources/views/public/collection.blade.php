@extends('layouts.catalogo')

@section('title', 'Catálogo | Arepas Boyacenses')

@push('css')
<style>
    /* ─── Catálogo Variables ─────────────────────────── */
    :root {
        --catalog-card-bg: rgba(255,255,255,0.04);
        --catalog-border: rgba(255,255,255,0.1);
    }
    [data-theme="light"] {
        --catalog-card-bg: #ffffff;
        --catalog-border: rgba(0,0,0,0.08);
    }

    /* ─── Layout ─────────────────────────────────────── */
    .catalog-hero {
        padding-top: 100px;
        padding-bottom: 40px;
        text-align: center;
    }
    .catalog-hero h1 {
        color: var(--text-color);
        font-size: clamp(1.8rem, 5vw, 3rem);
        font-weight: 800;
        letter-spacing: 4px;
        margin-bottom: 8px;
    }
    .catalog-hero p { color: var(--text-muted); margin-bottom: 0; }
    .catalog-divider {
        width: 50px; height: 3px;
        background: var(--primary-color);
        margin: 16px auto;
        border-radius: 2px;
    }

    /* ─── Category Pills ─────────────────────────────── */
    .cat-pills-wrap {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        padding: 0 16px 24px;
    }
    .cat-pill {
        background: transparent;
        border: 1px solid var(--catalog-border);
        color: var(--text-muted);
        padding: 7px 18px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .cat-pill.active,
    .cat-pill:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: #000;
        font-weight: 600;
    }

    /* ─── Product Card ───────────────────────────────── */
    .catalog-card {
        background: var(--catalog-card-bg);
        border: 1px solid var(--catalog-border);
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .catalog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }

    /* ─── Image ──────────────────────────────────────── */
    .catalog-img-wrap {
        position: relative;
        padding-top: 100%;
        background: rgba(128,128,128,0.08);
        overflow: hidden;
    }
    .catalog-img {
        position: absolute;
        inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .catalog-card:hover .catalog-img { transform: scale(1.04); }
    .catalog-img-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
    }
    .catalog-cat-badge {
        position: absolute;
        top: 8px; left: 8px;
        background: rgba(0,0,0,0.65);
        backdrop-filter: blur(4px);
        color: var(--primary-color);
        font-size: 0.62rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 999px;
        border: 1px solid var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        max-width: calc(100% - 16px);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ─── Card Body ──────────────────────────────────── */
    .catalog-info {
        padding: 12px 12px 8px;
        flex-grow: 1;
    }
    .catalog-name {
        color: var(--text-color);
        font-size: 0.88rem;
        font-weight: 600;
        margin: 0 0 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3;
        min-height: 2.3em;
    }
    .catalog-price {
        color: var(--primary-color);
        font-size: 1.15rem;
        font-weight: 700;
    }

    /* ─── Add to Cart Button ─────────────────────────── */
    .btn-add-cart {
        display: block;
        width: 100%;
        padding: 11px 12px;
        background: var(--primary-color);
        color: #000;
        border: none;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
        letter-spacing: 0.3px;
        border-top: 1px solid var(--catalog-border);
    }
    .btn-add-cart:hover { opacity: 0.85; }
    .btn-add-cart:active { transform: scale(0.98); }

    /* ─── Floating Cart Button ───────────────────────── */
    .floating-cart-btn {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translateY(-50%);
        background: var(--primary-color);
        color: #000;
        border: none;
        border-radius: 12px 0 0 12px;
        padding: 16px 12px 16px 16px;
        cursor: pointer;
        box-shadow: -4px 0 20px rgba(0,0,0,0.4);
        z-index: 1005;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        transition: padding-right 0.2s;
    }
    .floating-cart-btn:hover { padding-right: 18px; }
    .cart-float-badge {
        background: #dc3545;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }

    /* ─── Offcanvas Cart ─────────────────────────────── */
    #cartOffcanvas {
        max-width: 400px;
        width: 100%;
        background: #1a0e00 !important;
        border-left: 1px solid #3a2a18;
    }
    [data-theme="light"] #cartOffcanvas {
        background: #ffffff !important;
        border-left: 1px solid #e5d5c5;
    }

    .cart-item {
        display: flex;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--catalog-border);
        align-items: flex-start;
    }
    .cart-item-img {
        width: 56px; height: 56px;
        border-radius: 8px;
        overflow: hidden;
        background: rgba(128,128,128,0.1);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
    .cart-item-name {
        color: var(--text-color);
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 2px;
    }
    .cart-item-unit {
        color: var(--text-muted);
        font-size: 0.76rem;
        margin-bottom: 6px;
    }
    .cart-item-subtotal {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 0.9rem;
    }
    .qty-controls { display: flex; align-items: center; gap: 8px; }
    .qty-btn {
        width: 26px; height: 26px;
        border-radius: 50%;
        border: 1px solid var(--primary-color);
        background: transparent;
        color: var(--primary-color);
        font-size: 1rem;
        cursor: pointer;
        line-height: 1;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s, color 0.15s;
    }
    .qty-btn:hover { background: var(--primary-color); color: #000; }
    .qty-val {
        color: var(--text-color);
        font-weight: 600;
        min-width: 22px;
        text-align: center;
        font-size: 0.9rem;
    }
    .btn-remove-item {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        font-size: 0.85rem;
        margin-left: auto;
        display: block;
        transition: color 0.15s;
        flex-shrink: 0;
    }
    .btn-remove-item:hover { color: #dc3545; }

    .cart-empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-muted);
    }
    .cart-empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; }

    .cart-footer { padding: 16px; border-top: 1px solid var(--catalog-border); }
    .cart-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .cart-total-label { color: var(--text-muted); font-size: 0.9rem; }
    .cart-total-val {
        color: var(--primary-color);
        font-size: 1.4rem;
        font-weight: 700;
    }

    .btn-whatsapp {
        display: block;
        width: 100%;
        padding: 14px;
        background: #25d366;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
        margin-bottom: 8px;
    }
    .btn-whatsapp:hover { background: #1da851; }

    .btn-clear-cart {
        display: block;
        width: 100%;
        padding: 10px;
        background: transparent;
        border: 1px solid var(--catalog-border);
        color: var(--text-muted);
        border-radius: 10px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-clear-cart:hover { border-color: #dc3545; color: #dc3545; }

    /* ─── Toast ──────────────────────────────────────── */
    .cart-toast {
        position: fixed;
        bottom: 160px;
        right: 20px;
        background: var(--primary-color);
        color: #000;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.83rem;
        z-index: 9999;
        opacity: 0;
        transform: translateY(8px);
        transition: all 0.25s;
        pointer-events: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .cart-toast.show { opacity: 1; transform: translateY(0); }

    /* ─── Empty/No results ───────────────────────────── */
    .catalog-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    .catalog-empty i { font-size: 3rem; margin-bottom: 12px; display: block; }

    /* ─── Responsive tweaks ──────────────────────────── */
    @media (max-width: 575px) {
        .catalog-name { font-size: 0.82rem; }
        .catalog-price { font-size: 1rem; }
        .btn-add-cart { font-size: 0.78rem; padding: 9px 8px; }
        .floating-cart-btn { padding: 12px 10px 12px 12px; }
    }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="catalog-hero">
    <h1>🫓 NUESTRO MENÚ</h1>
    <p>Arma tu pedido y lo recibimos por WhatsApp</p>
    <div class="catalog-divider"></div>
</div>

{{-- Category Pills --}}
<div class="cat-pills-wrap">
    <button class="cat-pill active" data-cat="all">Todos</button>
    @foreach($categorias as $cat)
        <button class="cat-pill" data-cat="{{ $cat }}">{{ $cat }}</button>
    @endforeach
</div>

{{-- Products Grid --}}
<div class="container-fluid px-3 px-md-4 px-lg-5 pb-5">
    @if($productos->isEmpty())
        <div class="catalog-empty">
            <i class="fas fa-box-open"></i>
            <p>No hay productos disponibles en este momento.</p>
        </div>
    @else
    <div class="row g-3 g-md-4" id="productsGrid">
        @foreach($productos as $item)
        <div class="col-6 col-sm-4 col-md-3 col-xl-2 product-item"
             data-cat="{{ $item->categoria?->caracteristica?->nombre ?? '' }}">
            <div class="catalog-card">
                <div class="catalog-img-wrap">
                    @if(!empty($item->img_path))
                        <img src="{{ $item->image_url }}"
                             alt="{{ $item->nombre }}"
                             class="catalog-img"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="catalog-img-placeholder" style="display:none">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    @else
                        <div class="catalog-img-placeholder">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    @endif
                    @if($item->categoria?->caracteristica?->nombre)
                        <span class="catalog-cat-badge">{{ $item->categoria->caracteristica->nombre }}</span>
                    @endif
                </div>

                <div class="catalog-info">
                    <div class="catalog-name">{{ $item->nombre }}</div>
                    <div class="catalog-price">${{ number_format($item->precio ?? 0, 0, ',', '.') }}</div>
                </div>

                <button class="btn-add-cart"
                        onclick="addToCart(
                            '{{ $item->id }}',
                            {{ json_encode($item->nombre) }},
                            {{ (int)($item->precio ?? 0) }},
                            {{ json_encode($item->image_url) }}
                        )">
                    <i class="fas fa-cart-plus me-1"></i> Agregar
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <div id="noResults" class="catalog-empty" style="display:none">
        <i class="fas fa-search"></i>
        <p>No hay productos en esta categoría.</p>
    </div>
    @endif
</div>

{{-- Cart Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
    <div class="offcanvas-header" style="border-bottom: 1px solid var(--catalog-border)">
        <h5 class="offcanvas-title" style="color: var(--text-color); font-weight: 700">
            <i class="fas fa-shopping-bag me-2" style="color: var(--primary-color)"></i>
            Tu Pedido
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                style="filter: var(--bs-btn-close-filter, none)"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div id="cartItems" class="flex-grow-1 overflow-auto"></div>
        <div class="cart-footer">
            <div class="cart-total-row">
                <span class="cart-total-label">Total estimado</span>
                <span class="cart-total-val" id="cartTotal">$0</span>
            </div>
            <button class="btn-whatsapp" onclick="sendToWhatsApp()">
                <i class="fab fa-whatsapp me-2"></i> Pedir por WhatsApp
            </button>
            <button class="btn-clear-cart" onclick="clearCart()">
                <i class="fas fa-trash me-1"></i> Vaciar carrito
            </button>
        </div>
    </div>
</div>

{{-- Modal: datos de entrega --}}
<div class="modal fade" id="orderInfoModal" tabindex="-1" aria-labelledby="orderInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-card, #1e1e1e); color: var(--text-color, #fff); border: 1px solid var(--catalog-border, #333)">
            <div class="modal-header" style="border-bottom: 1px solid var(--catalog-border, #333)">
                <h5 class="modal-title" id="orderInfoModalLabel" style="font-weight:700">
                    <i class="fab fa-whatsapp me-2" style="color:#25d366"></i> Datos del pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="orderNombre" class="form-label fw-semibold">
                        <i class="fas fa-user me-1" style="color: var(--primary-color, #e84393)"></i>
                        Nombre *
                    </label>
                    <input type="text" id="orderNombre" class="form-control"
                           style="background: var(--bg-input, #2a2a2a); color: var(--text-color, #fff); border-color: var(--catalog-border, #444)"
                           placeholder="Tu nombre completo">
                </div>
                <div class="mb-3">
                    <label for="orderDireccion" class="form-label fw-semibold">
                        <i class="fas fa-map-marker-alt me-1" style="color: var(--primary-color, #e84393)"></i>
                        Dirección o local *
                    </label>
                    <input type="text" id="orderDireccion" class="form-control"
                           style="background: var(--bg-input, #2a2a2a); color: var(--text-color, #fff); border-color: var(--catalog-border, #444)"
                           placeholder="Ej: Calle 10 # 5-23, Local 3, Barrio Centro…">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-credit-card me-1" style="color: var(--primary-color, #e84393)"></i>
                        Método de pago *
                    </label>
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="radio" class="btn-check" name="orderPago" id="pagoNequi" value="Nequi">
                        <label class="btn btn-outline-success btn-sm fw-semibold" for="pagoNequi">Nequi</label>

                        <input type="radio" class="btn-check" name="orderPago" id="pagoDaviplata" value="Daviplata">
                        <label class="btn btn-outline-danger btn-sm fw-semibold" for="pagoDaviplata">Daviplata</label>

                        <input type="radio" class="btn-check" name="orderPago" id="pagoEfectivo" value="Efectivo">
                        <label class="btn btn-outline-warning btn-sm fw-semibold" for="pagoEfectivo">Efectivo</label>
                    </div>
                    <div id="pagoError" class="text-danger small mt-1" style="display:none">Selecciona un método de pago</div>
                </div>
                <div class="mb-2">
                    <label for="orderComentarios" class="form-label fw-semibold">
                        <i class="fas fa-comment-dots me-1" style="color: var(--primary-color, #e84393)"></i>
                        Detalles o comentarios
                    </label>
                    <textarea id="orderComentarios" class="form-control" rows="3"
                              style="background: var(--bg-input, #2a2a2a); color: var(--text-color, #fff); border-color: var(--catalog-border, #444)"
                              placeholder="Ej: Sin cebolla, entregar después de las 12pm, apartamento 201…"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--catalog-border, #333)">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmSendToWhatsApp()" style="background:#25d366; border-color:#25d366; font-weight:700">
                    <i class="fab fa-whatsapp me-1"></i> Enviar pedido
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Floating Cart Button --}}
<button class="floating-cart-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#cartOffcanvas"
        id="floatingCartBtn"
        title="Ver pedido">
    <i class="fas fa-shopping-bag" style="font-size:1.3rem"></i>
    <span class="cart-float-badge" id="cartBadgeFloat">0</span>
    <span style="font-size:0.6rem; font-weight:700; text-transform:uppercase; letter-spacing:0.3px; writing-mode:vertical-rl; text-orientation:mixed; margin-top:4px">Pedido</span>
</button>

@endsection

@push('js')
<script>
(function () {
    const WA_NUMBER = '573202078858';
    const CART_KEY  = 'catalogo_cart';

    /* ─── Helpers ─────────────────────────── */
    function getCart() {
        try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
        catch { return []; }
    }

    function saveCart(cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        renderCart();
    }

    function fmtPrice(n) {
        return '$' + Number(n).toLocaleString('es-CO');
    }

    /* ─── Cart Actions ────────────────────── */
    window.addToCart = function(id, nombre, precio, imagen) {
        const cart = getCart();
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.cantidad++;
        } else {
            cart.push({ id, nombre, precio, imagen, cantidad: 1 });
        }
        saveCart(cart);
        showToast('¡Agregado al carrito!');
    };

    window.removeFromCart = function(id) {
        saveCart(getCart().filter(i => i.id !== id));
    };

    window.updateQty = function(id, delta) {
        const cart = getCart();
        const item = cart.find(i => i.id === id);
        if (item) {
            item.cantidad = Math.max(1, item.cantidad + delta);
            saveCart(cart);
        }
    };

    window.clearCart = function() {
        if (getCart().length === 0) return;
        saveCart([]);
    };

    window.sendToWhatsApp = function() {
        const cart = getCart();
        if (cart.length === 0) {
            alert('Tu carrito está vacío. Agrega productos antes de pedir.');
            return;
        }
        // Limpiar campos y abrir modal de datos
        document.getElementById('orderNombre').value = '';
        document.getElementById('orderDireccion').value = '';
        document.getElementById('orderComentarios').value = '';
        document.querySelectorAll('input[name="orderPago"]').forEach(r => r.checked = false);
        document.getElementById('pagoError').style.display = 'none';
        const modal = new bootstrap.Modal(document.getElementById('orderInfoModal'));
        modal.show();
    };

    window.confirmSendToWhatsApp = function() {
        const nombre    = document.getElementById('orderNombre').value.trim();
        const direccion = document.getElementById('orderDireccion').value.trim();
        const pago      = document.querySelector('input[name="orderPago"]:checked')?.value || '';
        const comentarios = document.getElementById('orderComentarios').value.trim();

        let valid = true;
        document.getElementById('orderNombre').style.borderColor    = nombre    ? '' : '#dc3545';
        document.getElementById('orderDireccion').style.borderColor = direccion ? '' : '#dc3545';
        if (!nombre)    { document.getElementById('orderNombre').focus(); valid = false; }
        if (!direccion) { valid = false; }
        if (!pago)      { document.getElementById('pagoError').style.display = 'block'; valid = false; }
        else            { document.getElementById('pagoError').style.display = 'none'; }
        if (!valid) return;

        const cart  = getCart();
        const total = cart.reduce((s, i) => s + i.precio * i.cantidad, 0);
        const lines = cart
            .map(i => `• ${i.nombre} x${i.cantidad} — ${fmtPrice(i.precio * i.cantidad)}`)
            .join('\n');

        let msg = `¡Hola! Quiero hacer este pedido 🛍️\n\n${lines}\n\n💰 Total: ${fmtPrice(total)}`;
        msg += `\n\n👤 Nombre: ${nombre}`;
        msg += `\n📍 Dirección/Local: ${direccion}`;
        msg += `\n💳 Pago: ${pago}`;
        if (comentarios) msg += `\n💬 Comentarios: ${comentarios}`;
        msg += `\n\n¡Gracias!`;

        bootstrap.Modal.getInstance(document.getElementById('orderInfoModal')).hide();
        window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
    };

    /* ─── Render ──────────────────────────── */
    function renderCart() {
        const cart  = getCart();
        const count = cart.reduce((s, i) => s + i.cantidad, 0);
        const total = cart.reduce((s, i) => s + i.precio * i.cantidad, 0);

        // Badges
        const badgeFloat = document.getElementById('cartBadgeFloat');
        const badgeNav   = document.getElementById('cartCountNav');
        if (badgeFloat) badgeFloat.textContent = count;
        if (badgeNav)   badgeNav.textContent   = count;

        // Total
        const totalEl = document.getElementById('cartTotal');
        if (totalEl) totalEl.textContent = fmtPrice(total);

        // Items
        const container = document.getElementById('cartItems');
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Tu carrito está vacío.<br>
                    <small>Agrega productos desde el catálogo.</small></p>
                </div>`;
            return;
        }

        container.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-img">
                    ${item.imagen
                        ? `<img src="${item.imagen}" alt="${item.nombre}" onerror="this.style.display='none'">`
                        : '<i class="fas fa-image"></i>'}
                </div>
                <div style="flex:1; min-width:0">
                    <div class="cart-item-name">${item.nombre}</div>
                    <div class="cart-item-unit">${fmtPrice(item.precio)} / unidad</div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty('${item.id}', -1)">−</button>
                        <span class="qty-val">${item.cantidad}</span>
                        <button class="qty-btn" onclick="updateQty('${item.id}', 1)">+</button>
                        <span class="cart-item-subtotal ms-2">${fmtPrice(item.precio * item.cantidad)}</span>
                    </div>
                </div>
                <button class="btn-remove-item" onclick="removeFromCart('${item.id}')" title="Quitar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }

    /* ─── Category Filter ─────────────────── */
    document.querySelectorAll('.cat-pill').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const cat = this.dataset.cat;
            let visible = 0;
            document.querySelectorAll('.product-item').forEach(el => {
                const match = cat === 'all' || el.dataset.cat === cat;
                el.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            const noResults = document.getElementById('noResults');
            if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
        });
    });

    /* ─── Toast ───────────────────────────── */
    function showToast(msg) {
        const t = document.createElement('div');
        t.className = 'cart-toast';
        t.innerHTML = `<i class="fas fa-check me-1"></i> ${msg}`;
        document.body.appendChild(t);
        requestAnimationFrame(() => { t.offsetHeight; t.classList.add('show'); });
        setTimeout(() => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 300);
        }, 2000);
    }

    /* ─── Init ────────────────────────────── */
    renderCart();
})();
</script>
@endpush
