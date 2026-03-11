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
            $respuesta = $this->llamarGemini($apiKey, $promptSistema, $mensaje);
            return response()->json(['respuesta' => $respuesta]);
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

        // Datos del inventario (cacheados 5 min para no sobrecargar la BD)
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

        // Ventas del día
        $hoyInicio = Carbon::now()->startOfDay();
        $hoyFin    = Carbon::now()->endOfDay();
        $ventasHoy = Cache::remember('agente_ia_ventas_hoy', 120, function () use ($hoyInicio, $hoyFin) {
            return Venta::whereBetween('created_at', [$hoyInicio, $hoyFin])->sum('total');
        });

        // Productos con stock bajo (< 10)
        $stockBajo = array_filter($inventario, fn($p) => $p['stock'] < 10 && $p['stock'] >= 0);

        return [
            'usuario_nombre' => $nombre,
            'usuario_rol'    => $rol,
            'inventario'     => $inventario,
            'ventas_hoy'     => $ventasHoy,
            'stock_bajo'     => array_values($stockBajo),
        ];
    }

    /**
     * Construye el prompt de sistema con el contexto del negocio.
     */
    private function construirPromptSistema(array $ctx): string
    {
        $inventarioJson = json_encode($ctx['inventario'], JSON_UNESCAPED_UNICODE);
        $stockBajoJson  = json_encode($ctx['stock_bajo'], JSON_UNESCAPED_UNICODE);
        $ventasHoy      = number_format($ctx['ventas_hoy'], 0, ',', '.');
        $nombreUsuario  = $ctx['usuario_nombre'];
        $rolUsuario     = $ctx['usuario_rol'];

        return <<<PROMPT
Eres el asistente virtual del sistema POS "Arepas Boyacenses".
Actualmente estás hablando con {$nombreUsuario}, que tiene el rol de "{$rolUsuario}" en el sistema.
Tu función es darle soporte personalizado según su rol, responder a sus preguntas y ayudarle a navegar por el sistema. Trátalo siempre por su nombre de forma cordial.

## Reglas:
- Responde SIEMPRE en español, de forma concisa y amigable.
- Ten en cuenta el rol del usuario (Ej: Si es de ventas, enfócate en ayudarlo a vender; si es admin, puedes darle más panoramas generales).
- Si no sabes algo, dilo claramente. No inventes datos.
- Para preguntas de navegación, da instrucciones claras con el menú del sistema.
- Los precios están en pesos colombianos (COP), sin decimales.

## Datos actuales del sistema (actualizado hace pocos minutos):

**Ventas de hoy:** \${$ventasHoy} COP

**Productos con stock bajo (menos de 10 unidades):**
{$stockBajoJson}

**Inventario completo:**
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
     */
    private function llamarGemini(string $apiKey, string $promptSistema, string $mensajeUsuario): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key={$apiKey}";

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
                'temperature'     => 0.3,
                'maxOutputTokens' => 512,
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
        return $data['candidates'][0]['content']['parts'][0]['text']
            ?? 'No pude generar una respuesta. Intenta de nuevo.';
    }
}
