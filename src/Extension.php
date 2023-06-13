<?php

namespace Igniter\Cart;

use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\Observers\MenuItemOptionObserver;
use Igniter\Cart\Models\Observers\MenuObserver;
use Igniter\Cart\Models\Observers\OrderObserver;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\Scopes\CategoryScope;
use Igniter\Cart\Models\Scopes\MenuScope;
use Igniter\Cart\Models\Scopes\OrderScope;
use Igniter\Cart\Requests\OrderSettingsRequest;
use Igniter\Flame\Igniter;
use Igniter\Local\Facades\Location;
use Igniter\System\Classes\BaseExtension;
use Igniter\System\Models\Settings;
use Igniter\User\Facades\Auth;
use Igniter\User\Http\Controllers\Customers;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class Extension extends BaseExtension
{
    protected $observers = [
        MenuItemOption::class => MenuItemOptionObserver::class,
        Menu::class => MenuObserver::class,
        Order::class => OrderObserver::class,
    ];

    protected array $scopes = [
        Category::class => CategoryScope::class,
        Menu::class => MenuScope::class,
        Order::class => OrderScope::class,
    ];

    protected array $morphMap = [
        'categories' => \Igniter\Cart\Models\Category::class,
        'ingredients' => \Igniter\Cart\Models\Ingredient::class,
        'mealtimes' => \Igniter\Cart\Models\Mealtime::class,
        'menu_categories' => \Igniter\Cart\Models\MenuCategory::class,
        'menu_item_option_values' => \Igniter\Cart\Models\MenuItemOptionValue::class,
        'menu_option_values' => \Igniter\Cart\Models\MenuOptionValue::class,
        'menu_options' => \Igniter\Cart\Models\MenuOption::class,
        'menus' => \Igniter\Cart\Models\Menu::class,
        'menus_specials' => \Igniter\Cart\Models\MenuSpecial::class,
        'orders' => \Igniter\Cart\Models\Order::class,
        'stocks' => \Igniter\Cart\Models\Stock::class,
        'stock_history' => \Igniter\Cart\Models\StockHistory::class,
    ];

    public array $singletons = [
        Classes\CartConditionManager::class,
        Classes\CartManager::class,
        Classes\OrderManager::class,
    ];

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'cart');

        $this->registerCart();
        $this->registerSystemSettings();

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

        Customers::extendFormFields(function (Form $form) {
            if (!$form->model instanceof Customer) {
                return;
            }

            $form->addTabFields([
                'orders' => [
                    'tab' => 'lang:igniter.cart::default.text_tab_orders',
                    'type' => 'datatable',
                    'context' => ['edit', 'preview'],
                    'useAjax' => true,
                    'defaultSort' => ['order_id', 'desc'],
                    'columns' => [
                        'order_id' => [
                            'title' => 'lang:igniter::admin.column_id',
                        ],
                        'customer_name' => [
                            'title' => 'lang:igniter.cart::default.orders.column_customer_name',
                        ],
                        'status_name' => [
                            'title' => 'lang:igniter::admin.label_status',
                        ],
                        'order_type_name' => [
                            'title' => 'lang:igniter::admin.label_type',
                        ],
                        'order_total' => [
                            'title' => 'lang:igniter.cart::default.orders.column_total',
                        ],
                        'order_time' => [
                            'title' => 'lang:igniter.cart::default.orders.column_time',
                        ],
                        'order_date' => [
                            'title' => 'lang:igniter.cart::default.orders.column_date',
                        ],
                    ],
                ],
            ], 'primary');
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
            \Igniter\Cart\Components\Menu::class => [
                'code' => 'localMenu',
                'name' => 'lang:igniter.cart::default.menu_component_title',
                'description' => 'lang:igniter.cart::default.menu_component_desc',
            ],
            \Igniter\Cart\Components\Categories::class => [
                'code' => 'categories',
                'name' => 'lang:igniter.cart::default.categories_component_title',
                'description' => 'lang:igniter.cart::default.categories_component_desc',
            ],
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
            'Admin.Allergens' => [
                'label' => 'igniter.cart::default.text_permission_ingredients',
                'group' => 'menu',
            ],
            'Admin.Categories' => [
                'label' => 'igniter.cart::default.text_permission_categories',
                'group' => 'menu',
            ],
            'Admin.Menus' => [
                'label' => 'igniter.cart::default.text_permission_menus',
                'group' => 'menu',
            ],
            'Admin.Mealtimes' => [
                'label' => 'igniter.cart::default.text_permission_mealtimes',
                'group' => 'menu',
            ],
            'Admin.Orders' => [
                'label' => 'igniter.cart::default.text_permission_orders',
                'group' => 'order',
            ],
            'Admin.DeleteOrders' => [
                'label' => 'igniter.cart::default.text_permission_delete_orders',
                'group' => 'order',
            ],
            'Admin.AssignOrders' => [
                'label' => 'igniter.cart::default.text_permission_assign_orders',
                'group' => 'order',
            ],
            'Module.CartModule' => [
                'description' => 'Manage cart extension settings',
                'group' => 'order',
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
            'igniter.cart::mail.order_update' => 'lang:igniter.cart::default.text_mail_order_update',
            'igniter.cart::mail.low_stock_alert' => 'lang:igniter.cart::default.text_mail_low_stock_alert',
        ];
    }

    public function registerImportExport()
    {
        return [
            'import' => [
                'menus' => [
                    'label' => 'Import Menu Items',
                    'model' => \Igniter\Cart\Models\MenuImport::class,
                    'configFile' => 'igniter.cart::/models/menuimport',
                ],
            ],
            'export' => [
                'menus' => [
                    'label' => 'Export Menu Items',
                    'model' => \Igniter\Cart\Models\MenuExport::class,
                    'configFile' => 'igniter.cart::/models/menuexport',
                ],
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'restaurant' => [
                'child' => [
                    'menus' => [
                        'priority' => 20,
                        'class' => 'menus',
                        'href' => admin_url('menus'),
                        'title' => lang('igniter.cart::default.text_side_menu_menu'),
                        'permission' => 'Admin.Menus',
                    ],
                    'mealtimes' => [
                        'priority' => 40,
                        'class' => 'mealtimes',
                        'href' => admin_url('mealtimes'),
                        'title' => lang('igniter.cart::default.text_side_menu_mealtimes'),
                        'permission' => 'Admin.Mealtimes',
                    ],
                ],
            ],
            'sales' => [
                'child' => [
                    'orders' => [
                        'priority' => 10,
                        'class' => 'orders',
                        'href' => admin_url('orders'),
                        'title' => lang('igniter.cart::default.text_side_menu_order'),
                        'permission' => 'Admin.Orders',
                    ],
                ],
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Igniter\Cart\FormWidgets\StockEditor::class => [
                'label' => 'Stock Editor',
                'code' => 'stockeditor',
            ],
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

    protected function registerSystemSettings()
    {
        Settings::registerCallback(function (Settings $manager) {
            $manager->registerSettingItems('core', [
                'order' => [
                    'label' => 'lang:igniter.cart::default.text_tab_order',
                    'description' => 'lang:igniter.cart::default.text_tab_desc_order',
                    'icon' => 'fa fa-file-invoice',
                    'priority' => 1,
                    'permission' => ['Site.Settings'],
                    'url' => admin_url('settings/edit/order'),
                    'form' => 'igniter.cart::/models/ordersettings',
                    'request' => OrderSettingsRequest::class,
                ],
            ]);
        });
    }
}
