<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // VERCEL FIX: Force Runtime Cloudinary Config if Env Var exists (Check all sources)
        $cloudinaryUrl = $_ENV['CLOUDINARY_URL'] ?? $_SERVER['CLOUDINARY_URL'] ?? getenv('CLOUDINARY_URL');

        if ($cloudinaryUrl) {
            $components = parse_url($cloudinaryUrl);

            if ($components) {
                config([
                    'filesystems.disks.cloudinary' => [
                        'driver' => 'cloudinary',
                        'cloud_name' => $components['host'],
                        'api_key' => $components['user'],
                        'api_secret' => $components['pass'],
                        'secure' => true,
                        'url' => 'https://res.cloudinary.com/' . $components['host'], 
                    ],
                    'filesystems.default' => 'cloudinary', 
                ]);
            }
        }
    }
}
