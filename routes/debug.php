<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

Route::get('/debug-php', function () {
    $results = [];
    $tmpFile = '/tmp/debug_direct.txt';
    file_put_contents($tmpFile, 'Direct Upload Test ' . time());

    // Test 1: Storage Disk
    try {
        $write = Storage::disk('cloudinary')->put('storage_test.txt', 'Storage Test ' . time());
        $results['storage_write'] = $write;
        $results['storage_url'] = Storage::disk('cloudinary')->url('storage_test.txt');
    } catch (\Throwable $e) {
        $results['storage_error'] = $e->getMessage();
    }

    // Test 2: Direct Facade
    try {
        // UploadFile matches the signature better or simple upload
        $upload = Cloudinary::upload($tmpFile, [
            'folder' => 'debug', 
            'public_id' => 'direct_test_' . time(),
            'resource_type' => 'auto'
        ]);
        $results['direct_url'] = $upload->getSecurePath();
        $results['direct_result'] = $upload->getArrayCopy();
    } catch (\Throwable $e) {
        $results['direct_error'] = $e->getMessage();
        // $results['direct_trace'] = $e->getTraceAsString();
    }

    $results['env_check'] = [
        'has_url' => !empty(env('CLOUDINARY_URL')),
        'url_len' => strlen(env('CLOUDINARY_URL')),
    ];

    return response()->json($results);
});
