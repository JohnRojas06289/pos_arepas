<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AgenteIAController extends Controller
{
    /**
     * Tablas nunca accesibles, sin importar qué genere la IA.
     */
    private const BLOCKED_TABLES = [
        'users', 'password_resets', 'personal_access_tokens',
        'permissions', 'roles', 'model_has_permissions',
        'model_has_roles', 'role_has_permissions', 'activity_logs',
        'failed_jobs', 'jobs', 'notifications', 'sync_states',
    ];

    /**
     * Palabras clave SQL peligrosas que nunca deben aparecer.
     */
    private const BLOCKED_KEYWORDS = [
        'DROP', 'DELETE', 'UPDATE', 'INSERT', 'TRUNCATE',
        'ALTER', 'CREATE', 'EXEC', 'EXECUTE', 'GRANT', 'REVOKE',
        '--', '/*', 'pg_sleep', 'information_schema', 'pg_catalog',
        'pg_tables', 'pg_class',
    ];

    // ── Punto de entrada ──────────────────────────────────────────────────

    public function chat(Request $request): JsonResponse
    {
        $request->validate(['mensaje' => 'required|string|max:500']);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'El agente IA no está configurado.'], 503);
        }

        $userId       = auth()->id();
        $rateLimitKey = "agente-ia:{$userId}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $segundos = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => "Has alcanzado el límite de mensajes. Intenta de nuevo en {$segundos} segundos.",
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        $usuario = auth()->user();
        $esAdmin = $usuario && $usuario->hasRole('administrador');
        $mensaje = $request->input('mensaje');

        try {
            $resultado = $esAdmin
                ? $this->procesarConSQL($apiKey, $usuario, $mensaje)
                : $this->procesarBasico($apiKey, $usuario, $mensaje);

            return response()->json([
                'respuesta'   => $resultado['respuesta'],
                'sugerencias' => $resultado['sugerencias'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en AgenteIA', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al conectar con el agente IA. Intenta de nuevo.'], 500);
        }
    }

    // ── Flujo admin: Text-to-SQL ──────────────────────────────────────────

    private function procesarConSQL(string $apiKey, $usuario, string $mensaje): array
    {
        $esquema = $this->obtenerEsquema();
        $prompt  = $this->construirPromptSQL($usuario->name, $esquema);

        // Primera llamada: ¿necesita SQL?
        $primera = $this->llamarGeminiGenerarSQL($apiKey, $prompt, $mensaje);

        // Si la IA puede responder sin consultar la BD, devuelve directo
        if (empty($primera['needs_sql'])) {
            return [
                'respuesta'   => $primera['respuesta']   ?? 'No pude generar una respuesta.',
                'sugerencias' => $primera['sugerencias']  ?? [],
            ];
        }

        // Validar y ejecutar la query
        $sql = trim($primera['sql'] ?? '');
        if (empty($sql)) {
            return [
                'respuesta'   => 'No pude formular la consulta correctamente. Intenta reformular la pregunta.',
                'sugerencias' => ['¿Cuánto se vendió hoy?', '¿Qué productos tienen menos de 5 unidades?', '¿Cuál fue el día con más ventas este mes?'],
            ];
        }

        try {
            $resultados = $this->ejecutarConsultaSegura($sql);
        } catch (\Exception $e) {
            Log::warning('AgenteIA: SQL rechazada o fallida', [
                'sql'   => $sql,
                'error' => $e->getMessage(),
            ]);
            return [
                'respuesta'   => 'No pude obtener esa información. Intenta reformular la pregunta con otros términos.',
                'sugerencias' => ['¿Cuánto se vendió esta semana?', '¿Cuáles son los productos más vendidos?', '¿Cuánto se gastó este mes?'],
            ];
        }

        // Segunda llamada: interpretar resultados
        return $this->llamarGeminiInterpretarResultados($apiKey, $usuario->name, $mensaje, $resultados);
    }

    /**
     * Valida y ejecuta una consulta SQL de solo lectura.
     * Lanza excepción si la query es inválida o peligrosa.
     */
    private function ejecutarConsultaSegura(string $sql): array
    {
        // Solo SELECT
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            throw new \Exception('Solo se permiten consultas SELECT.');
        }

        // Palabras clave peligrosas
        foreach (self::BLOCKED_KEYWORDS as $keyword) {
            if (stripos($sql, $keyword) !== false) {
                throw new \Exception("Consulta bloqueada: contiene '{$keyword}'.");
            }
        }

        // Tablas bloqueadas
        foreach (self::BLOCKED_TABLES as $table) {
            if (preg_match('/\b' . preg_quote($table, '/') . '\b/i', $sql)) {
                throw new \Exception("Acceso a tabla restringida: '{$table}'.");
            }
        }

        // Eliminar punto y coma al final para evitar múltiples sentencias
        $sql = rtrim($sql, '; ');

        // Forzar límite de filas si la IA no lo incluyó
        if (!preg_match('/\bLIMIT\b/i', $sql)) {
            $sql .= ' LIMIT 100';
        }

        // Ejecutar con timeout de 5 segundos (PostgreSQL)
        DB::statement("SET LOCAL statement_timeout = '5000'");
        $resultados = DB::select($sql);

        Log::info('AgenteIA ejecutó consulta', [
            'usuario' => auth()->id(),
            'sql'     => $sql,
            'filas'   => count($resultados),
        ]);

        return array_map(fn($r) => (array) $r, $resultados);
    }

    /**
     * Esquema de la BD que se le pasa a la IA para que genere SQL correcto.
     * Solo incluye tablas de negocio — nada de usuarios ni permisos.
     */
    private function obtenerEsquema(): string
    {
        $ahora      = Carbon::now()->toDateTimeString();
        $diaActual  = Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $diaSemana  = Carbon::now()->dayOfWeek; // 0=Dom ... 6=Sab

        return <<<SCHEMA
Base de datos PostgreSQL.
Fecha/hora actual: {$ahora} (hora Colombia)
Hoy es: {$diaActual} (EXTRACT DOW = {$diaSemana} donde 0=Domingo, 1=Lunes ... 6=Sábado)

=== TABLAS DE NEGOCIO ===

ventas (id uuid PK, cliente_id uuid nullable FK clientes, user_id uuid FK users,
        numero_comprobante varchar, metodo_pago [EFECTIVO|TARJETA],
        fecha_hora timestamp, subtotal decimal, total decimal,
        revertida smallint [0=válida, 1=anulada], created_at)
  IMPORTANTE: Siempre filtra con WHERE revertida = 0 para excluir ventas anuladas.

producto_venta (id uuid PK, venta_id uuid FK ventas, producto_id uuid FK productos,
                cantidad int, precio_venta decimal, created_at)

productos (id uuid PK, codigo varchar, nombre varchar, precio decimal,
           estado tinyint [1=activo, 0=inactivo],
           categoria_id uuid FK categorias, marca_id uuid FK marcas,
           presentacione_id uuid FK presentaciones, created_at)

compras (id uuid PK, user_id uuid, proveedore_id uuid FK proveedores,
         numero_comprobante varchar nullable, metodo_pago [EFECTIVO|TARJETA],
         fecha_hora timestamp, subtotal decimal, total decimal, created_at)

compra_producto (id uuid PK, compra_id uuid FK compras, producto_id uuid FK productos,
                 cantidad int, precio_compra decimal, created_at)

inventario (id uuid PK, producto_id uuid FK productos UNIQUE,
            cantidad int, fecha_vencimiento date nullable)

kardex (id uuid PK, producto_id uuid FK productos,
        tipo_transaccion [COMPRA|VENTA|AJUSTE|APERTURA],
        descripcion_transaccion varchar,
        entrada int nullable, salida int nullable, saldo int,
        costo_unitario decimal, created_at)

gastos (id uuid PK, user_id uuid, categoria varchar, descripcion varchar,
        monto decimal, fecha date, metodo_pago varchar nullable,
        notas text nullable, created_at)

cajas (id uuid PK, nombre varchar, fecha_hora_apertura timestamp,
       fecha_hora_cierre timestamp nullable, saldo_inicial decimal,
       saldo_final decimal nullable, estado tinyint [1=abierta, 0=cerrada],
       user_id uuid, created_at)

movimientos (id uuid PK, tipo [VENTA|RETIRO], descripcion varchar,
             monto decimal, metodo_pago [EFECTIVO|TARJETA],
             caja_id uuid FK cajas, created_at)

categorias (id uuid PK, caracteristica_id uuid FK caracteristicas)
caracteristicas (id uuid PK, nombre varchar, tipo varchar, estado tinyint)

clientes (id uuid PK, persona_id uuid FK personas)
personas (id uuid PK, nombre varchar, telefono varchar, email varchar)

proveedores (id uuid PK, persona_id uuid FK personas)

=== JOINS FRECUENTES ===

-- Nombre de categoría de un producto:
JOIN categorias cat ON productos.categoria_id = cat.id
JOIN caracteristicas car ON cat.caracteristica_id = car.id
-- car.nombre es el nombre de la categoría

-- Nombre del cliente en una venta:
LEFT JOIN clientes cl ON ventas.cliente_id = cl.id
LEFT JOIN personas p ON cl.persona_id = p.id
-- p.nombre es el nombre del cliente (null = público general)

-- Nombre del proveedor en una compra:
JOIN proveedores prov ON compras.proveedore_id = prov.id
JOIN personas pers ON prov.persona_id = pers.id

=== FUNCIONES PostgreSQL ÚTILES ===
- EXTRACT(DOW FROM fecha_hora) → 0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb
- TO_CHAR(fecha_hora, 'YYYY-MM-DD') → fecha como texto
- DATE_TRUNC('week', fecha_hora) → inicio de semana
- NOW() → fecha/hora actual
- CURRENT_DATE → fecha actual
SCHEMA;
    }

    private function construirPromptSQL(string $nombreUsuario, string $esquema): string
    {
        return <<<PROMPT
Eres el asistente de datos del POS "Arepas Boyacenses". Hablas con {$nombreUsuario} (administrador).

Tu trabajo es responder preguntas de negocio sobre ventas, compras, inventario, gastos y cajas.

## ESQUEMA DE LA BASE DE DATOS:
{$esquema}

## REGLAS:
1. Si la pregunta necesita datos de la BD → genera SQL PostgreSQL válido.
2. Si puedes responder sin SQL (navegación, explicaciones) → responde directo.
3. SOLO genera SELECT. Nunca UPDATE, DELETE, INSERT, DROP, ALTER, TRUNCATE.
4. No accedas a: users, password_resets, personal_access_tokens, permissions, roles.
5. Siempre filtra ventas con revertida = 0.
6. Incluye LIMIT (máximo 50 filas).
7. Si la pregunta es ambigua, asume el período más lógico (hoy, este mes).
8. Los precios están en pesos colombianos (COP).

## FORMATO DE RESPUESTA (JSON válido, sin texto extra):

Si necesita SQL:
{"needs_sql": true, "sql": "SELECT ...", "intent": "descripción breve"}

Si NO necesita SQL:
{"needs_sql": false, "respuesta": "Markdown", "sugerencias": ["p1", "p2", "p3"]}
PROMPT;
    }

    private function llamarGeminiGenerarSQL(string $apiKey, string $prompt, string $mensaje): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $body = [
            'system_instruction' => ['parts' => [['text' => $prompt]]],
            'contents'           => [['role' => 'user', 'parts' => [['text' => $mensaje]]]],
            'generationConfig'   => [
                'temperature'      => 0.1,
                'maxOutputTokens'  => 400,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error ' . $response->status());
        }

        $raw = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $parsed = json_decode($raw, true);
        Log::info('AgenteIA primera llamada raw', ['raw' => $raw, 'parsed' => $parsed]);
        return $parsed ?? [];
    }

    private function llamarGeminiInterpretarResultados(
        string $apiKey,
        string $nombreUsuario,
        string $preguntaOriginal,
        array  $resultados
    ): array {
        $url            = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        $resultadosJson = json_encode($resultados, JSON_UNESCAPED_UNICODE);
        $total          = count($resultados);

        $prompt = <<<PROMPT
Eres el asistente del POS "Arepas Boyacenses". Hablas con {$nombreUsuario} (administrador).

El usuario preguntó: "{$preguntaOriginal}"

Se consultó la base de datos y se obtuvieron {$total} resultado(s):
{$resultadosJson}

Responde la pregunta del usuario de forma clara, concisa y amigable en español usando Markdown.
- Formatea montos como pesos colombianos: $1.500.000
- Presenta fechas de forma legible (ej: "lunes 21 de abril")
- Si no hay resultados, explícalo y sugiere por qué podría ser
- No expliques el proceso técnico ni menciones SQL
- Sé directo y útil

FORMATO OBLIGATORIO (JSON válido, sin texto extra):
{"respuesta": "Markdown", "sugerencias": ["pregunta1", "pregunta2", "pregunta3"]}
PROMPT;

        $body = [
            'system_instruction' => ['parts' => [['text' => $prompt]]],
            'contents'           => [['role' => 'user', 'parts' => [['text' => 'Interpreta los resultados y responde al usuario.']]]],
            'generationConfig'   => [
                'temperature'      => 0.3,
                'maxOutputTokens'  => 600,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error ' . $response->status());
        }

        $raw    = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $parsed = json_decode($raw, true) ?? [];

        return [
            'respuesta'   => $parsed['respuesta']   ?? 'No pude interpretar los resultados.',
            'sugerencias' => $parsed['sugerencias']  ?? [],
        ];
    }

    // ── Flujo no-admin: contexto estático ────────────────────────────────

    private function procesarBasico(string $apiKey, $usuario, string $mensaje): array
    {
        $inventario = Cache::remember('agente_ia_inventario', 300, function () {
            return Producto::with('inventario')
                ->where('estado', 1)
                ->get()
                ->map(fn($p) => [
                    'nombre' => $p->nombre,
                    'codigo' => $p->codigo,
                    'precio' => $p->precio,
                    'stock'  => $p->inventario?->cantidad ?? 0,
                ])
                ->toArray();
        });

        $nombre         = $usuario->name;
        $rol            = $usuario->getRoleNames()->first() ?? 'usuario';
        $inventarioJson = json_encode($inventario, JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
Eres el asistente del POS "Arepas Boyacenses". Hablas con {$nombre} (rol: {$rol}).
Responde en español, de forma concisa y amigable.
No tienes acceso a datos financieros — solo al inventario básico.
Si te preguntan sobre ventas, estadísticas o finanzas, indica que necesitan rol de administrador.

**Inventario actual:**
{$inventarioJson}

FORMATO OBLIGATORIO (JSON válido, sin texto extra):
{"respuesta": "Markdown", "sugerencias": ["p1", "p2", "p3"]}
PROMPT;

        return $this->llamarGemini($apiKey, $prompt, $mensaje);
    }

    private function llamarGemini(string $apiKey, string $promptSistema, string $mensajeUsuario): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $body = [
            'system_instruction' => ['parts' => [['text' => $promptSistema]]],
            'contents'           => [['role' => 'user', 'parts' => [['text' => $mensajeUsuario]]]],
            'generationConfig'   => [
                'temperature'      => 0.3,
                'maxOutputTokens'  => 600,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error ' . $response->status());
        }

        $raw    = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $parsed = json_decode($raw, true);

        return [
            'respuesta'   => $parsed['respuesta']   ?? 'No pude generar una respuesta.',
            'sugerencias' => $parsed['sugerencias']  ?? [],
        ];
    }
}
