<?php

namespace Flute\Modules\API\Providers;

use Flute\Core\Support\ModuleServiceProvider;

class APIProvider extends ModuleServiceProvider
{
    public array $extensions = [];

    public function boot(\DI\Container $container): void
    {
        $this->loadRouterAttributes();
    }

    public function register(\DI\Container $container): void
    {
    }
}
