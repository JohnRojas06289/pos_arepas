<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

Route::get('/debug-gate', function () {
    $user = auth()->user();
    if (!$user) return 'Not logged in';
    
    echo "User: " . $user->name . "<br>";
    echo "Has Role 'administrador': " . ($user->hasRole('administrador') ? 'YES' : 'NO') . "<br>";
    echo "Has Permission 'ver-categoria': " . ($user->hasPermissionTo('ver-categoria') ? 'YES' : 'NO') . "<br>";
    echo "Gate::allows('ver-categoria'): " . (Gate::allows('ver-categoria') ? 'YES' : 'NO') . "<br>";
    echo "User->can('ver-categoria'): " . ($user->can('ver-categoria') ? 'YES' : 'NO') . "<br>";
    
    echo "Permissions count: " . $user->getAllPermissions()->count() . "<br>";
});
