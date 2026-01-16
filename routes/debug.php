<?php
use Illuminate\Support\Facades\Route;

Route::get('/debug-php', function () {
    $debug = [
        'filesystem_default' => config('filesystems.default'),
        'disk_config' => config('filesystems.disks.cloudinary'),
        'env_url' => env('CLOUDINARY_URL'),
        'test_url' => \Illuminate\Support\Facades\Storage::url('test.jpg'),
        'php_upload_max' => ini_get('upload_max_filesize'),
        'php_post_max' => ini_get('post_max_size'),
    ];
    return response()->json($debug);
});
