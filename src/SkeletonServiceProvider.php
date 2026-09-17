<?php

declare(strict_types=1);

namespace VendorName\Skeleton;

use Illuminate\Support\ServiceProvider;
use VendorName\Skeleton\Console\Commands\SkeletonCommand;

class SkeletonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        /* @chisel-config */
        $this->mergeConfigFrom(__DIR__.'/../config/skeleton.php', 'skeleton');
        /* @end-chisel-config */

        $this->app->singleton(Skeleton::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /* @chisel-routes */
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        /* @end-chisel-routes */

        /* @chisel-views */
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');
        /* @end-chisel-views */

        /* @chisel-translations */
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'skeleton');
        /* @end-chisel-translations */

        /* @chisel-any-features */
        if (! $this->app->runningInConsole()) {
            return;
        }

        /* @end-chisel-any-features */

        /* @chisel-vue */
        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/skeleton'),
        ], ['skeleton', 'skeleton-assets']);
        /* @end-chisel-vue */

        /* @chisel-config */
        $this->publishes([
            __DIR__.'/../config/skeleton.php' => config_path('skeleton.php'),
        ], ['skeleton', 'skeleton-config']);
        /* @end-chisel-config */

        /* @chisel-views */
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/skeleton'),
        ], ['skeleton', 'skeleton-views']);
        /* @end-chisel-views */

        /* @chisel-translations */
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/skeleton'),
        ], ['skeleton', 'skeleton-lang']);
        /* @end-chisel-translations */

        /* @chisel-assets */
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/skeleton'),
        ], ['skeleton', 'skeleton-assets']);
        /* @end-chisel-assets */

        /* @chisel-migrations */
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['skeleton', 'skeleton-migrations']);
        /* @end-chisel-migrations */

        /* @chisel-commands */
        $this->commands([
            SkeletonCommand::class,
        ]);
        /* @end-chisel-commands */
    }
}
