<?php

namespace Igniter\Cart;

use Admin\Models\Orders_model;
use Igniter\Cart\Middleware\CartMiddleware;
use Igniter\Cart\Models\Cart as CartStore;
use Igniter\Cart\Models\CartSettings;
use Igniter\Flame\Cart\Facades\Cart;
use Igniter\Local\Facades\Location;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Main\Facades\Auth;
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
            $this->app['router']->pushMiddlewareToGroup('web', CartMiddleware::class);
        }

        $this->bindCartEvents();
        $this->bindCheckoutEvents();
        $this->bindOrderStatusEvent();

        Orders_model::extend(function ($model) {
            $model->implement[] = 'Igniter\Cart\Actions\OrderAction';
        });
    }

    public function registerCartConditions()
    {
        return [
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
                'igniter.cart.orderStatusAdded' => \Igniter\Cart\AutomationRules\Events\NewOrderStatus::class,
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
            \Igniter\Cart\Components\CartBox::class => [
                'code' => 'cartBox',
                'name' => 'lang:igniter.cart::default.text_component_title',
                'description' => 'lang:igniter.cart::default.text_component_desc',
            ],
            \Igniter\Cart\Components\Checkout::class => [
                'code' => 'checkout',
                'name' => 'lang:igniter.cart::default.text_checkout_component_title',
                'description' => 'lang:igniter.cart::default.text_checkout_component_desc',
            ],
            \Igniter\Cart\Components\Orders::class => [
                'code' => 'accountOrders',
                'name' => 'lang:igniter.cart::default.orders.component_title',
                'description' => 'lang:igniter.cart::default.orders.component_desc',
            ],
            \Igniter\Cart\Components\Order::class => [
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
                'description' => 'Manage cart conditions and tipping settings.',
                'icon' => 'fa fa-gear',
                'model' => \Igniter\Cart\Models\CartSettings::class,
                'permissions' => ['Module.CartModule'],
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.cart::mail.order' => 'lang:igniter.cart::default.text_mail_order',
            'igniter.cart::mail.order_alert' => 'lang:igniter.cart::default.text_mail_order_alert',
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
                && Cart::content()->isEmpty()
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

        Event::listen('admin.order.beforePaymentProcessed', function (Orders_model $model) {
            $model->subtractStock();
        });
    }

    protected function bindOrderStatusEvent()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Orders_model)
                return;

            Event::fire('igniter.cart.beforeAddOrderStatus', [$model, $object, $statusId, $previousStatus], true);
        });

        Event::listen('admin.statusHistory.added', function ($model, $statusHistory) {
            if (!$model instanceof Orders_model)
                return;

            Event::fire('igniter.cart.orderStatusAdded', [$model, $statusHistory], true);
        });

        Event::listen('admin.assignable.assigned', function ($model) {
            if (!$model instanceof Orders_model)
                return;

            Event::fire('igniter.cart.orderAssigned', [$model], true);
        });
    }
}
