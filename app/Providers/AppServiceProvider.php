<?php

namespace App\Providers;

use App\Models\Caja;
use App\Models\Empresa;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(['layouts.include.navigation-header', 'layouts.include.navigation-menu'], function ($view) {
            $companyName = 'Arepas Boyacenses';
            $cajaStatus = 'unknown';

            try {
                $companyName = Empresa::query()->value('nombre') ?: $companyName;
            } catch (\Throwable $e) {
                // Mantener un fallback silencioso para no romper el layout.
            }

            if (auth()->check()) {
                try {
                    $cajaStatus = Caja::query()
                        ->where('user_id', auth()->id())
                        ->where('estado', true)
                        ->exists() ? 'open' : 'closed';
                } catch (\Throwable $e) {
                    $cajaStatus = 'unknown';
                }
            }

            $view->with([
                'layoutCompanyName' => $companyName,
                'layoutCajaStatus' => $cajaStatus,
            ]);
        });

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
