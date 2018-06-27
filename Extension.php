<?php namespace SamPoyigi\Cart;

use Event;
use Igniter\Flame\Cart\Cart;
use Illuminate\Foundation\AliasLoader;
use SamPoyigi\Cart\Models\CartSettings;
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

        $this->registerCartConditions();
    }

    public function initialize()
    {
        Event::listen('admin.order.paymentProcessed', function ($model) {
            $model->mailSend('sampoyigi.cart::mail.order', 'customer');
            $model->mailSend('sampoyigi.cart::mail.order_alert', 'location');
            $model->mailSend('sampoyigi.cart::mail.order_alert', 'admin');
        });
    }

    public function registerCartConditions()
    {
        CartSettings::registerConditions(function (CartSettings $settingsModel) {
            $settingsModel->registerCondition('SamPoyigi\Cart\Conditions\Coupon', [
                'name'        => 'coupon',
                'label'       => 'lang:sampoyigi.cart::default.text_coupon',
                'description' => 'lang:sampoyigi.cart::default.help_coupon_condition',
            ]);

            $settingsModel->registerCondition('SamPoyigi\Cart\Conditions\Tax', [
                'name'        => 'tax',
                'label'       => 'lang:sampoyigi.cart::default.text_vat',
                'description' => 'lang:sampoyigi.cart::default.help_tax_condition',
            ]);
        });
    }

    public function registerComponents()
    {
        return [
            'SamPoyigi\Cart\Components\CartBox'  => [
                'code'        => 'cartBox',
                'name'        => 'lang:sampoyigi.cart::default.text_component_title',
                'description' => 'lang:sampoyigi.cart::default.text_component_desc',
            ],
            'SamPoyigi\Cart\Components\Checkout' => [
                'code'        => 'checkout',
                'name'        => 'lang:sampoyigi.cart::default.text_checkout_component_title',
                'description' => 'lang:sampoyigi.cart::default.text_checkout_component_desc',
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

    public function registerSettings()
    {
        return [
            'cartsettings' => [
                'label'       => 'Cart Settings',
                'description' => 'Manage cart settings.',
                'icon'        => '',
                'model'       => 'SamPoyigi\Cart\Models\CartSettings',
                'permissions' => ['Module.Cart'],
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'sampoyigi.cart::mail.order'       => 'Order confirmation email to customer',
            'sampoyigi.cart::mail.order_alert' => 'New order alert email to admin',
        ];
    }
}