<?php

namespace App\Http\Controllers;

use App\Models\CarritoPOS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoPOSController extends Controller
{
    public function index(): JsonResponse
    {
        $carritos = CarritoPOS::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($carritos);
    }

    public function sync(Request $request): JsonResponse
    {
        $carts = $request->input('carts', []);
        $userId = Auth::id();

        foreach ($carts as $cart) {
            if (empty($cart['uuid'])) continue;

            CarritoPOS::updateOrCreate(
                ['id' => $cart['uuid']],
                [
                    'user_id'         => $userId,
                    'nombre'          => $cart['name'] ?? null,
                    'items'           => $cart['items'] ?? [],
                    'metodo_pago'     => $cart['metodoPago'] ?? 'EFECTIVO',
                    'dinero_recibido' => $cart['dineroRecibido'] ?? 0,
                    'vuelto'          => $cart['vuelto'] ?? 0,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        CarritoPOS::where('id', $uuid)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }
}
