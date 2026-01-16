<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

Route::get('/debug-php', function () {
    $results = [];

    // Test URL generation for IMAGE (what we care about)
    try {
        $results['jpg_url'] = Storage::disk('cloudinary')->url('test_product.jpg');
        $results['png_url'] = Storage::disk('cloudinary')->url('folder/image.png');
    } catch (\Throwable $e) {
        $results['url_error'] = $e->getMessage();
    }

    // Helper test
    try {
        $results['helper_jpg'] = cloudinary()->getUrl('test_helper.jpg');
    } catch (\Throwable $e) {
        $results['helper_error'] = $e->getMessage();
    }

    return response()->json($results);
});
