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

    // Test 2: Helper
    try {
        $results['helper_url'] = cloudinary()->getUrl('storage_test.txt');
    } catch (\Throwable $e) {
        $results['helper_error'] = $e->getMessage();
    }

    // Test 3: Direct Facade Upload
    try {
        $upload = Cloudinary::upload($tmpFile, [
            'folder' => 'debug', 
            'public_id' => 'direct_test_' . time(),
            'resource_type' => 'auto'
        ]);
        $results['direct_url'] = $upload->getSecurePath();
    } catch (\Throwable $e) {
        $results['direct_error'] = $e->getMessage();
    }

    return response()->json($results);
});
