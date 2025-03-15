<?php

declare(strict_types=1);

namespace App\Extensions\Menu\System;

use App\Extensions\Menu\System\Http\Controllers\MenuController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(Kernel $kernel): void
    {
        $this->registerViews()
            ->registerRoutes();

    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'menu');

        return $this;
    }

    private function registerRoutes(): void
    {
        $this->router()
            ->group([
                'prefix'     => LaravelLocalization::setLocale(),
                'middleware' => ['web', 'auth', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
            ], function (Router $router) {
                Route::prefix('dashboard/admin')
                    ->middleware('admin')
                    ->name('dashboard.admin.menu.')
                    ->controller(MenuController::class)
                    ->group(function () {
                        Route::get('menu', 'index')->name('index');
                        Route::get('menu/{menu}/delete', 'delete')->name('delete');
                        Route::any('menu/{menu}/status', 'status')->name('status');
                        Route::any('menu/{menu}/bolt-menu', 'boltMenu')->name('bolt-menu');
                        Route::post('menu/{menu}/{type}', 'update')->name('update');
                        Route::any('menu/order', 'order')->name('order');
                        Route::post('menu', 'store')->name('store');
                    });
            });
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }
}
