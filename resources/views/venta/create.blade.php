@extends('layouts.app')

@section('title', 'Realizar venta')

@push('css')
<style>
    body { overflow: hidden; }
    .pos-container { height: calc(100vh - 56px); overflow: hidden; }
    
    /* Sidebar de categorías mejorada */
    .category-sidebar { 
        height: 100%; 
        overflow-y: auto; 
        background: linear-gradient(180deg, #1a1d23 0%, #212529 100%);
        border-right: 2px solid #343a40; 
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .product-grid { 
        height: 100%; 
        overflow-y: auto; 
        padding: 1.25rem; 
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .cart-section { 
        height: 100%; 
        display: flex; 
        flex-direction: column; 
        background-color: #fff; 
        border-left: 2px solid #dee2e6;
        box-shadow: -2px 0 10px rgba(0,0,0,0.05);
    }

    /* Botones de categoría más grandes y claros */
    .category-btn { 
        width: 100%; 
        text-align: left; 
        padding: 18px 20px; 
        background: transparent; 
        border: none; 
        border-bottom: 1px solid #343a40; 
        color: #adb5bd; 
        transition: all 0.3s ease; 
        font-weight: 500; 
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }
    
    .category-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: #f59e0b;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    
    .category-btn:hover, .category-btn.active { 
        background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%);
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
        margin-right: 12px; 
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
        border: 2px solid #e2e8f0; 
        overflow: hidden;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .product-card:hover { 
        transform: translateY(-4px) scale(1.02); 
        border-color: #f59e0b; 
        box-shadow: 0 8px 16px rgba(245, 158, 11, 0.2);
    }
    
    .product-card:active { 
        transform: translateY(-2px) scale(0.98); 
    }
    
    .product-img-container { 
        height: 140px; 
        overflow: hidden; 
        background: linear-gradient(135deg, #f5f5f5 0%, #e9ecef 100%);
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
        font-weight: bold; 
        color: #f59e0b; 
        font-size: 1.15rem;
    }
    
    .product-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    /* Items del carrito más espaciados */
    .cart-items { 
        flex-grow: 1; 
        overflow-y: auto; 
        padding: 0;
        background: #fafafa;
    }
    
    .cart-item { 
        padding: 15px; 
        border-bottom: 1px solid #e5e7eb; 
        display: flex; 
        align-items: center; 
        justify-content: space-between;
        background: #fff;
        margin: 8px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
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
        padding: 1.25rem; 
        background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
        border-top: 2px solid #dee2e6;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    
    .smart-cash-btn { 
        font-size: 0.9rem; 
        font-weight: 700; 
        padding: 10px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 2px solid #dee2e6;
    }
    
    .smart-cash-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #f59e0b;
        background-color: #fff3e0;
    }
    
    /* Total más prominente */
    .total-display {
        font-size: 2.5rem !important;
        font-weight: 900 !important;
        color: #059669 !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Botón de cobrar mejorado */
    #btnPay {
        font-size: 1.25rem;
        font-weight: 900;
        padding: 18px !important;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        transition: all 0.3s ease;
        border: none;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
    
    #btnPay:not(:disabled):hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.4);
    }
    
    #btnPay:not(:disabled):active {
        transform: translateY(-1px);
    }
    
    #btnPay:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Inputs mejorados */
    .form-control {
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        padding: 10px 12px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
    
    /* Responsive mejorado */
    @media (max-width: 768px) {
        .product-card {
            border-radius: 10px;
        }
        
        .product-img-container {
            height: 120px;
        }
        
        .total-display {
            font-size: 2rem !important;
        }
        
        #btnPay {
            font-size: 1.1rem;
            padding: 16px !important;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<form action="{{ route('ventas.store') }}" method="post" id="ventaForm" class="h-100">
    @csrf
    <div class="row g-0 pos-container">
        
        <!-- Column 1: Categories -->
        <div class="col-md-2 category-sidebar d-none d-md-block">
            <div class="p-3 text-white border-bottom border-secondary">
                <h5 class="m-0"><i class="fa-solid fa-layer-group me-2"></i>Categorías</h5>
            </div>
            <button type="button" class="category-btn active" onclick="filterCategory('all', this)">
                <i class="fa-solid fa-border-all"></i> Todo
                <span class="shortcut-hint">F1</span>
            </button>
            @foreach ($categorias as $cat)
            <button type="button" class="category-btn" onclick="filterCategory('{{$cat->id}}', this)">
                <i class="fa-solid fa-tag"></i> {{$cat->caracteristica->nombre}}
                <span class="shortcut-hint">F{{$loop->iteration + 1}}</span>
            </button>
            @endforeach
        </div>

        <!-- Column 2: Products -->
        <div class="col-md-6 col-lg-7 product-grid">
            <div class="sticky-top bg-light pb-3 pt-1 mb-2" style="z-index: 10;">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar producto..." autofocus>
                </div>
            </div>

            <div class="row g-3" id="productsContainer">
                @foreach ($productos as $item)
                <div class="col-6 col-md-4 col-lg-3 product-item" 
                     data-category="{{$item->categoria_id}}"
                     data-search="{{ strtolower($item->nombre . ' ' . $item->codigo) }}">
                    <div class="card h-100 product-card shadow-sm border-0" onclick="addToCart({{$item->id}}, '{{addslashes($item->nombre)}}', {{$item->precio}}, {{$item->cantidad}}, '{{$item->sigla}}')">
                        <div class="product-img-container">
                            @if($item->img_path)
                                <img src="{{ asset('storage/'.$item->img_path) }}" class="product-img" alt="{{$item->nombre}}" onerror="this.parentElement.innerHTML='<div class=\'text-muted text-center p-3\'><i class=\'fa-solid fa-image fa-3x mb-2 opacity-25\'></i><br><small>Sin imagen</small></div>'">
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="fa-solid fa-image fa-3x mb-2 opacity-25"></i>
                                    <br><small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title mb-1 text-truncate product-name" title="{{$item->nombre}}">{{$item->nombre}}</h6>
                            <div class="product-price">{{$empresa->moneda->simbolo}} {{ number_format($item->precio, 0, ',', '.') }}</div>
                            <small class="text-{{ $item->cantidad > 5 ? 'success' : 'danger' }} d-block" style="font-size: 0.7rem;">
                                Stock: {{$item->cantidad}}
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Column 3: Cart -->
        <div class="col-md-4 col-lg-3 cart-section shadow-lg">
            <div class="p-3 bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="m-0"><i class="fa-solid fa-cart-shopping me-2"></i>Carrito</h5>
                <span class="badge bg-warning text-dark" id="cartCount">0</span>
            </div>
            
            <div class="d-none">
                <select name="cliente_id"><option value="{{$clientes->first()->id ?? ''}}" selected></option></select>
                <select name="comprobante_id"><option value="{{$comprobantes->first()->id ?? ''}}" selected></option></select>
                <select name="metodo_pago"><option value="{{$optionsMetodoPago[0]->value ?? ''}}" selected></option></select>
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
                    <span class="total-display">{{$empresa->moneda->simbolo}} <span id="totalDisplay">0</span></span>
                </div>
                
                <input type="hidden" name="subtotal" id="inputSubtotal" value="0">
                <input type="hidden" name="impuesto" id="inputImpuesto" value="0">
                <input type="hidden" name="total" id="inputTotal" value="0">

                <div class="mb-2">
                    <label class="form-label small text-muted mb-1">Pago Rápido:</label>
                    <div class="row g-1">
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="setExactCash()">Exacto</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="addCash(10000)">$10k</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="addCash(20000)">$20k</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="addCash(50000)">$50k</button></div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
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
            // F1-F8: Categorías
            if (e.key >= 'F1' && e.key <= 'F8') {
                e.preventDefault();
                const categoryButtons = document.querySelectorAll('.category-btn');
                const index = parseInt(e.key.substring(1)) - 1;
                if (categoryButtons[index]) {
                    categoryButtons[index].click();
                    showKeyboardHint('Categoría: ' + categoryButtons[index].textContent.trim());
                }
                return;
            }

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
        document.getElementById('cartCount').innerText = cart.reduce(function(acc, item) { return acc + item.cantidad; }, 0);

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

    function cancelarVenta() {
        cart = [];
        document.getElementById('dinero_recibido').value = '';
        document.getElementById('dinero_recibido_display').value = '';
        document.getElementById('vuelto').value = '';
        document.getElementById('vuelto_display').value = '';
        renderCart();
        document.getElementById('searchInput').focus();
    }

    // Prevenir envío accidental del formulario
    document.getElementById('ventaForm').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'Agrega productos antes de cobrar'
            });
            return false;
        }
        
        // Sonido de éxito al procesar venta
        playSound(1200, 0.3);
    });
</script>
@endpush