<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class profileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-perfil', ['only' => ['index']]);
        $this->middleware('permission:editar-perfil', ['only' => ['update']]);
    }

    public function index(): View
    {
        return view('profile.index');
    }

    public function update(Request $request, User $profile): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $profile->id,
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $data = $request->only(['name', 'email']);

            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }

            $profile->update($data);

            return redirect()->route('profile.index')->with('success', 'Cambios guardados');
        } catch (Throwable $e) {
            Log::error('Error al actualizar perfil', ['error' => $e->getMessage()]);
            return redirect()->route('profile.index')->with('error', 'Ups, algo falló');
        }
    }
}
