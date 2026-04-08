@extends('layouts.public')

@section('title', 'Bajo Cero | Inicio')

@section('content')
<!-- Hero Section -->
<header class="hero-section">
    <div class="container px-5">
        <div class="row gx-5 align-items-center justify-content-center">
            <div class="col-lg-8 col-xl-7 col-xxl-6">
                <div class="my-5 text-center text-xl-start">
                    <h1 class="hero-title mb-2">
                        Estilo que <br><span>Desafía</span>
                    </h1>
                    <p class="lead fw-normal text-white-50 mb-4">Descubre la nueva colección de chaquetas y gorras diseñadas para la vida urbana. Calidad premium, diseño exclusivo y la actitud que necesitas.</p>
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-xl-start">
                        <a class="btn-neon px-4 me-sm-3" href="{{ route('collection') }}">Ver Colección</a>
                        <a class="btn btn-outline-light btn-lg px-4" href="{{ route('contact') }}">Contactar</a>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-xxl-6 d-none d-xl-block text-center">
                <!-- Placeholder for Hero Image -->
                <div style="width: 100%; height: 500px; background: radial-gradient(circle, #00f2ff40 0%, transparent 70%); position: relative; border: 1px solid #333; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-snowflake fa-5x" style="color: var(--primary-color); opacity: 0.8; filter: drop-shadow(0 0 10px var(--primary-color));"></i>
                    <div style="position: absolute; bottom: 20px; right: 20px; color: #fff;">
                        <i class="fas fa-star fa-spin"></i> Nueva Colección
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Features Section -->
<section class="py-5 bg-darker" style="background-color: #0a0a0a;">
    <div class="container px-5 my-5">
        <div class="row gx-5">
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-collection"></i></div>
                <h2 class="h4 fw-bold text-white"><i class="fas fa-check-circle text-primary me-2"></i>Calidad Premium</h2>
                <p class="text-white-50">Materiales seleccionados rigurosamente para garantizar durabilidad y confort en cada prenda.</p>
            </div>
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-building"></i></div>
                <h2 class="h4 fw-bold text-white"><i class="fas fa-bolt text-warning me-2"></i>Diseño Exclusivo</h2>
                <p class="text-white-50">Ediciones limitadas y diseños únicos que no encontrarás en ningún otro lugar.</p>
            </div>
            <div class="col-lg-4">
                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-toggles2"></i></div>
                <h2 class="h4 fw-bold text-white"><i class="fas fa-box text-success me-2"></i>Envío Seguro</h2>
                <p class="text-white-50">Recibe tus productos en la puerta de tu casa con total garantía y seguimiento en tiempo real.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container px-5 my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bolder text-white">Tendencias <span style="color: var(--primary-color);">Destacadas</span></h2>
            <p class="lead fw-normal text-muted mb-0">Lo más buscado de la temporada</p>
        </div>
        <div class="row gx-5">
            @forelse($featuredProducts as $product)
                <div class="col-lg-3 col-md-6 mb-5">
                    <div class="card product-card h-100 border-0">
                        <!-- Product image-->
                        @if($product->image_path)
                            <img class="card-img-top" src="{{ $product->image_url }}" alt="{{ $product->nombre }}" />
                        @else
                           <div style="height: 300px; background-color: #222; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tshirt fa-3x text-muted"></i>
                           </div>
                        @endif
                        
                        <div class="badge-new">Nuevo</div>

                        <!-- Product details-->
                        <div class="product-info text-center">
                            <div class="product-brand">{{ $product->marca->nombre ?? 'Bajo Cero' }}</div>
                            <h5 class="product-title text-white text-truncate">{{ $product->nombre }}</h5>
                            <div class="d-flex justify-content-center small text-warning mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="product-price">${{ number_format($product->precio_venta, 0) }}</div>
                        </div>
                        <!-- Product actions-->
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center">
                                <a class="btn btn-outline-light mt-auto w-100" href="{{ route('product.show', $product->id) }}">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-white-50">
                    <p>No hay productos destacados por el momento.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Banner / Call to Action -->
<section class="py-5" style="background: linear-gradient(90deg, #000 0%, #0d1a1b 100%); border-top: 1px solid #333;">
    <div class="container px-5 my-5">
        <div class="row gx-5 justify-content-center">
            <div class="col-lg-8 col-xl-6 text-center">
                <h2 class="display-5 fw-bold text-white mb-3">¿LISTO PARA EL SIGUIENTE NIVEL?</h2>
                <p class="lead fw-normal text-muted mb-4">Explora nuestro catálogo completo y encuentra tu estilo. La colección Neon Edition ya está disponible.</p>
                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                    <a class="btn-neon px-5" href="{{ route('collection') }}">IR AL CATÁLOGO</a>
                </div>
            </div>
            <div class="col-lg-4 col-xl-4 text-center mt-4 mt-lg-0">
                 <!-- Banner Image Placeholder -->
                 <div style="border: 2px solid var(--primary-color); padding: 10px; transform: rotate(3deg);">
                    <div style="background-color: #111; height: 200px; display: flex; align-items: center; justify-content: center;">
                         <span class="text-white h5">NEON <br> EDITION</span>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</section>

<!-- TikTok Section -->
<section class="py-5 bg-black">
    <div class="container text-center">
         <h2 class="fw-bold mb-5 text-white">SÍGUENOS EN <span style="color: #00f2ff;">TIKTOK</span></h2>
         <div style="border: 1px dashed #333; padding: 3rem; display: inline-block;">
             <i class="fab fa-tiktok fa-3x text-white mb-3"></i>
             <p class="text-white">Descubre nuestro contenido exclusivo</p>
             <a href="#" class="btn btn-danger btn-sm">Seguir en TikTok</a>
         </div>
    </div>
</section>
@endsection
