<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

class PublicController extends Controller
{
    public function home()
    {
        // Fetch featured products for the home page (e.g., latest 4)
        $featuredProducts = Producto::with(['categoria', 'marca', 'presentacione'])
            ->latest()
            ->take(4)
            ->get();
            
        return view('welcome', compact('featuredProducts'));
    }

    public function collection(Request $request)
    {
        $productos = Producto::with(['categoria.caracteristica'])
            ->where('en_catalogo', 1)
            ->orderBy('nombre')
            ->get();

        $categorias = $productos
            ->map(fn($p) => $p->categoria?->caracteristica?->nombre)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('public.collection', compact('productos', 'categorias'));
    }

    public function show($id)
    {
        $product = Producto::with(['categoria', 'marca', 'presentacione', 'inventario'])->findOrFail($id);
        
        // Related products (same category, excluding current)
        $relatedProducts = Producto::where('categoria_id', $product->categoria_id)
            ->where('id', '!=', $id)
            ->take(4)
            ->get();

        return view('public.show', compact('product', 'relatedProducts'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function about()
    {
        return view('public.about');
    }
}
