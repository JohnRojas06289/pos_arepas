@extends('layouts.app')

@section('title', 'Punto de Venta')

@push('css')
<style>
    body { overflow: hidden; }
    .pos-container { height: calc(100vh - 60px); overflow: hidden; }

    /* Fix for extra spacing at the top */
    main { padding: 0 !important; margin: 0 !important; }

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
    }



    /* Botones de categoría más grandes y claros */
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
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
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

    .category-btn.active::before {
        transform: scaleY(1);
    }
    
    .category-btn i { 
        width: 30px; 
        text-align: center; 
        margin-right: 8px; 
        font-size: 1.1rem;
    }
    
    .category-btn .shortcut-hint {
        float: right;
        font-size: 0.75rem;
        opacity: 0.6;
        background: rgba(255,255,255,0.1);
        padding: 2px 6px;
        border-radius: 3px;
    }

    /* Tarjetas de producto mejoradas */
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
    
    .product-card:active { 
        transform: translateY(-2px) scale(0.98); 
    }
    
    .product-img-container {
        height: 140px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .product-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-img {
        transform: scale(1.1);
    }
    
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
    
    .cart-item:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transform: translateX(4px);
    }
    
    .cart-item-new {
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .cart-footer {
        padding: 1rem 1.1rem;
        background: var(--bg-card);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
    }

    .smart-cash-btn {
        font-size: 0.88rem;
        font-weight: 700;
        padding: 10px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 2px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
    }

    .smart-cash-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        border-color: var(--color-accent);
        background-color: var(--color-accent-subtle);
    }

    /* Total más prominente */
    .total-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.2rem !important;
        font-weight: 900 !important;
        color: var(--color-success) !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }

    /* Botón de cobrar */
    #btnPay {
        font-size: 1.15rem;
        font-weight: 800;
        padding: 16px !important;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(76,175,125,0.30);
        transition: all 0.25s ease;
        border: none;
        background: linear-gradient(135deg, var(--color-success) 0%, #3a9068 100%);
        color: #fff;
    }

    #btnPay:not(:disabled):hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(76,175,125,0.40);
    }

    #btnPay:not(:disabled):active { transform: translateY(-1px); }
    #btnPay:disabled { opacity: 0.5; cursor: not-allowed; }
    
    /* Inputs mejorados */
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
    
    /* Búsqueda mejorada */
    #searchInput {
        font-size: 1.1rem;
        padding: 14px 16px;
        border-radius: 12px;
    }
    
    /* Badge del carrito */
    #cartCount {
        font-size: 1rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
    }
    
    /* Scrollbar personalizada */
    ::-webkit-scrollbar { 
        width: 8px; 
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb { 
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
    }
    
    footer { display: none !important; }
    
    /* Indicador de atajo de teclado */
    .keyboard-hint {
        position: fixed;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: #fff;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .keyboard-hint.show {
        opacity: 1;
    }
    
    /* Animación de pulso para el botón de cobrar */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    
    #btnPay:not(:disabled) {
        animation: pulse 2s infinite;
    }
    
    /* Mobile: cart como panel deslizable desde abajo */
    .cart-toggle-mobile {
        display: none;
        position: fixed;
        bottom: 90px;
        left: 16px;
        z-index: 1045;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bg-sidebar);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.35);
        font-size: 1.2rem;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2px;
    }
    .cart-toggle-mobile .badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--color-accent);
        color: var(--color-secondary);
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .mobile-cart-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1039;
    }
    .mobile-cart-overlay.active { display: block; }

    /* Responsive mejorado */
    @media (max-width: 767px) {
        .cart-toggle-mobile { display: flex; }

        /* Cart panel: full-screen slide-up en móvil */
        .cart-section {
            position: fixed !important;
            bottom: 0;
            left: 0;
            right: 0;
            height: 85dvh;
            max-height: 85dvh;
            z-index: 1040;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 32px rgba(0,0,0,0.2);
        }
        .cart-section.mobile-open { transform: translateY(0); }

        .product-card { border-radius: 10px; }
        .product-img-container { height: 100px; }
        .total-display { font-size: 2rem !important; }
        #btnPay { font-size: 1.1rem; padding: 16px !important; }
        .cart-footer { padding: 0.75rem; }
        .smart-cash-btn { min-height: 44px; }
    }

    @media (max-width: 576px) {
        .product-name { font-size: 0.8rem; }
        .product-price { font-size: 0.95rem; }
    }


    @media (min-width: 992px) {
        .col-lg-20 {
            flex: 0 0 auto;
            width: 20%;
        }
    }

    /* Barra alfabética */
    .alpha-letter:hover,
    .alpha-letter.active {
        background: var(--color-primary) !important;
        color: #fff !important;
        border-radius: 3px;
    }
    .alpha-letter.has-items {
        color: var(--color-primary) !important;
        font-weight: 800 !important;
    }
    .alpha-letter.no-items {
        opacity: 0.3 !important;
        cursor: default !important;
    }
    @media (max-width: 576px) {
        #alpha-bar { width: 18px !important; }
        .alpha-letter { font-size: 8px !important; padding: 1px 2px !important; }
        #productsContainer { padding-right: 20px !important; }
    }
