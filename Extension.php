<?php namespace SamPoyigi\Cart;

//use Cart;
use Igniter\Flame\Cart\Cart;
use Igniter\Flame\Cart\CartServiceProvider;
use Illuminate\Foundation\AliasLoader;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Cart', 'Igniter\Flame\Cart\Facades\Cart');

        $config = __DIR__.'/config/cart.php';
        $this->mergeConfigFrom($config, 'cart');

        $this->app->singleton('cart', function ($app) {
            return new Cart($app['session.store'], $app['events']);
        });
    }

    public function registerComponents()
    {
        return [
            'SamPoyigi\Cart\components\CartBox'  => [
                'code'        => 'cartBox',
                'name'        => 'lang:cart::default.text_component_title',
                'description' => 'lang:cart::default.text_component_desc',
            ],
            'SamPoyigi\Cart\components\Checkout' => [
                'code'        => 'checkout',
                'name'        => 'lang:cart::default.text_checkout_component_title',
                'description' => 'lang:cart::default.text_checkout_component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.CartModule' => [
                'description' => 'Ability to manage cart module',
                'action'      => ['manage'],
            ],
        ];
    }
}