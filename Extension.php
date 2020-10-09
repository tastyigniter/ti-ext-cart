<?php

namespace Igniter\Cart;

use Admin\Models\Orders_model;
use Auth;
use Cart;
use Config;
use Event;
use Igniter\Cart\Models\Cart as CartStore;
use Igniter\Cart\Models\CartSettings;
use Igniter\Local\Facades\Location;
use Illuminate\Foundation\AliasLoader;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function register()
    {
        $this->app->register(\Igniter\Flame\Cart\CartServiceProvider::class);

        AliasLoader::getInstance()->alias('Cart', \Igniter\Flame\Cart\Facades\Cart::class);
    }

    public function boot()
    {
        if (!$this->app->runningInAdmin()) {
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)
                ->appendMiddlewareToGroup('web', \Igniter\Cart\Middleware\CartMiddleware::class);
        }

        $this->bindCartEvents();
        $this->bindCheckoutEvents();
        $this->bindOrderStatusEvent();
    }

    public function registerCartConditions()
    {
        return [
            \Igniter\Cart\CartConditions\Coupon::class => [
                'name' => 'coupon',
                'label' => 'lang:igniter.cart::default.text_coupon',
                'description' => 'lang:igniter.cart::default.help_coupon_condition',
            ],
            \Igniter\Cart\CartConditions\PaymentFee::class => [
                'name' => 'paymentFee',
                'label' => 'lang:igniter.cart::default.text_payment_fee',
                'description' => 'lang:igniter.cart::default.help_payment_fee',
            ],
            \Igniter\Cart\CartConditions\Tax::class => [
                'name' => 'tax',
                'label' => 'lang:igniter.cart::default.text_vat',
                'description' => 'lang:igniter.cart::default.help_tax_condition',
            ],
            \Igniter\Cart\CartConditions\Tip::class => [
                'name' => 'tip',
                'label' => 'lang:igniter.cart::default.text_tip',
                'description' => 'lang:igniter.cart::default.help_tip_condition',
            ],
        ];
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [
                'admin.order.paymentProcessed' => \Igniter\Cart\AutomationRules\Events\OrderPlaced::class,
                'igniter.cart.beforeAddOrderStatus' => \Igniter\Cart\AutomationRules\Events\NewOrderStatus::class,
                'igniter.cart.orderAssigned' => \Igniter\Cart\AutomationRules\Events\OrderAssigned::class,
            ],
            'actions' => [],
            'conditions' => [
                \Igniter\Cart\AutomationRules\Conditions\OrderAttribute::class,
                \Igniter\Cart\AutomationRules\Conditions\OrderStatusAttribute::class,
            ],
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
            'Igniter\Cart\Components\Orders' => [
                'code' => 'accountOrders',
                'name' => 'lang:igniter.cart::default.orders.component_title',
                'description' => 'lang:igniter.cart::default.orders.component_desc',
            ],
            'Igniter\Cart\Components\Order' => [
                'code' => 'orderPage',
                'name' => 'lang:igniter.cart::default.orders.order_component_title',
                'description' => 'lang:igniter.cart::default.orders.order_component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.CartModule' => [
                'description' => 'Manage cart extension settings',
                'group' => 'module',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Cart Settings',
                'description' => 'Manage cart settings.',
                'icon' => 'fa fa-cart-plus',
                'model' => 'Igniter\Cart\Models\CartSettings',
                'permissions' => ['Module.CartModule'],
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

    public function registerPrintTemplates()
    {
        return [
            'igniter.cart::print.order' => 'Default order print template',
        ];
    }

    public function registerActivityTypes()
    {
        return [
            ActivityTypes\OrderCreated::class => 'orderCreated',
        ];
    }

    protected function bindCartEvents()
    {
        Event::listen('cart.beforeRegister', function () {
            Config::set('cart.model', CartStore::class);
            Config::set('cart.abandonedCart', CartSettings::get('abandoned_cart'));
        });

        Event::listen('cart.afterRegister', function ($cart, $instance) {
            if (Location::current())
                $cart->instance('location-'.Location::getId());
        });

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

    protected function bindCheckoutEvents()
    {
        Event::listen('admin.order.paymentProcessed', function (Orders_model $model) {
            ActivityTypes\OrderCreated::log($model);

            $model->mailSend('igniter.cart::mail.order', 'customer');
            $model->mailSend('igniter.cart::mail.order_alert', 'location');
            $model->mailSend('igniter.cart::mail.order_alert', 'admin');
        });
    }

    protected function bindOrderStatusEvent()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Orders_model)
                return;

            Event::fire('igniter.cart.beforeAddOrderStatus', [$model, $object, $statusId, $previousStatus], TRUE);
        });

        Event::listen('admin.assignable.assigned', function ($model) {
            if (!$model instanceof Orders_model)
                return;

            Event::fire('igniter.cart.orderAssigned', [$model], TRUE);
        });
    }
}
