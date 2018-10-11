<?php namespace Igniter\Cart;

use Auth;
use Cart;
use Config;
use Event;
use Igniter\Cart\Models\Cart as CartStore;
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
            Config::set('cart.model', CartStore::class);
            Config::set('cart.conditions', CartSettings::get('conditions'));
        });
    }

    public function boot()
    {
        $this->registerCartEvents();
        $this->registerCheckoutEvents();
    }

    public function registerCartConditions()
    {
        return [
            \Igniter\Cart\Conditions\Coupon::class => [
                'name' => 'coupon',
                'label' => 'lang:igniter.cart::default.text_coupon',
                'description' => 'lang:igniter.cart::default.help_coupon_condition',
            ],
            \Igniter\Cart\Conditions\Tax::class => [
                'name' => 'tax',
                'label' => 'lang:igniter.cart::default.text_vat',
                'description' => 'lang:igniter.cart::default.help_tax_condition',
            ]
        ];
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

    protected function registerCartEvents()
    {
        Event::listen('igniter.user.login', function () {
            if (CartSettings::get('abandoned_cart')
                AND Cart::content()->isEmpty()
            ) {
                Cart::restore(Auth::getId());
            }
        });

        Event::listen('igniter.user.logout', function () {
            if (CartSettings::get('destroy_on_logout'))
                Cart::destroy();
        });
    }

    protected function registerCheckoutEvents()
    {
        Event::listen('igniter.checkout.beforePayment', function ($orderModel) {
            $couponCondition = Cart::getCondition('coupon');

            if ($couponCondition AND $couponModel = $couponCondition->getModel())
                $orderModel->logCouponHistory($couponModel, Auth::customer());
        });

        Event::listen('admin.order.paymentProcessed', function ($model) {
            if ($user = Auth::user()) {
                activity()->causedBy($user)->log(
                    lang('system::lang.activities.activity_logged_in')
                );
            }

            $model->mailSend('igniter.cart::mail.order', 'customer');
            $model->mailSend('igniter.cart::mail.order_alert', 'location');
            $model->mailSend('igniter.cart::mail.order_alert', 'admin');
        });
    }
}