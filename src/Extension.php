<?php

namespace Igniter\Cart;

use Igniter\Admin\Models\Order;
use Igniter\Flame\Igniter;
use Igniter\Local\Facades\Location;
use Igniter\Main\Facades\Auth;
use Igniter\System\Classes\BaseExtension;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class Extension extends BaseExtension
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'cart');

        $this->app->singleton(Classes\CartConditionManager::class);
        $this->app->singleton(Classes\CartManager::class);
        $this->app->singleton(Classes\OrderManager::class);

        $this->registerCart();

        AliasLoader::getInstance()->alias('Cart', Facades\Cart::class);
    }

    public function boot()
    {
        if (!Igniter::runningInAdmin()) {
            $this->app['router']->pushMiddlewareToGroup('web', Http\Middleware\CartMiddleware::class);
        }

        $this->bindCartEvents();
        $this->bindCheckoutEvents();
        $this->bindOrderStatusEvent();

        Order::extend(function ($model) {
            $model->implement[] = \Igniter\Cart\Actions\OrderAction::class;
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
            'igniter.cart::mail.order' => 'Order confirmation email to customer',
            'igniter.cart::mail.order_alert' => 'New order alert email to admin',
        ];
    }

    protected function bindCartEvents()
    {
        Event::listen('cart.beforeRegister', function () {
            Config::set('cart.model', Models\Cart::class);
            Config::set('cart.abandonedCart', Models\CartSettings::get('abandoned_cart'));
        });

        Event::listen('cart.afterRegister', function ($cart, $instance) {
            if (Location::current()) {
                $cart->instance('location-'.Location::getId());
            }
        });

        Event::listen('igniter.user.login', function () {
            if (Models\CartSettings::get('abandoned_cart')
                && Facades\Cart::content()->isEmpty()
            ) {
                Facades\Cart::restore(Auth::getId());
            }
        });

        Event::listen('igniter.user.logout', function () {
            if (Models\CartSettings::get('destroy_on_logout')) {
                Facades\Cart::destroy();
            }
        });
    }

    protected function bindCheckoutEvents()
    {
        Event::listen('admin.order.paymentProcessed', function (Order $model) {
            Notifications\OrderCreatedNotification::make()->subject($model)->broadcast();

            $model->mailSend('igniter.cart::mail.order', 'customer');
            $model->mailSend('igniter.cart::mail.order_alert', 'location');
            $model->mailSend('igniter.cart::mail.order_alert', 'admin');
        });

        Event::listen('admin.order.beforePaymentProcessed', function (Order $model) {
            $model->subtractStock();
        });
    }

    protected function bindOrderStatusEvent()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.beforeAddOrderStatus', [$model, $object, $statusId, $previousStatus], true);
        });

        Event::listen('admin.statusHistory.added', function ($model, $statusHistory) {
            if (!$model instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.orderStatusAdded', [$model, $statusHistory], true);
        });

        Event::listen('admin.assignable.assigned', function ($model) {
            if (!$model instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.orderAssigned', [$model], true);
        });
    }

    protected function registerCart(): void
    {
        $this->app->singleton('cart', function ($app) {
            $this->app['events']->fire('cart.beforeRegister', [$this]);

            $instance = new Cart($app['session'], $app['events']);

            $this->app['events']->fire('cart.afterRegister', [$instance, $this]);

            return $instance;
        });
    }
}
