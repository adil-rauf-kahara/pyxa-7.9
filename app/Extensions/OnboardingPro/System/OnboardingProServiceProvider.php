<?php

declare(strict_types=1);

namespace App\Extensions\OnboardingPro\System;

use App\Extensions\OnboardingPro\System\Http\Controllers\OnboardingProController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Author: Rauf Abbas <info@liquid-themes.com>
 *
 * @note When you create a new service provider, make sure to add it to the "MarketplaceServiceProvider". Otherwise, your Laravel application won’t recognize this provider, and the related functions won’t work properly.
 */
class OnboardingProServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerRoutes()
            ->registerMigrations()
            ->publishAssets();
    }

    public function publishAssets(): static
    {
        $this->publishes([
            //            __DIR__ . '/../resources/assets/js' => public_path('vendor/onboarding-pro/js'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/onboarding-pro.php', 'onboarding-pro');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'onboarding-pro');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'onboarding-pro');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    private function registerRoutes(): static
    {
        $this->router()
            ->group([
                'prefix'     => LaravelLocalization::setLocale(),
                'middleware' => ['web', 'auth', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
            ], function (Router $route) {

                $route->group([
                    'prefix' => 'dashboard/admin/',
                    'as'     => 'dashboard.admin.',
                ], function (Router $router) {
                    $router->get('onboarding-pro', [OnboardingProController::class, 'index'])->name('onboarding-pro.index');
                    $router->get('onboarding-pro/banners', [OnboardingProController::class, 'banners'])->name('onboarding-pro.banners');
                    $router->get('onboarding-pro/banners/create', [OnboardingProController::class, 'bannerCreate'])->name('onboarding-pro.banner.create');
                    $router->post('onboarding-pro/banners/post', [OnboardingProController::class, 'bannerPost'])->name('onboarding-pro.banner.post');
                    $router->put('onboarding-pro/banners/update/{id}', [OnboardingProController::class, 'bannerUpdate'])->name('onboarding-pro.banner.update');
                    $router->get('onboarding-pro/banners/{id}/edit', [OnboardingProController::class, 'bannerEdit'])->name('onboarding-pro.banner.edit');
                    $router->get('onboarding-pro/banners/{id}/delete', [OnboardingProController::class, 'bannerDelete'])->name('onboarding-pro.banner.delete');
                    $router->get('onboarding-pro/banners/display/{id}', [OnboardingProController::class, 'bannerDisplay'])->name('onboarding-pro.banner.display');

                    $router->get('onboarding-pro/surveys', [OnboardingProController::class, 'surveys'])->name('onboarding-pro.surveys');
                    $router->get('onboarding-pro/surveys/create', [OnboardingProController::class, 'surveyCreate'])->name('onboarding-pro.survey.create');
                    $router->get('onboarding-pro/surveys/{id}/edit', [OnboardingProController::class, 'surveyEdit'])->name('onboarding-pro.survey.edit');
                    $router->get('onboarding-pro/surveys/{id}/delete', [OnboardingProController::class, 'surveyDelete'])->name('onboarding-pro.survey.delete');
                    $router->get('onboarding-pro/surveys/{id}/result', [OnboardingProController::class, 'surveyResult'])->name('onboarding-pro.survey.result');
                    $router->get('onboarding-pro/surveys/{id}/result/{point}', [OnboardingProController::class, 'surveyResultPoint'])->name('onboarding-pro.survey.result.point');
                    $router->post('onboarding-pro/surveys/post', [OnboardingProController::class, 'surveyPost'])->name('onboarding-pro.survey.post');
                    $router->put('onboarding-pro/surveys/update/{id}', [OnboardingProController::class, 'surveyUpdate'])->name('onboarding-pro.survey.update');
                    $router->get('onboarding-pro/surveys/display/{point}/{id}', [OnboardingProController::class, 'surveyDisplay'])->name('onboarding-pro.survey.display');

                    $router->get('onboarding-pro/introduction', [OnboardingProController::class, 'introduction'])->name('onboarding-pro.introduction');
                    $router->post('onboarding-pro/introduction/save', [OnboardingProController::class, 'introductionSave'])->name('onboarding-pro.introduction.save');
                    $router->get('onboarding-pro/introduction/customization', [OnboardingProController::class, 'customization'])->name('onboarding-pro.introduction.customization');
                    $router->delete('onboarding-pro/introduction/delete-image/{key}', [OnboardingProController::class, 'ImageDelete'])->name('onboarding-pro.introduction.delete-image');
                    $router->post('onboarding-pro/introduction/customization/save', [OnboardingProController::class, 'customizationSave'])->name('onboarding-pro.introduction.customization.save');
                });
            });

        return $this;
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }
}
