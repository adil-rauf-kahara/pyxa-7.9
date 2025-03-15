<?php

declare(strict_types=1);

namespace App\Extensions\Cloudflare\System;

use App\Extensions\Cloudflare\System\Http\Controllers\CloudflareR2SettingController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CloudflareServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(Kernel $kernel): void
    {
        $this
            ->publishAssets()
            ->registerViews()
            ->registerRoutes();
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets/images' => public_path('images/integrations'),
        ], 'extension');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'cloudflare');

        return $this;
    }

    private function registerRoutes(): void
    {
        $this->router()
            ->group([
                'prefix'     => LaravelLocalization::setLocale(),
                'middleware' => ['web', 'auth', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
            ], function (Router $router) {
                Route::controller(CloudflareR2SettingController::class)
                    ->prefix('dashboard/admin/settings')
                    ->name('dashboard.admin.settings.')
                    ->middleware('admin')
                    ->group(function (Router $router) {
                        $router->get('cloudflare-r2', 'index')->name('cloudflare-r2');
                        $router->post('cloudflare-r2', 'update');
                    });
            });
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }
}
