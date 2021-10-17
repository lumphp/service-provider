<?php
namespace Lum;
use Illuminate\Support\ServiceProvider;

/**
 * Class LumServiceProvider
 *
 * @package Dayu\Package
 */
class LumServiceProvider  extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
    }

    /**
     * Register all packages.
     */
    public function register()
    {
        $this->registerProviders();
    }

    /**
     * Register providers.
     */
    protected function registerProviders()
    {
        $this->app->register(ConsoleServiceProvider::class);
    }
}