</style>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')
<form action="{{ route('ventas.store') }}" method="post" id="ventaForm" class="h-100">
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
        <div class="col-12 col-md product-grid" style="position:relative;">
            <div class="sticky-top pb-3 pt-1 mb-2" style="z-index:10;background:var(--bg-primary);">
                <div class="input-group input-group-lg" style="border-radius:12px;overflow:hidden;box-shadow:var(--card-shadow);">
                    <span class="input-group-text" style="background:var(--bg-input);border:1.5px solid var(--border-input);border-right:none;border-radius:12px 0 0 12px;">
                        <i class="fa-solid fa-search" style="color:var(--text-muted);"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o código..." autofocus
                           style="border-radius:0 12px 12px 0;border:1.5px solid var(--border-input);border-left:none;">
                </div>
            </div>

            {{-- Grilla de productos con padding-right para la barra alfabética --}}
            <div class="row g-3" id="productsContainer" style="padding-right:24px;">
                @foreach ($productos as $item)
                <div class="col-6 col-md-3 col-lg-20 product-item"
                     id="product-{{$item->id}}"
                     data-stock="{{$item->cantidad}}"
                     data-category="{{$item->categoria_id}}"
                     data-nombre="{{ strtoupper(substr(trim($item->nombre), 0, 1)) }}"
                     data-search="{{ strtolower($item->nombre . ' ' . $item->codigo) }}">
                    <div class="card h-100 product-card shadow-sm border-0" onclick="addToCart('{{$item->id}}', '{{addslashes($item->nombre)}}', {{$item->precio}}, parseInt(this.closest('.product-item').getAttribute('data-stock')), '{{$item->sigla ?? 'UND'}}')">
                        <div class="product-img-container">
                            @if($item->img_path)
                                <img src="{{ $item->image_url }}" class="product-img" alt="{{$item->nombre}}" onerror="this.parentElement.innerHTML='<div class=\'text-muted text-center p-3\'><i class=\'fa-solid fa-image fa-3x mb-2 opacity-25\'></i><br><small>Sin imagen</small></div>'">
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="fa-solid fa-image fa-3x mb-2 opacity-25"></i>
                                    <br><small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title mb-1 text-truncate product-name" title="{{$item->nombre}}">{{$item->nombre}}</h6>
                            <div class="product-price">{{$empresa->moneda->simbolo ?? '$'}} {{ number_format($item->precio, 0, ',', '.') }}</div>
                            <small class="text-{{ $item->cantidad > 5 ? 'success' : 'danger' }} d-block stock-display" style="font-size: 0.7rem;">
                                Stock: <span class="stock-count">{{$item->cantidad}}</span>
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Barra alfabética vertical --}}
            <div id="alpha-bar" style="
                position:absolute;
                top:0; right:0;
                width:22px;
                height:100%;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:flex-start;
                padding-top:60px;
                z-index:9;
                background:transparent;
                user-select:none;
                touch-action:none;
            ">
                @foreach(range('A','Z') as $letter)
                <span class="alpha-letter" data-letter="{{ $letter }}" style="
                    font-size:10px;
                    font-weight:700;
                    line-height:1;
                    padding:2px 3px;
                    cursor:pointer;
                    color:var(--text-muted);
                    border-radius:3px;
                    transition:all 0.15s ease;
                    width:100%;
                    text-align:center;
                    flex-shrink:0;
                ">{{ $letter }}</span>
                @endforeach
            </div>
        </div>

        <!-- Column 3: Cart -->
        <div class="col-md-3 cart-section shadow-lg">
            <div class="p-3 d-flex justify-content-between align-items-center"
                 style="background:var(--bg-sidebar);color:#fff;border-bottom:1px solid var(--border-sidebar);">
                <h5 class="m-0 fw-700" style="font-size:1rem;font-weight:700;">
                    <i class="fa-solid fa-cart-shopping me-2" style="color:var(--color-accent);"></i>Carrito
                </h5>
                <span class="badge" id="cartCount"
                      style="background:var(--color-accent);color:var(--color-secondary);font-family:'JetBrains Mono',monospace;font-weight:700;">0</span>
            </div>
            
            <div class="d-none">
                <select name="cliente_id" id="selectClienteId"><option value="" selected></option></select>
                <select name="comprobante_id"><option value="{{$comprobantes->first()->id ?? ''}}" selected></option></select>
                <select name="metodo_pago" id="selectMetodoPago">
                    @foreach($optionsMetodoPago as $op)
                        <option value="{{ $op->value }}" {{ $loop->first ? 'selected' : '' }}>{{ $op->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cart-items" id="cartItemsContainer">
                <div class="text-center text-muted mt-5" id="emptyCartMessage">
                    <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-50"></i>
                    <p>Carrito vacío</p>
                </div>
                <div id="cartList"></div>
            </div>

            <div class="cart-footer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fs-6 fw-bold text-secondary">TOTAL:</span>
                    <span class="total-display">{{$empresa->moneda->simbolo ?? '$'}} <span id="totalDisplay">0</span></span>
                </div>
                
                <input type="hidden" name="subtotal" id="inputSubtotal" value="0">

                <input type="hidden" name="total" id="inputTotal" value="0">

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small text-muted mb-0">Método de Pago:</label>
                        <span id="paymentBadge" class="badge bg-success" style="font-size:0.8rem;">
                            <i class="fa-solid fa-money-bill me-1"></i> EFECTIVO
                        </span>
                    </div>
                    <div id="fiadoClienteBadge" class="d-none mb-1">
                        <span class="badge bg-warning text-dark w-100 text-start" style="font-size:0.8rem;">
                            <i class="fa-solid fa-user me-1"></i>
                            <span id="fiadoClienteNombre">Sin cliente</span>
                            <button type="button" class="btn-close btn-close-sm ms-auto float-end" style="font-size:0.6rem;" onclick="cancelarFiado()"></button>
                        </span>
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:var(--color-secondary);color:#fff;border-color:var(--color-secondary);font-size:0.72rem;"
                                onclick="pagarCon('NEQUI')">
                                <i class="fas fa-mobile-alt me-1"></i>NEQUI
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:var(--color-primary);color:#fff;border-color:var(--color-primary);font-size:0.72rem;"
                                onclick="pagarCon('DAVIPLATA')">
                                <i class="fas fa-university me-1"></i>DAVI
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:var(--color-warning);color:#fff;border-color:var(--color-warning);font-size:0.72rem;"
                                onclick="iniciarFiado()">
                                <i class="fas fa-handshake me-1"></i>FIADO
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small text-muted mb-1">Pago Rápido (Efectivo):</label>
                    <div class="row g-1">
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();setExactCash()">Exacto</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(10000)">$10k</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(20000)">$20k</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(50000)">$50k</button></div>
                    </div>
                </div>

                <div class="row g-2 mb-3" id="efectivoCampos">
                    <div class="col-6">
                        <label class="small text-muted">Recibido</label>
                        <input type="text" id="dinero_recibido_display" class="form-control fw-bold" placeholder="0" oninput="updateReceived(this)">
                        <input type="hidden" id="dinero_recibido" name="monto_recibido">
                    </div>
                    <div class="col-6">
                        <label class="small text-muted">Vuelto</label>
                        <input type="text" id="vuelto_display" class="form-control fw-bold text-success bg-white" readonly placeholder="0">
                        <input type="hidden" id="vuelto" name="vuelto_entregado">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg fw-bold py-3" id="btnPay" disabled>
                        <i class="fa-solid fa-cash-register me-2"></i> COBRAR <i class="fa-solid fa-check ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-sm text-danger" onclick="cancelarVenta()">
                        <i class="fa-solid fa-times me-1"></i> Cancelar Venta
                    </button>
                </div>
                
                <!-- Indicador de atajos de teclado -->
                <div class="keyboard-hint" id="keyboardHint"></div>
            </div>
        </div>
    </div>
    <!-- Mobile Cart Toggle Button -->
    <button type="button" class="cart-toggle-mobile" id="cartToggleMobile" onclick="toggleMobileCart()">
        <i class="fa-solid fa-shopping-cart"></i>
        <span class="badge" id="cartCountMobile">0</span>
    </button>

    <!-- Overlay para cerrar el carrito móvil tocando fuera -->
    <div class="mobile-cart-overlay" id="mobileCartOverlay" onclick="toggleMobileCart()"></div>
    
    <!-- Modal Clientes -->
    <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:var(--color-secondary);">
                    <h5 class="modal-title" style="color:#fff;font-size:1rem;font-weight:700;">
                        <i class="fas fa-user me-2"></i>Seleccionar Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="clientSearchInput" placeholder="Buscar cliente..." onkeyup="filterClients()">
                    </div>
                    <div class="list-group" id="clientListGroup" style="max-height: 400px; overflow-y: auto;">
                        @foreach($clientes as $client)
                            @if(strtolower($client->persona->razon_social) == 'indefinido' || $client->persona->numero_documento == '1111111111')
                                @continue
                            @endif
                            <button type="button" class="list-group-item list-group-item-action client-item" 
                                    onclick="selectClient('{{$client->id}}', '{{addslashes($client->persona->razon_social)}}')">
                                {{$client->persona->razon_social}} <small class="text-muted">({{$client->persona->numero_documento}})</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script>
    var cart = [];
    var total = 0;
    var soundEnabled = true; // Cambiar a false para desactivar sonidos

    // Sonidos simples usando Web Audio API
    function playSound(frequency, duration) {
        if (!soundEnabled) return;
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + duration);
        } catch(e) {
            // Silenciar errores de audio
        }
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function showKeyboardHint(text) {
        const hint = document.getElementById('keyboardHint');
        hint.textContent = text;
        hint.classList.add('show');
        setTimeout(() => {
            hint.classList.remove('show');
        }, 1500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-collapse sidebar with animation
        if (!document.body.classList.contains('sb-sidenav-toggled')) {
            setTimeout(function() {
                document.body.classList.add('sb-sidenav-toggled');
            }, 500); // Delay to show animation
        }

        // Auto-focus en búsqueda
        const searchInput = document.getElementById('searchInput');
        searchInput.focus();

        // Búsqueda mejorada
        searchInput.addEventListener('keyup', function(e) {
            // Si presiona Enter en la búsqueda y hay un solo resultado, agregarlo
            if (e.key === 'Enter') {
                const visibleProducts = document.querySelectorAll('.product-item[style=""], .product-item:not([style*="display: none"])');
                if (visibleProducts.length === 1) {
                    visibleProducts[0].querySelector('.product-card').click();
                    this.value = '';
                    this.dispatchEvent(new Event('keyup')); // Limpiar filtro
                }
                return;
            }

            var value = this.value.toLowerCase();
            var items = document.querySelectorAll('.product-item');
            items.forEach(function(item) {
                var search = item.getAttribute('data-search');
                if (search.indexOf(value) > -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Mantener foco en búsqueda después de agregar productos
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.cart-section') && !e.target.closest('input')) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        // Atajos de teclado globales
        document.addEventListener('keydown', function(e) {


            // F9: Pago exacto
            if (e.key === 'F9') {
                e.preventDefault();
                setExactCash();
                showKeyboardHint('Pago Exacto');
                return;
            }

            // F10: $10k
            if (e.key === 'F10') {
                e.preventDefault();
                addCash(10000);
                showKeyboardHint('$10,000');
                return;
            }

            // F11: $20k
            if (e.key === 'F11') {
                e.preventDefault();
                addCash(20000);
                showKeyboardHint('$20,000');
                return;
            }

            // F12: $50k
            if (e.key === 'F12') {
                e.preventDefault();
                addCash(50000);
                showKeyboardHint('$50,000');
                return;
            }

            // Enter: Cobrar (si está habilitado)
            if (e.key === 'Enter' && !e.target.matches('#searchInput')) {
                const btnPay = document.getElementById('btnPay');
                if (!btnPay.disabled) {
                    e.preventDefault();
                    btnPay.click();
                }
                return;
            }

            // Escape: Cancelar venta
            if (e.key === 'Escape') {
                e.preventDefault();
                if (cart.length > 0) {
                    Swal.fire({
                        title: '¿Cancelar venta?',
                        text: 'Se perderán todos los productos del carrito',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, cancelar',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            cancelarVenta();
                            showKeyboardHint('Venta cancelada');
                        }
                    });
                }
                return;
            }

            // / (slash): Enfocar búsqueda
            if (e.key === '/' && !e.target.matches('input')) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
                return;
            }
        });
    });

    function filterCategory(catId, btn) {
        var buttons = document.querySelectorAll('.category-btn');
        buttons.forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        
        var items = document.querySelectorAll('.product-item');
        if(catId === 'all') {
            items.forEach(function(item) { item.style.display = ''; });
        } else {
            items.forEach(function(item) {
                if(item.getAttribute('data-category') == catId) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Re-enfocar búsqueda
        setTimeout(() => document.getElementById('searchInput').focus(), 100);
    }

    function addToCart(id, nombre, precio, stock, sigla) {
        id = id.toString();
        var existingItem = cart.find(function(item) { return item.id === id; });
        var currentQty = existingItem ? existingItem.cantidad : 0;
        
        if (currentQty + 1 > stock) {
            playSound(200, 0.2); // Sonido de error
            Swal.fire({ 
                icon: 'error', 
                title: 'Stock insuficiente', 
                toast: true, 
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 2000,
                timerProgressBar: true
            });
            return;
        }

        // Sonido de éxito
        playSound(800, 0.1);

        if (existingItem) {
            existingItem.cantidad++;
            existingItem.subtotal = existingItem.cantidad * existingItem.precio;
        } else {
            cart.push({ 
                id: id, 
                nombre: nombre, 
                precio: parseFloat(precio), 
                cantidad: 1, 
                sigla: sigla, 
                stock: parseInt(stock),
                subtotal: parseFloat(precio),
                isNew: true
            });
        }
        renderCart();
        
        // Mostrar notificación toast
        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            text: nombre,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    }

    function updateQuantityManual(id, newQty, maxStock) {
        newQty = parseInt(newQty);
        
        if (isNaN(newQty) || newQty < 1) {
            playSound(200, 0.2);
            Swal.fire({ 
                icon: 'warning', 
                title: 'Cantidad mínima: 1', 
                toast: true, 
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 2000 
            });
            renderCart();
            return;
        }
        
        if (newQty > maxStock) {
            playSound(200, 0.2);
            Swal.fire({ 
                icon: 'error', 
                title: 'Stock insuficiente (máx: ' + maxStock + ')', 
                toast: true, 
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 2000 
            });
            renderCart();
            return;
        }

        var item = cart.find(function(i) { return i.id === id; });
        if (item) {
            item.cantidad = newQty;
            item.subtotal = item.cantidad * item.precio;
            playSound(600, 0.1);
            renderCart();
        }
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
        }).then((result) => {
            if (result.isConfirmed) {
                cart = cart.filter(function(i) { return i.id !== id; });
                renderCart();
                Swal.fire({
                    icon: 'success',
                    title: 'Producto eliminado',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    }

    function renderCart() {
        var container = document.getElementById('cartList');
        container.innerHTML = '';
        total = 0;

        if (cart.length === 0) {
            document.getElementById('emptyCartMessage').style.display = 'block';
            document.getElementById('btnPay').disabled = true;
        } else {
            document.getElementById('emptyCartMessage').style.display = 'none';
            
            cart.forEach(function(item) {
                total += item.subtotal;
                var itemClass = item.isNew ? 'cart-item cart-item-new' : 'cart-item';
                item.isNew = false; // Resetear flag
                
                var row = '<div class="' + itemClass + '">' +
                    '<div class="flex-grow-1">' +
                        '<div class="fw-bold text-truncate" style="max-width: 140px;">' + item.nombre + '</div>' +
                        '<div class="d-flex align-items-center gap-2 mt-1">' +
                            '<small class="text-muted">Cantidad:</small>' +
                            '<input type="number" class="form-control form-control-sm" style="width: 60px;" ' +
                                'value="' + item.cantidad + '" ' +
                                'min="1" max="' + item.stock + '" ' +
                                'onchange="updateQuantityManual(\'' + item.id + '\', this.value, ' + item.stock + ')" ' +
                                'onclick="this.select()">' +
                            '<small class="text-muted">' + item.sigla + '</small>' +
                        '</div>' +
                        '<small class="text-muted">' + formatNumber(item.precio) + ' c/u</small>' +
                        '<input type="hidden" name="arrayidproducto[]" value="' + item.id + '">' +
                        '<input type="hidden" name="arraycantidad[]" value="' + item.cantidad + '">' +
                        '<input type="hidden" name="arrayprecioventa[]" value="' + item.precio + '">' +
                    '</div>' +
                    '<div class="text-end ms-2">' +
                        '<div class="fw-bold mb-2 text-primary">' + formatNumber(item.subtotal) + '</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeFromCart(\'' + item.id + '\')" title="Eliminar">' +
                            '<i class="fa-solid fa-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
                container.insertAdjacentHTML('beforeend', row);
            });
        }
        
        document.getElementById('totalDisplay').innerText = formatNumber(total);
        document.getElementById('inputTotal').value = total;
        document.getElementById('inputSubtotal').value = total;

        var totalItems = cart.reduce(function(acc, item) { return acc + item.cantidad; }, 0);
        document.getElementById('cartCount').innerText = totalItems;
        var mobileCount = document.getElementById('cartCountMobile');
        if (mobileCount) mobileCount.innerText = totalItems;

        calculateChange();
    }

    function setExactCash() {
        if(total === 0) return;
        document.getElementById('dinero_recibido').value = total;
        document.getElementById('dinero_recibido_display').value = formatNumber(total);
        playSound(600, 0.1);
        calculateChange();
    }

    function addCash(amount) {
        if(total === 0) return;
        document.getElementById('dinero_recibido').value = amount;
        document.getElementById('dinero_recibido_display').value = formatNumber(amount);
        playSound(600, 0.1);
        calculateChange();
    }

    function updateReceived(input) {
        var val = input.value.replace(/\D/g, '');
        var num = parseFloat(val);
        
        if(isNaN(num)) {
            document.getElementById('dinero_recibido').value = 0;
            input.value = '';
        } else {
            document.getElementById('dinero_recibido').value = num;
            input.value = formatNumber(num);
        }
        calculateChange();
    }

    function calculateChange() {
        var received = parseFloat(document.getElementById('dinero_recibido').value) || 0;
        
        if (received >= total && total > 0) {
            var change = received - total;
            document.getElementById('vuelto').value = change;
            document.getElementById('vuelto_display').value = formatNumber(change);
            document.getElementById('btnPay').disabled = false;
            playSound(1000, 0.1);
        } else {
            document.getElementById('vuelto').value = '';
            document.getElementById('vuelto_display').value = '';
            document.getElementById('btnPay').disabled = true;
        }
    }

    function toggleMobileCart() {
        var cartSection = document.querySelector('.cart-section');
        var overlay = document.getElementById('mobileCartOverlay');
        var isOpen = cartSection.classList.contains('mobile-open');
        if (isOpen) {
            cartSection.classList.remove('mobile-open');
            overlay.classList.remove('active');
        } else {
            cartSection.classList.add('mobile-open');
            overlay.classList.add('active');
        }
    }

    function cancelarVenta() {
        cart = [];
        document.getElementById('dinero_recibido').value = '';
        document.getElementById('dinero_recibido_display').value = '';
        document.getElementById('vuelto').value = '';
        document.getElementById('vuelto_display').value = '';
        pagarEfectivo();
        renderCart();
        document.getElementById('searchInput').focus();
    }

    function pagarEfectivo() {
        var select = document.getElementById('selectMetodoPago');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value === 'EFECTIVO') { select.selectedIndex = i; break; }
        }
        var badge = document.getElementById('paymentBadge');
        badge.className = 'badge bg-success';
        badge.style.fontSize = '0.8rem';
        badge.innerHTML = '<i class="fa-solid fa-money-bill me-1"></i> EFECTIVO';
        // Mostrar campos de efectivo
        document.getElementById('efectivoCampos').style.display = '';
    }

    function pagarCon(type) {
        if (total === 0) return;
        var select = document.getElementById('selectMetodoPago');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value === type) { select.selectedIndex = i; break; }
        }
        var badge = document.getElementById('paymentBadge');
        badge.style.fontSize = '0.8rem';
        if (type === 'NEQUI') {
            badge.className = 'badge';
            badge.style.backgroundColor = '#230836';
            badge.style.color = '#fff';
            badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> NEQUI';
        } else if (type === 'DAVIPLATA') {
            badge.className = 'badge bg-danger';
            badge.style.backgroundColor = '';
            badge.style.color = '';
            badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> DAVIPLATA';
        } else if (type === 'FIADO') {
            badge.className = 'badge bg-warning text-dark';
            badge.style.backgroundColor = '';
            badge.style.color = '';
            badge.innerHTML = '<i class="fa-solid fa-handshake me-1"></i> FIADO';
            // Ocultar campos de efectivo (no hay vuelto en fiado)
            document.getElementById('efectivoCampos').style.display = 'none';
            document.getElementById('dinero_recibido').value = total;
            document.getElementById('vuelto').value = 0;
            document.getElementById('btnPay').disabled = false;
            playSound(600, 0.1);
            return; // Salir antes de la lógica de abajo
        }
        document.getElementById('efectivoCampos').style.display = '';
        document.getElementById('dinero_recibido').value = total;
        document.getElementById('dinero_recibido_display').value = formatNumber(total);
        document.getElementById('vuelto').value = 0;
        document.getElementById('vuelto_display').value = '0';
        document.getElementById('btnPay').disabled = false;
        playSound(600, 0.1);
    }

    function iniciarFiado() {
        if (total === 0) return;
        // Abrir modal de selección de cliente
        var modal = new bootstrap.Modal(document.getElementById('clientModal'));
        modal.show();
        // Marcar que el modal se abrió por motivo FIADO
        document.getElementById('clientModal').dataset.context = 'fiado';
    }

    function cancelarFiado() {
        // Limpiar cliente seleccionado y volver a EFECTIVO
        document.getElementById('selectClienteId').value = '';
        document.getElementById('fiadoClienteBadge').classList.add('d-none');
        document.getElementById('fiadoClienteNombre').textContent = 'Sin cliente';
        pagarEfectivo();
        document.getElementById('btnPay').disabled = true;
    }

    function selectClient(id, nombre) {
        var context = document.getElementById('clientModal').dataset.context;
        // Actualizar el campo oculto cliente_id
        document.getElementById('selectClienteId').innerHTML = '<option value="' + id + '" selected>' + nombre + '</option>';

        // Cerrar modal
        bootstrap.Modal.getInstance(document.getElementById('clientModal')).hide();

        if (context === 'fiado') {
            // Completar flujo FIADO
            pagarCon('FIADO');
            document.getElementById('fiadoClienteBadge').classList.remove('d-none');
            document.getElementById('fiadoClienteNombre').textContent = nombre;
            document.getElementById('clientModal').dataset.context = '';
        }
    }

    // Prevenir envío tradicional y usar AJAX (Fetch API)
    document.getElementById('ventaForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar la recarga de página

        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'Agrega productos antes de cobrar'
            });
            return false;
        }

        // Deshabilitar botón para evitar doble cobro
        var btnPay = document.getElementById('btnPay');
        var originalBtnHtml = btnPay.innerHTML;
        btnPay.disabled = true;
        btnPay.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> PROCESANDO...';

        var formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                return response.json().then(data => { throw data; });
            }
        })
        .then(data => {
            // Sonido de éxito al procesar venta
            playSound(1200, 0.3);

            // Alerta de éxito pequeña
            Swal.fire({
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                title: data.message || 'Venta registrada con éxito'
            });

            // Descontar inventario en la vista (DOM) sin recargar
            cart.forEach(function(item) {
                var productEl = document.getElementById('product-' + item.id);
                if (productEl) {
                    var currentStock = parseInt(productEl.getAttribute('data-stock')) || 0;
                    var newStock = currentStock - item.cantidad;
                    productEl.setAttribute('data-stock', newStock);
                    
                    var stockDisplay = productEl.querySelector('.stock-display');
                    if (stockDisplay) {
                        stockDisplay.innerHTML = 'Stock: <span class="stock-count">' + newStock + '</span>';
                        if (newStock > 5) {
                            stockDisplay.className = 'text-success d-block stock-display';
                        } else {
                            stockDisplay.className = 'text-danger d-block stock-display';
                        }
                    }
                }
            });

            // Limpiar todo para la siguiente venta
            cart = [];
            total = 0;
            document.getElementById('inputSubtotal').value = '0';
            document.getElementById('inputTotal').value = '0';
            
            // Si estaba en fiado, regresar a efectivo normal
            cancelarFiado();
            renderCart(); // Esto repinta el carrito vacío

            // Restaurar botón
            btnPay.disabled = true; // Sigue disabled porque el carrito está vacío ahora
            btnPay.innerHTML = originalBtnHtml;
        })
        .catch(error => {
            console.error('Error procesando venta:', error);
            let errorMsg = error.message || 'Ocurrió un error inesperado al procesar la venta.';
            if (error.errors) {
                // Errores de validación de Laravel
                let messages = [];
                for (let field in error.errors) {
                    messages.push(error.errors[field][0]);
                }
                errorMsg = messages.join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });

            // Restaurar botón
            btnPay.disabled = false;
            btnPay.innerHTML = originalBtnHtml;
        });
    });

    // ================================================================
    // BARRA ALFABÉTICA VERTICAL
    // ================================================================
    (function initAlphaBar() {
        var alphaBar  = document.getElementById('alpha-bar');
        var container = document.getElementById('productsContainer');
        var grid      = document.querySelector('.product-grid');
        if (!alphaBar || !container || !grid) return;

        var letters = alphaBar.querySelectorAll('.alpha-letter');

        // Detectar qué letras tienen productos
        function updateLetterAvailability() {
            var available = new Set();
            container.querySelectorAll('.product-item:not([style*="display: none"])').forEach(function(el) {
                var l = el.getAttribute('data-nombre') || '';
                if (l) available.add(l.toUpperCase());
            });
            letters.forEach(function(el) {
                var l = el.getAttribute('data-letter');
                el.classList.remove('has-items', 'no-items');
                if (available.has(l)) {
                    el.classList.add('has-items');
                } else {
                    el.classList.add('no-items');
                }
            });
        }

        // Scroll hasta la primera tarjeta con esa letra
        function scrollToLetter(letter) {
            letter = letter.toUpperCase();
            var items = Array.from(container.querySelectorAll('.product-item:not([style*="display: none"])'));
            // Buscar coincidencia exacta, luego la siguiente disponible
            var target = items.find(function(el) {
                return (el.getAttribute('data-nombre') || '').toUpperCase() === letter;
            });
            if (!target) {
                // Buscar la primera letra después
                var sorted = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var idx    = sorted.indexOf(letter);
                for (var i = idx + 1; i < sorted.length && !target; i++) {
                    target = items.find(function(el) {
                        return (el.getAttribute('data-nombre') || '').toUpperCase() === sorted[i];
                    });
                }
            }
            if (!target) return;

            // Resaltar letra
            letters.forEach(function(el) { el.classList.remove('active'); });
            var activeLetter = alphaBar.querySelector('[data-letter="' + letter + '"]');
            if (activeLetter) activeLetter.classList.add('active');

            // Hacer scroll en el contenedor del grid
            var targetRect = target.getBoundingClientRect();
            var gridRect   = grid.getBoundingClientRect();
            grid.scrollTop += (targetRect.top - gridRect.top) - 60; // 60px offset para la búsqueda sticky
        }

        // Click en letra
        letters.forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                if (el.classList.contains('no-items')) return;
                scrollToLetter(el.getAttribute('data-letter'));
            });
        });

        // Touch deslizante (arrastrar el dedo por la barra)
        var touchActive = false;
        alphaBar.addEventListener('touchstart', function(e) {
            touchActive = true;
            handleTouch(e);
        }, { passive: false });
        alphaBar.addEventListener('touchmove', function(e) {
            if (!touchActive) return;
            e.preventDefault();
            handleTouch(e);
        }, { passive: false });
        alphaBar.addEventListener('touchend', function() { touchActive = false; });

        function handleTouch(e) {
            var touch  = e.touches[0];
            var el     = document.elementFromPoint(touch.clientX, touch.clientY);
            if (el && el.classList.contains('alpha-letter') && !el.classList.contains('no-items')) {
                scrollToLetter(el.getAttribute('data-letter'));
            }
        }

        // Inicializar disponibilidad al cargar
        updateLetterAvailability();

        // Actualizar al buscar / filtrar categoría
        var searchOrig = window.filterCategory;
        window.filterCategory = function(catId, btn) {
            if (searchOrig) searchOrig(catId, btn);
            setTimeout(updateLetterAvailability, 50);
        };
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                setTimeout(updateLetterAvailability, 50);
            });
        }
    })();
</script>
@endpush
