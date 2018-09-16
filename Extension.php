<?php namespace Igniter\Cart;

use Event;
use Igniter\Cart\Models\CartSettings;
use Illuminate\Foundation\AliasLoader;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function register()
    {
        $this->app->register(\Igniter\Flame\Cart\CartServiceProvider::class);

        AliasLoader::getInstance()->alias('Cart', \Igniter\Flame\Cart\Facades\Cart::class);

        $this->app->resolving(\Igniter\Flame\Cart\Cart::class, function ($cart, $container) {
            $cart->setDestroyOnLogout(CartSettings::get('destroy_on_logout'));
        });

        $this->registerCartConditions();
    }

    public function boot()
    {
        Event::listen('admin.order.paymentProcessed', function ($model) {
            $model->mailSend('igniter.cart::mail.order', 'customer');
            $model->mailSend('igniter.cart::mail.order_alert', 'location');
            $model->mailSend('igniter.cart::mail.order_alert', 'admin');
        });
    }

    public function registerCartConditions()
    {
        CartSettings::registerConditions(function (CartSettings $settingsModel) {
            $settingsModel->registerCondition('Igniter\Cart\Conditions\Coupon', [
                'name' => 'coupon',
                'label' => 'lang:igniter.cart::default.text_coupon',
                'description' => 'lang:igniter.cart::default.help_coupon_condition',
            ]);

            $settingsModel->registerCondition('Igniter\Cart\Conditions\Tax', [
                'name' => 'tax',
                'label' => 'lang:igniter.cart::default.text_vat',
                'description' => 'lang:igniter.cart::default.help_tax_condition',
            ]);
        });
    }

    public function registerComponents()
    {
        return [
            'Igniter\Cart\Components\CartBox' => [
                'code' => 'cartBox',
                'name' => 'lang:igniter.cart::default.text_component_title',
                'description' => 'lang:igniter.cart::default.text_component_desc',
            ],
            'Igniter\Cart\Components\Checkout' => [
                'code' => 'checkout',
                'name' => 'lang:igniter.cart::default.text_checkout_component_title',
                'description' => 'lang:igniter.cart::default.text_checkout_component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.CartModule' => [
                'description' => 'Ability to manage cart module',
                'action' => ['manage'],
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'cartsettings' => [
                'label' => 'Cart Settings',
                'description' => 'Manage cart settings.',
                'icon' => '',
                'model' => 'Igniter\Cart\Models\CartSettings',
                'permissions' => ['Module.Cart'],
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.cart::mail.order' => 'Order confirmation email to customer',
            'igniter.cart::mail.order_alert' => 'New order alert email to admin',
        ];
    }
}