@extends('layouts.app')

@section('title', 'Realizar venta')

@push('css')
<style>
    body { overflow: hidden; }
    .pos-container { height: calc(100vh - 56px); overflow: hidden; }
    
    .category-sidebar { height: 100%; overflow-y: auto; background-color: #212529; border-right: 1px solid #343a40; }
    .product-grid { height: 100%; overflow-y: auto; padding: 1rem; background-color: #f8f9fa; }
    .cart-section { height: 100%; display: flex; flex-direction: column; background-color: #fff; border-left: 1px solid #dee2e6; }

    .category-btn { width: 100%; text-align: left; padding: 15px 20px; background: transparent; border: none; border-bottom: 1px solid #343a40; color: #adb5bd; transition: all 0.2s; font-weight: 500; }
    .category-btn:hover, .category-btn.active { background-color: #f59e0b; color: #000; font-weight: bold; }
    .category-btn i { width: 25px; text-align: center; margin-right: 10px; }

    .product-card { cursor: pointer; transition: transform 0.1s; border: 1px solid #e2e8f0; overflow: hidden; }
    .product-card:hover { transform: scale(1.02); border-color: #f59e0b; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .product-card:active { transform: scale(0.98); }
    .product-img-container { height: 120px; overflow: hidden; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center; }
    .product-img { width: 100%; height: 100%; object-fit: cover; }
    .product-price { font-weight: bold; color: #f59e0b; }
    
    .cart-items { flex-grow: 1; overflow-y: auto; padding: 0; }
    .cart-item { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
    .cart-footer { padding: 1rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; }
    .smart-cash-btn { font-size: 0.85rem; font-weight: 600; padding: 8px 5px; }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 3px; }
    footer { display: none !important; }
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
            </button>
            @foreach ($categorias as $cat)
            <button type="button" class="category-btn" onclick="filterCategory('{{$cat->id}}', this)">
                <i class="fa-solid fa-tag"></i> {{$cat->caracteristica->nombre}}
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
                                <img src="{{ asset('storage/productos/'.$item->img_path) }}" class="product-img" alt="{{$item->nombre}}" onerror="this.parentElement.innerHTML='<div class=\'text-muted text-center p-3\'><i class=\'fa-solid fa-image fa-3x mb-2 opacity-25\'></i><br><small>Sin imagen</small></div>'">
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="fa-solid fa-image fa-3x mb-2 opacity-25"></i>
                                    <br><small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title mb-1 text-truncate small fw-bold" title="{{$item->nombre}}">{{$item->nombre}}</h6>
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
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 fw-bold text-secondary">Total:</span>
                    <span class="fs-4 fw-bold text-dark">{{$empresa->moneda->simbolo}} <span id="totalDisplay">0</span></span>
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
                        COBRAR <i class="fa-solid fa-check ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-sm text-danger" onclick="cancelarVenta()">
                        Cancelar Venta
                    </button>
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

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput').addEventListener('keyup', function() {
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
    }

    function addToCart(id, nombre, precio, stock, sigla) {
        id = id.toString();
        var existingItem = cart.find(function(item) { return item.id === id; });
        var currentQty = existingItem ? existingItem.cantidad : 0;
        
        if (currentQty + 1 > stock) {
            Swal.fire({ icon: 'error', title: 'Stock insuficiente', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            return;
        }

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
                subtotal: parseFloat(precio) 
            });
        }
        renderCart();
    }

    function updateQuantityManual(id, newQty, maxStock) {
        newQty = parseInt(newQty);
        
        if (isNaN(newQty) || newQty < 1) {
            Swal.fire({ icon: 'warning', title: 'Cantidad mínima: 1', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            renderCart();
            return;
        }
        
        if (newQty > maxStock) {
            Swal.fire({ icon: 'error', title: 'Stock insuficiente (máx: ' + maxStock + ')', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            renderCart();
            return;
        }

        var item = cart.find(function(i) { return i.id === id; });
        if (item) {
            item.cantidad = newQty;
            item.subtotal = item.cantidad * item.precio;
            renderCart();
        }
    }

    function removeFromCart(id) {
        cart = cart.filter(function(i) { return i.id !== id; });
        renderCart();
    }

    function updateQuantity(id, change) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;

        var newQty = item.cantidad + change;
        if (newQty <= 0) {
            cart = cart.filter(function(i) { return i.id !== id; });
        } else {
            item.cantidad = newQty;
            item.subtotal = item.cantidad * item.precio;
        }
        renderCart();
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
                var row = '<div class="cart-item">' +
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
        calculateChange();
    }

    function addCash(amount) {
        if(total === 0) return;
        document.getElementById('dinero_recibido').value = amount;
        document.getElementById('dinero_recibido_display').value = formatNumber(amount);
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
    }
</script>
@endpush