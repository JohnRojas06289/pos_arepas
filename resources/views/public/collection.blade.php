@extends('layouts.public')

@section('title', 'Bajo Cero | Colección')

@section('content')
<div class="container px-5 mt-5 pt-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bolder text-white">NUESTRO CATÁLOGO</h1>
        <p class="lead fw-normal text-muted mb-0">Descubre las últimas tendencias en moda urbana</p>
        <div style="width: 50px; height: 3px; background-color: var(--primary-color); margin: 20px auto;"></div>
    </div>

    <div class="row gx-5">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-5">
            <div class="card bg-dark border-secondary text-white p-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-sliders-h me-2"></i>FILTROS</h4>
                
                <form action="{{ route('collection') }}" method="GET">
                    <!-- Text Search -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">BUSCAR</label>
                        <input type="text" name="search" class="form-control form-control-dark" placeholder="Nombre del producto..." value="{{ request('search') }}">
                    </div>

                    <!-- Categories -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">CATEGORÍA</label>
                        <select name="categoria" class="form-select form-control-dark">
                            <option value="all">Todas las categorías</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->nombre }}" {{ request('categoria') == $cat->nombre ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brands -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">MARCA</label>
                        <select name="marca" class="form-select form-control-dark">
                            <option value="all">Todas las marcas</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->nombre }}" {{ request('marca') == $marca->nombre ? 'selected' : '' }}>
                                    {{ $marca->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range (Visual only for now) -->
                    <div class="mb-4">
                         <label class="form-label text-muted small fw-bold">PRECIO MÁXIMO</label>
                         <input type="range" class="form-range" min="0" max="500000" step="10000" id="priceRange">
                         <div class="d-flex justify-content-between text-muted small">
                             <span>$0</span>
                             <span>$500k+</span>
                         </div>
                    </div>

                    <button type="submit" class="btn btn-neon w-100 text-center">APLICAR FILTROS</button>
                    
                    @if(request()->hasAny(['search', 'categoria', 'marca']))
                        <a href="{{ route('collection') }}" class="btn btn-link text-white-50 w-100 mt-2">Limpiar Filtros</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-3 justify-content-center">
                @forelse($products as $product)
                    <div class="col mb-5">
                        <div class="card product-card h-100 border-0">
                            <!-- Sale badge-->
                            @if(rand(0,1))
                                <div class="badge bg-success text-white position-absolute" style="top: 0.5rem; right: 0.5rem">DISPONIBLE</div>
                            @else
                                 <div class="badge bg-danger text-white position-absolute" style="top: 0.5rem; right: 0.5rem">AGOTADO</div>
                            @endif

                            <!-- Product image-->
                            @if($product->image_path)
                                <img class="card-img-top" src="{{ $product->image_url }}" alt="{{ $product->nombre }}" />
                            @else
                                <div style="height: 250px; background-color: #222; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-camera fa-2x text-muted"></i>
                                </div>
                            @endif

                            <!-- Product details-->
                            <div class="card-body p-4">
                                <div class="text-start">
                                    <div class="small text-muted text-uppercase mb-1">{{ $product->marca->nombre ?? 'Bajo Cero' }}</div>
                                    <h5 class="fw-bolder text-white text-truncate">{{ $product->nombre }}</h5>
                                    <div class="text-info fw-bold fs-5">${{ number_format($product->precio_venta, 0) }}</div>
                                </div>
                            </div>
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center">
                                    <a class="btn btn-outline-info mt-auto w-100" href="{{ route('product.show', $product->id) }}">VER DETALLES</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h3>No encontramos productos</h3>
                            <p>Intenta ajustar tus filtros de búsqueda.</p>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
