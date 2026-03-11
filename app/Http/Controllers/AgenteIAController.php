<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AgenteIAController extends Controller
{
    /**
     * Procesa un mensaje del usuario y devuelve la respuesta del agente IA.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'mensaje' => 'required|string|max:500',
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'El agente IA no está configurado.'], 503);
        }

        // Rate limiting: máximo 20 mensajes por usuario por hora
        $userId = auth()->id();
        $rateLimitKey = "agente-ia:{$userId}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $segundos = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => "Has alcanzado el límite de mensajes. Intenta de nuevo en {$segundos} segundos."
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        $mensaje = $request->input('mensaje');
        $contexto = $this->construirContexto();
        $promptSistema = $this->construirPromptSistema($contexto);

        try {
            $resultado = $this->llamarGemini($apiKey, $promptSistema, $mensaje);
            return response()->json([
                'respuesta'   => $resultado['respuesta'],
                'sugerencias' => $resultado['sugerencias'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al llamar a Gemini API', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al conectar con el agente IA. Intenta de nuevo.'], 500);
        }
    }

    /**
     * Construye el contexto del negocio para el prompt del sistema.
     */
    private function construirContexto(): array
    {
        // Datos del usuario actual
        $usuario = auth()->user();
        $nombre = $usuario ? $usuario->name : 'Usuario';
        $rol = $usuario ? ($usuario->getRoleNames()->first() ?? 'desconocido') : 'desconocido';
        $esAdmin = $usuario && $usuario->hasRole('administrador');

        // Datos del inventario (cacheados 5 min para no sobrecargar la BD)
        // Todos los roles necesitan ver qué hay en stock para vender.
        $inventario = Cache::remember('agente_ia_inventario', 300, function () {
            return Producto::with('inventario')
                ->where('estado', 1)
                ->get()
                ->map(fn($p) => [
                    'nombre'   => $p->nombre,
                    'codigo'   => $p->codigo,
                    'precio'   => $p->precio,
                    'stock'    => $p->inventario?->cantidad ?? 0,
                    'vence'    => $p->inventario?->fecha_vencimiento,
                ])
                ->toArray();
        });

        $datosVentas = ['hoy' => 0, 'mes' => 0, 'top' => []];
        $stockBajo = [];

        // Solo consultamos y armamos la info financiera/gerencial si es admin
        if ($esAdmin) {
            $datosVentas = Cache::remember('agente_ia_ventas', 120, function () {
                $hoyInicio = Carbon::now()->startOfDay();
                $hoyFin    = Carbon::now()->endOfDay();
                
                $mesInicio = Carbon::now()->startOfMonth();
                $mesFin    = Carbon::now()->endOfMonth();

                $ventasHoy = Venta::whereBetween('created_at', [$hoyInicio, $hoyFin])->sum('total');
                $ventasMes = Venta::whereBetween('created_at', [$mesInicio, $mesFin])->sum('total');

                // 5 productos más vendidos del mes
                $masVendidos = \Illuminate\Support\Facades\DB::table('producto_venta')
                    ->join('ventas', 'producto_venta.venta_id', '=', 'ventas.id')
                    ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                    ->select('productos.nombre', \Illuminate\Support\Facades\DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                    ->whereBetween('ventas.created_at', [$mesInicio, $mesFin])
                    ->groupBy('productos.id', 'productos.nombre')
                    ->orderByDesc('total_vendido')
                    ->limit(5)
                    ->get()
                    ->toArray();

                return [
                    'hoy' => $ventasHoy,
                    'mes' => $ventasMes,
                    'top' => $masVendidos
                ];
            });

            $stockBajo = array_filter($inventario, fn($p) => $p['stock'] < 10 && $p['stock'] >= 0);
        }

        return [
            'usuario_nombre' => $nombre,
            'usuario_rol'    => $rol,
            'es_admin'       => $esAdmin,
            'inventario'     => $inventario,
            'ventas_hoy'     => $datosVentas['hoy'],
            'ventas_mes'     => $datosVentas['mes'],
            'mas_vendidos'   => $datosVentas['top'],
            'stock_bajo'     => array_values($stockBajo),
        ];
    }

    /**
     * Construye el prompt de sistema con el contexto del negocio.
     */
    private function construirPromptSistema(array $ctx): string
    {
        $inventarioJson = json_encode($ctx['inventario'], JSON_UNESCAPED_UNICODE);
        $nombreUsuario  = $ctx['usuario_nombre'];
        $rolUsuario     = $ctx['usuario_rol'];
        $esAdmin        = $ctx['es_admin'];

        $seccionEspecial = "";

        if ($esAdmin) {
            $masVendidosJson = json_encode($ctx['mas_vendidos'], JSON_UNESCAPED_UNICODE);
            $stockBajoJson   = json_encode($ctx['stock_bajo'], JSON_UNESCAPED_UNICODE);
            $ventasHoy       = number_format($ctx['ventas_hoy'], 0, ',', '.');
            $ventasMes       = number_format($ctx['ventas_mes'], 0, ',', '.');

            $seccionEspecial = <<<ADMIN
**Ventas:** 
- Hoy: \${$ventasHoy} COP
- Este Mes: \${$ventasMes} COP

**Top 5 Productos Más Vendidos Este Mes:**
{$masVendidosJson}

**Productos con stock bajo (menos de 10 unidades):**
{$stockBajoJson}
ADMIN;
        } else {
            $seccionEspecial = "No tienes permisos de Administrador para ver reportes financieros, estadísticas de ventas o alertas de stock a nivel gerencial. Si el usuario te pregunta detalles de dinero, ganancias o cuadre de caja, debes indicarle con amabilidad que su rol actual no cuenta con dichos permisos.";
        }

        return <<<PROMPT
Eres el asistente virtual del sistema POS "Arepas Boyacenses".
Actualmente estás hablando con {$nombreUsuario}, que tiene el rol de "{$rolUsuario}" en el sistema.
Tu función es darle soporte personalizado según su rol, responder a sus preguntas y ayudarle a navegar por el sistema. Trátalo siempre por su nombre de forma cordial.

## Reglas:
- Responde SIEMPRE en español, de forma concisa y amigable.
- Ten en cuenta el rol del usuario (Ej: Si es de ventas, enfócate en ayudarlo a vender; si es admin, puedes darle más panoramas generales).
- Si el usuario te pide una lista de productos, categorías, o cualquier tipo de datos, SIEMPRE organízala ALFABÉTICAMENTE.
- Utiliza Markdown generosamente para dar una estructura organizada y agradable a la vista (viñetas, negritas, separadores).
- Si no sabes algo, dilo claramente. No inventes datos.
- Para preguntas de navegación, da instrucciones claras con el menú del sistema.
- Los precios están en pesos colombianos (COP), sin decimales.

## FORMATO DE RESPUESTA OBLIGATORIO:
Debes responder SIEMPRE con un JSON válido sin ningún texto extra. El schema es:
{
  "respuesta": "<Tu respuesta en Markdown, puede incluir negritas, listas y saltos de línea>",
  "sugerencias": ["<pregunta 1>", "<pregunta 2>", "<pregunta 3>"]
}
Donde `sugerencias` es un array de exactamente 3 preguntas de seguimiento cortas, interesantes y contextuales basadas en lo que acabas de responder. Deben ser preguntas que el usuario probablemente quiera hacerte a continuación.

## Datos actuales del sistema (actualizados recién):

{$seccionEspecial}

**Inventario completo actual (Usa esto para confirmar precios y stock a los clientes):**
{$inventarioJson}

## Navegación del sistema:
- Punto de Venta → menú "Nueva Venta"
- Productos → menú "Productos" > "Lista de Productos"
- Inventario → menú "Inventario"
- Compras → menú "Compras"
- Clientes → menú "Clientes"
- Reportes → menú "Estadísticas"
- Caja → menú "Cajas"
- Usuarios → menú "Usuarios"
PROMPT;
    }

    /**
     * Llama a la API de Google Gemini Flash.
     * Devuelve array con 'respuesta' (string Markdown) y 'sugerencias' (array de strings).
     */
    private function llamarGemini(string $apiKey, string $promptSistema, string $mensajeUsuario): array
    {
        // endpoint para gemini-2.5-flash
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $body = [
            'system_instruction' => [
                'parts' => [['text' => $promptSistema]],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $mensajeUsuario]],
                ],
            ],
            'generationConfig' => [
                'temperature'       => 0.3,
                'maxOutputTokens'   => 600,
                'responseMimeType'  => 'application/json',
            ],
        ];

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('Gemini API respondió con error ' . $response->status());
        }

        $data = $response->json();
        $raw  = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        $parsed = json_decode($raw, true);

        return [
            'respuesta'   => $parsed['respuesta']   ?? 'No pude generar una respuesta. Intenta de nuevo.',
            'sugerencias' => $parsed['sugerencias']  ?? [],
        ];
    }
}
