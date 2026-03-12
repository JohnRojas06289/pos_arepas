<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Empleado;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Throwable;

class userController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-user|crear-user|editar-user|eliminar-user', ['only' => ['index']]);
        $this->middleware('permission:crear-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-user', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $users = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'administrador'))
            ->orderBy('name', 'asc')
            ->get();

        return view('user.index', compact('users'));
    }

    public function create(): View
    {
        $roles     = Role::where('name', '!=', 'administrador')->get();
        $empleados = Empleado::all();

        return view('user.create', compact('roles', 'empleados'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $data         = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $user->assignRole($request->role);

            DB::commit();
            ActivityLogService::log('Creación de usuario', 'Usuarios', $request->validated());

            return redirect()->route('users.index')->with('success', 'Usuario registrado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear el usuario', ['error' => $e->getMessage()]);
            return redirect()->route('users.index')->with('error', 'Ups, algo falló');
        }
    }

    public function edit(User $user): View
    {
        $roles = Role::where('name', '!=', 'administrador')->get();
        return view('user.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->safe()->except(['password', 'role']);

            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            $user->syncRoles([$request->role]);

            DB::commit();
            ActivityLogService::log('Edición de usuario', 'Usuarios', $request->validated());

            return redirect()->route('users.index')->with('success', 'Usuario editado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al editar el usuario', ['error' => $e->getMessage()]);
            return redirect()->route('users.index')->with('error', 'Ups, algo falló');
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $user       = User::findOrFail($id);
            $nuevoEstado = $user->estado == 1 ? 0 : 1;
            $user->update(['estado' => $nuevoEstado]);

            $message = $nuevoEstado == 1 ? 'Usuario activado' : 'Usuario desactivado';
            ActivityLogService::log($message, 'Usuario', ['user_id' => $id, 'estado' => $nuevoEstado]);

            return redirect()->route('users.index')->with('success', $message);
        } catch (Throwable $e) {
            Log::error('Error al cambiar estado del usuario', ['error' => $e->getMessage()]);
            return redirect()->route('users.index')->with('error', 'Ups, algo falló');
        }
    }
}
