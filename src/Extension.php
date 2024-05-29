<?php

namespace Igniter\Cart;

use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Concerns\LocationAction;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\Observers\MenuItemOptionObserver;
use Igniter\Cart\Models\Observers\MenuObserver;
use Igniter\Cart\Models\Observers\MenuOptionObserver;
use Igniter\Cart\Models\Observers\OrderObserver;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\Scopes\CategoryScope;
use Igniter\Cart\Models\Scopes\MenuScope;
use Igniter\Cart\Models\Scopes\OrderScope;
use Igniter\Flame\Igniter;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\System\Classes\BaseExtension;
use Igniter\System\Models\Settings;
use Igniter\User\Facades\Auth;
use Igniter\User\Http\Controllers\Customers;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;

class Extension extends BaseExtension
{
    protected $observers = [
        MenuItemOption::class => MenuItemOptionObserver::class,
        MenuOption::class => MenuOptionObserver::class,
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

        $this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'igniter-cart');

        $this->registerCart();
        $this->registerSystemSettings();

        AliasLoader::getInstance()->alias('Cart', Facades\Cart::class);
    }

    public function boot()
    {
        if (!Igniter::runningInAdmin()) {
            $this->app['router']->pushMiddlewareToGroup('igniter', Http\Middleware\CartMiddleware::class);
        }

        $this->bindCartEvents();
        $this->bindCheckoutEvents();
        $this->bindOrderStatusEvent();

        LocationModel::implement(LocationAction::class);

        Customers::extendFormFields(function(Form $form) {
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

    public function registerPermissions(): array
    {
        return [
            'Admin.Allergens' => [
                'label' => 'igniter.cart::default.text_permission_ingredients',
                'group' => 'igniter.cart::default.text_permission_menu_group',
            ],
            'Admin.Categories' => [
                'label' => 'igniter.cart::default.text_permission_categories',
                'group' => 'igniter.cart::default.text_permission_menu_group',
            ],
            'Admin.Menus' => [
                'label' => 'igniter.cart::default.text_permission_menus',
                'group' => 'igniter.cart::default.text_permission_menu_group',
            ],
            'Admin.Mealtimes' => [
                'label' => 'igniter.cart::default.text_permission_mealtimes',
                'group' => 'igniter.cart::default.text_permission_menu_group',
            ],
            'Admin.Orders' => [
                'label' => 'igniter.cart::default.text_permission_orders',
                'group' => 'igniter.cart::default.text_permission_order_group',
            ],
            'Admin.DeleteOrders' => [
                'label' => 'igniter.cart::default.text_permission_delete_orders',
                'group' => 'igniter.cart::default.text_permission_order_group',
            ],
            'Admin.AssignOrders' => [
                'label' => 'igniter.cart::default.text_permission_assign_orders',
                'group' => 'igniter.cart::default.text_permission_order_group',
            ],
            'Module.CartModule' => [
                'description' => 'Manage cart extension settings',
                'group' => 'igniter.cart::default.text_permission_order_group',
            ],
        ];
    }

    public function registerSettings(): array
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

    public function registerMailTemplates(): array
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

    public function registerNavigation(): array
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

    public function registerFormWidgets(): array
    {
        return [
            \Igniter\Cart\FormWidgets\StockEditor::class => [
                'label' => 'Stock Editor',
                'code' => 'stockeditor',
            ],
        ];
    }

    public function registerOrderTypes()
    {
        return [
            \Igniter\Cart\OrderTypes\Delivery::class => [
                'code' => LocationModel::DELIVERY,
                'name' => 'lang:igniter.local::default.text_delivery',
            ],
            \Igniter\Cart\OrderTypes\Collection::class => [
                'code' => LocationModel::COLLECTION,
                'name' => 'lang:igniter.local::default.text_collection',
            ],
        ];
    }

    public function registerLocationSettings()
    {
        return [
            'checkout' => [
                'label' => 'igniter.cart::default.settings.text_tab_checkout',
                'description' => 'igniter.cart::default.settings.text_tab_desc_checkout',
                'icon' => 'fa fa-sliders',
                'priority' => 0,
                'form' => 'igniter.cart::/models/checkoutsettings',
                'request' => \Igniter\Cart\Http\Requests\CheckoutSettingsRequest::class,
            ],
            'delivery' => [
                'label' => 'igniter.cart::default.settings.text_tab_delivery',
                'description' => 'igniter.cart::default.settings.text_tab_desc_delivery',
                'icon' => 'fa fa-sliders',
                'priority' => 0,
                'form' => 'igniter.cart::/models/deliverysettings',
                'request' => \Igniter\Cart\Http\Requests\DeliverySettingsRequest::class,
            ],
            'collection' => [
                'label' => 'igniter.cart::default.settings.text_tab_collection',
                'description' => 'igniter.cart::default.settings.text_tab_desc_collection',
                'icon' => 'fa fa-sliders',
                'priority' => 0,
                'form' => 'igniter.cart::/models/collectionsettings',
                'request' => \Igniter\Cart\Http\Requests\CollectionSettingsRequest::class,
            ],
        ];
    }

    protected function bindCartEvents()
    {
        Event::listen('igniter.user.login', function() {
            if (Models\CartSettings::get('abandoned_cart')
                && Facades\Cart::content()->isEmpty()
            ) {
                Facades\Cart::restore(Auth::getId());
            }
        });

        Event::listen('igniter.user.logout', function() {
            if (Models\CartSettings::get('destroy_on_logout')) {
                Facades\Cart::destroy();
            }
        });
    }

    protected function bindCheckoutEvents()
    {
        Event::listen('payregister.paypalexpress.extendFields', function($payment, &$fields, $order, $data) {
            if ($tax = $order->getOrderTotals()->firstWhere('code', 'tax')) {
                $fields['purchase_units'][0]['amount']['breakdown']['tax_total'] = [
                    'currency_code' => $fields['purchase_units'][0]['amount']['currency_code'],
                    'value' => number_format($tax->value, 2, '.', ''),
                ];
            }
        });

        Event::listen('admin.order.paymentProcessed', function(Order $model) {
            Notifications\OrderCreatedNotification::make()->subject($model)->broadcast();

            $model->mailSend('igniter.cart::mail.order', 'customer');
            $model->mailSend('igniter.cart::mail.order_alert', 'location');
            $model->mailSend('igniter.cart::mail.order_alert', 'admin');
        });

        Event::listen('admin.order.beforePaymentProcessed', function(Order $model) {
            $model->subtractStock();
        });

        Event::listen('igniter.cart.orderStatusAdded', function(Order $model, $statusHistory) {
            if ($statusHistory->notify) {
                $model->reloadRelations();
                $model->mailSend('igniter.cart::mail.order_update', 'customer');
            }
        });
    }

    protected function bindOrderStatusEvent()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function($statusHistory, $order, $statusId, $previousStatus) {
            if (!$order instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.beforeAddOrderStatus', [$statusHistory, $order, $statusId, $previousStatus], true);
        });

        Event::listen('admin.statusHistory.added', function($order, $statusHistory) {
            if (!$order instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.orderStatusAdded', [$order, $statusHistory], true);
        });

        Event::listen('admin.assignable.assigned', function($order, $assignableLog) {
            if (!$order instanceof Order) {
                return;
            }

            Event::fire('igniter.cart.orderAssigned', [$order, $assignableLog], true);
        });
    }

    protected function registerCart(): void
    {
        $this->app->singleton('cart', function($app) {
            $this->app['config']->set('igniter-cart.model', Models\Cart::class);
            $this->app['config']->set('igniter-cart.abandonedCart', Models\CartSettings::get('abandoned_cart'));
            $this->app['config']->set('igniter-cart.destroyOnLogout', Models\CartSettings::get('destroy_on_logout'));

            $this->app['events']->dispatch('cart.beforeRegister', [$this]);

            $instance = new Cart($app['session'], $app['events']);

            $this->app['events']->dispatch('cart.afterRegister', [$instance, $this]);

            return $instance;
        });
    }

    protected function registerSystemSettings()
    {
        Settings::registerCallback(function(Settings $manager) {
            $manager->registerSettingItems('core', [
                'order' => [
                    'label' => 'lang:igniter.cart::default.text_tab_order',
                    'description' => 'lang:igniter.cart::default.text_tab_desc_order',
                    'icon' => 'fa fa-file-invoice',
                    'priority' => 1,
                    'permission' => ['Site.Settings'],
                    'url' => admin_url('settings/edit/order'),
                    'form' => 'igniter.cart::/models/ordersettings',
                    'request' => \Igniter\Cart\Http\Requests\OrderSettingsRequest::class,
                ],
            ]);
        });
    }
}
