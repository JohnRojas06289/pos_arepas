<?php
use Illuminate\Support\Facades\Route;

Route::get('/debug-php', function () {
    return phpinfo();
});
