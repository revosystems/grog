<?php namespace BadChoice\Grog;

use Collective\Html\HtmlServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class GrogServiceProvider extends ServiceProvider
{

    public function boot(){
        $this->publishes([
            __DIR__.'/../resources/currencies/'     => resource_path('currencies'),
        ], 'resources');

        $this->publishes([
            __DIR__.'/../resources/assets/js/'      => resource_path('assets/js'),
        ], 'scripts');

        $this->publishes([
            __DIR__.'/../config/tenants.php'        => config_path('tenants.php'),
            __DIR__.'/../config/resourceRoute.php'  => config_path('resourceRoute.php'),
        ], 'config');

        AliasLoader::getInstance()->alias('Form', 'Collective\Html\FormFacade');
        AliasLoader::getInstance()->alias('Html', 'Collective\Html\HtmlFacade');

        require_once __DIR__ . '/Helpers/Macros.php';
        require_once __DIR__ . '/Helpers/Helpers.php';
        require_once __DIR__ . '/Helpers/RouteHelpers.php';
    }

    public function register(){
        $this->app->register(HtmlServiceProvider::class);
        $this->mergeConfigFrom(__DIR__ . '/../config/tenants.php', 'tenants');
    }
}
