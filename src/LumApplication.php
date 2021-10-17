<?php
namespace Lum;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Laravel\Lumen\Application as LumenApplication;
use Throwable;

/**
 * Class LumApplication
 *
 * @package App\Common
 */
class LumApplication extends LumenApplication {
   protected static $reservedMemory;
    /**
     * Normalize a relative or absolute path to a cache file.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    protected function normalizeCachePath($key, $default) {
        if (is_null($env=Env::get($key))) {
            return $this->bootstrapPath($default);
        }
        return Str::startsWith($env, '/') ? $env : $this->basePath($env);
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     *
     * @return string
     */
    public function bootstrapPath($path='') {
        return $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap' .
            ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath() {
        return $this->normalizeCachePath('APP_PACKAGES_CACHE', 'cache/packages.php');
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->normalizeCachePath('APP_SERVICES_CACHE', 'cache/services.php');
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function handleException(Throwable $e)
    {
        try {
            self::$reservedMemory = null;

            $this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        //if ($this->runningInConsole()) {
        //    $this->renderForConsole($e);
        //} else {
        //    $this->renderHttpResponse($e);
        //}
    }
    /**
     * Get an instance of the exception handler.
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        return $this->make(ExceptionHandler::class);
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->config['app.providers'])
            ->partition(function ($provider) {
                return strpos($provider, 'Illuminate\\') === 0;
            });

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
    }
}
