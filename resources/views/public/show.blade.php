@extends('layouts.public')

@section('title', 'Bajo Cero | ' . $product->nombre)

@section('content')
<div class="container px-5 mt-5 pt-5">
    <div class="row gx-5 align-items-center">
        <!-- Product Image -->
        <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="card bg-dark border-secondary overflow-hidden">
                @if($product->image_path)
                    <img class="img-fluid w-100" src="{{ Storage::url($product->image_path) }}" alt="{{ $product->nombre }}" style="max-height: 600px; object-fit: cover;" />
                @else
                    <div style="height: 500px; background-color: #222; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-camera fa-5x text-muted"></i>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="mb-3">
                <span class="badge bg-primary text-dark fw-bold px-3 py-2">{{ $product->marca->nombre ?? 'Bajo Cero' }}</span>
                <span class="badge bg-dark border border-secondary text-white fw-normal px-3 py-2 ms-2">{{ $product->categoria->nombre ?? 'General' }}</span>
            </div>
            
            <h1 class="display-4 fw-bolder text-white mb-3">{{ $product->nombre }}</h1>
            
            <div class="d-flex align-items-center mb-4">
                <h2 class="text-primary fw-bold mb-0 me-3">${{ number_format($product->precio_venta, 0) }}</h2>
                @if($product->inventario)
                    @if($product->inventario->stock > 0)
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>En Stock ({{ $product->inventario->stock }})</span>
                    @else
                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Agotado</span>
                    @endif
                @else
                     <span class="text-muted"><i class="fas fa-question-circle me-1"></i>Consultar disponibilidad</span>
                @endif
            </div>

            <p class="lead text-white-50 mb-5">{{ $product->descripcion ?? 'Sin descripción disponible para este producto. Diseñado para resistir y destacar en el entorno urbano.' }}</p>

            <!-- Size Selector (Visual) -->
            <div class="mb-5">
                <h6 class="text-white text-uppercase fw-bold mb-3">Presentación</h6>
                <div class="d-flex gap-2">
                    @if($product->presentacione)
                         <button class="btn btn-outline-light active">{{ $product->presentacione->nombre }}</button>
                    @else
                        <span class="text-white-50">Sin presentación definida</span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-3">
                <button class="btn btn-neon px-5 py-3 flex-grow-1">
                    <i class="fas fa-shopping-bag me-2"></i> AGREGAR AL CARRITO
                </button>
                <button class="btn btn-outline-light px-4 py-3">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            
            <div class="mt-4 pt-4 border-top border-secondary">
                 <div class="d-flex gap-4 text-muted small">
                     <span><i class="fas fa-truck me-2"></i>Envío a toda Colombia</span>
                     <span><i class="fas fa-shield-alt me-2"></i>Garantía de 30 días</span>
                 </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-5 pt-5">
        <h3 class="fw-bold text-white mb-4">PRODUCTOS RELACIONADOS</h3>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4">
            @forelse($relatedProducts as $related)
                <div class="col mb-5">
                    <div class="card product-card h-100 border-0">
                        @if($related->image_path)
                            <img class="card-img-top" src="{{ Storage::url($related->image_path) }}" alt="{{ $related->nombre }}" style="height: 200px; object-fit: cover;" />
                        @else
                            <div style="height: 200px; background-color: #222; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-camera fa-2x text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body p-4">
                            <div class="text-start">
                                <h5 class="fw-bolder text-white text-truncate">{{ $related->nombre }}</h5>
                                <div class="text-info fw-bold">${{ number_format($related->precio_venta, 0) }}</div>
                            </div>
                        </div>
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center">
                                <a class="btn btn-outline-light mt-auto w-100" href="{{ route('product.show', $related->id) }}">Ver</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-white-50">No hay productos relacionados.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
