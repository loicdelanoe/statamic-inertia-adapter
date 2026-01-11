<?php

namespace StatamicInertiaAdapter\StatamicInertiaAdapter;

use Illuminate\Contracts\Http\Kernel;
use Inertia\Inertia;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use StatamicInertiaAdapter\StatamicInertiaAdapter\Http\Middleware\StatamicInertiaAdapter;
use StatamicInertiaAdapter\StatamicInertiaAdapter\Support\SharedData;

class StatamicInertiaAdapterServiceProvider extends PackageServiceProvider
{
    /**
     * Perform any actions required during package boot.
     */
    public function bootingPackage(): void
    {
        $this->registerMiddleware();
        $this->shareInertiaData();
    }

    /**
     * Register the Statamic Inertia middleware on the 'web' group.
     *
     * This middleware resolves the current Statamic page and attaches it
     * to the request, making it available to shared data.
     */
    protected function registerMiddleware(): void
    {
        $this->app[Kernel::class]->appendMiddlewareToGroup('web', StatamicInertiaAdapter::class);
    }

    /**
     * Share the Statamic backend data with all Inertia responses.
     *
     * The SharedData class exposes backend data such as navigations, globals,
     * and sites..
     */
    protected function shareInertiaData(): void
    {
        $this->app->booted(function () {
            Inertia::share(SharedData::all());
        });
    }

    /**
     * Configure the package for Spatie Laravel Package Tools.
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('statamic-inertia');
    }
}
