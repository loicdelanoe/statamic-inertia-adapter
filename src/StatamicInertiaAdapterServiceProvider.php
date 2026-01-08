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
    public function bootingPackage()
    {
        $this->app[Kernel::class]->appendMiddlewareToGroup('web', StatamicInertiaAdapter::class);

        $this->app->booted(function () {
            Inertia::share(SharedData::all());
        });
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('statamic-inertia')
            ->hasConfigFile()
            ->hasRoute('web');
    }
}
