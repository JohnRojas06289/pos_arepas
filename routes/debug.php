<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/debug-php', function () {
    $results = [];
    
    try {
        $timestamp = time();
        $filename = "debug_{$timestamp}.txt";
        
        $write = Storage::disk('cloudinary')->put($filename, 'Hello World ' . $timestamp);
        $results['write'] = $write;
        
        $url = Storage::disk('cloudinary')->url($filename);
        $results['url'] = $url;
        
        $exists = Storage::disk('cloudinary')->exists($filename);
        $results['exists'] = $exists;
        
    } catch (\Throwable $e) {
        $results['error'] = $e->getMessage();
        $results['file'] = $e->getFile();
        $results['line'] = $e->getLine();
    }

    $debug = [
        'filesystem_default' => config('filesystems.default'),
        'disk_config' => config('filesystems.disks.cloudinary'),
        'write_test' => $results,
        'php_upload_max' => ini_get('upload_max_filesize'),
    ];
    
    // Add adapter info safe check
    try {
        $adapter = Storage::disk('cloudinary')->getAdapter();
        $debug['adapter_class'] = get_class($adapter);
    } catch (\Throwable $e) {
        $debug['adapter_error'] = $e->getMessage();
    }

    return response()->json($debug);
});
