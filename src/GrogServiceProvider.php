<?php namespace BadChoice\Grog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class GrogServiceProvider extends ServiceProvider
{
    //protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/currencies/' => resource_path('currencies'),
        ], 'resources');

        $this->publishes([
            __DIR__.'/../resources/assets/js/' => resource_path('assets/js'),
        ], 'scripts');

        $this->publishes([
            __DIR__.'/../config/tenants.php' => config_path('tenants.php'),
        ], 'config');

        AliasLoader::getInstance()->alias('Form', 'Collective\Html\FormFacade');
        AliasLoader::getInstance()->alias('Html', 'Collective\Html\HtmlFacade');

        require_once __DIR__ . '/Helpers/Macros.php';
        require_once __DIR__ . '/Helpers/Helpers.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Collective\Html\HtmlServiceProvider');
        $this->app->register('Khill\Fontawesome\FontAwesomeServiceProvider');

        /*$this->app->singleton('cart', function ($app) {
            return new Cart\Cart();
        });*/
    }

    public function provides()
    {
        //return ['cart'];
    }
}
