<?php
use Illuminate\Support\Facades\Route;

    try {
        $write = \Illuminate\Support\Facades\Storage::disk('cloudinary')->put('debug_test.txt', 'Hello World');
        $url = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url('debug_test.txt');
        $exists = \Illuminate\Support\Facades\Storage::disk('cloudinary')->exists('debug_test.txt');
    } catch (\Exception $e) {
        $write = $e->getMessage();
        $url = 'error';
        $exists = false;
    }

    $debug = [
        'filesystem_default' => config('filesystems.default'),
        'disk_config' => config('filesystems.disks.cloudinary'),
        'write_status' => $write,
        'generated_url' => $url,
        'file_exists' => $exists,
        'adapter_class' => get_class(\Illuminate\Support\Facades\Storage::disk('cloudinary')->getAdapter()),
    ];
    return response()->json($debug);
});
