<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\Caracteristica;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Inventario;
use App\Models\Persona;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OperacionCriticaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->withoutMiddleware(PermissionMiddleware::class);
        Permission::firstOrCreate(['name' => 'crear-venta']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('crear-venta');
        $this->actingAs($this->user);
    }

    public function test_fiado_sale_is_rejected_from_pos(): void
    {
        $this->openCaja();
        $producto = $this->createProductoConInventario(5, 3000);
        $clienteFiado = $this->createCliente('fiado');
        $comprobanteId = $this->createComprobante('Boleta');

        $response = $this->post(route('ventas.store'), [
            'cliente_id' => $clienteFiado->id,
            'comprobante_id' => $comprobanteId,
            'metodo_pago' => 'FIADO',
            'subtotal' => 6000,
            'total' => 6000,
            'monto_recibido' => 0,
            'vuelto_entregado' => 0,
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [2],
            'arrayprecioventa' => [3000],
        ]);

        $response->assertSessionHasErrors('metodo_pago');

        $this->assertDatabaseCount('ventas', 0);

        $this->assertDatabaseHas('inventario', [
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ]);

        $this->assertDatabaseCount('movimientos', 0);
    }

    public function test_credit_payment_requires_open_caja(): void
    {
        $clienteFiado = $this->createCliente('fiado');
        $comprobanteId = $this->createComprobante('Boleta');
        $caja = $this->openCaja();
        $caja->update(['estado' => 0]);

        DB::table('ventas')->insert([
            'id' => (string) Str::uuid(),
            'cliente_id' => $clienteFiado->id,
            'user_id' => $this->user->id,
            'caja_id' => $caja->id,
            'comprobante_id' => $comprobanteId,
            'numero_comprobante' => 'B-0000001',
            'metodo_pago' => 'FIADO',
            'fecha_hora' => now(),
            'subtotal' => 5000,
            'total' => 5000,
            'monto_recibido' => 0,
            'vuelto_entregado' => 0,
            'pagado' => 0,
            'saldo_pendiente' => 5000,
            'revertida' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('clientes.index'))
            ->post(route('clientes.pagarDeuda', $clienteFiado), [
                'monto' => 1000,
                'metodo_pago' => 'EFECTIVO',
            ]);

        $response->assertRedirect(route('clientes.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('ventas', [
            'cliente_id' => $clienteFiado->id,
            'saldo_pendiente' => 5000,
            'pagado' => 0,
        ]);

        $this->assertDatabaseCount('movimientos', 0);
    }

    private function createCliente(string $tipoCliente): Cliente
    {
        $documento = Documento::create(['nombre' => 'CC']);
        $persona = Persona::create([
            'razon_social' => 'Cliente ' . $tipoCliente,
            'tipo' => 'NATURAL',
            'documento_id' => $documento->id,
            'numero_documento' => (string) random_int(100000, 999999),
        ]);

        return Cliente::create([
            'persona_id' => $persona->id,
            'tipo_cliente' => $tipoCliente,
        ]);
    }

    private function createComprobante(string $nombre): string
    {
        $id = (string) Str::uuid();

        DB::table('comprobantes')->insert([
            'id' => $id,
            'nombre' => $nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createProductoConInventario(int $cantidad, int $precio): Producto
    {
        $caracteristica = Caracteristica::factory()->create();
        $presentacion = Presentacione::create([
            'caracteristica_id' => $caracteristica->id,
            'sigla' => 'UND',
        ]);

        $producto = Producto::create([
            'codigo' => (string) random_int(1000, 9999),
            'nombre' => 'Producto QA',
            'estado' => 1,
            'precio' => $precio,
            'presentacione_id' => $presentacion->id,
        ]);

        Inventario::create([
            'producto_id' => $producto->id,
            'ubicacione_id' => null,
            'cantidad' => $cantidad,
        ]);

        return $producto;
    }

    private function openCaja(): Caja
    {
        return Caja::create([
            'saldo_inicial' => 100000,
        ]);
    }
}
