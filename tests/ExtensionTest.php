<?php

namespace Igniter\Cart\Tests;

use Igniter\Admin\Models\StatusHistory;
use Igniter\Cart\AutomationRules\Conditions\OrderAttribute;
use Igniter\Cart\AutomationRules\Conditions\OrderStatusAttribute;
use Igniter\Cart\AutomationRules\Events\NewOrderStatus;
use Igniter\Cart\AutomationRules\Events\OrderAssigned;
use Igniter\Cart\AutomationRules\Events\OrderPlaced;
use Igniter\Cart\Extension;
use Igniter\Cart\Facades\Cart;
use Igniter\Cart\FormWidgets\StockEditor;
use Igniter\Cart\Http\Middleware\CartMiddleware;
use Igniter\Cart\Models\CartSettings;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Notifications\OrderCreatedNotification;
use Igniter\PayRegister\Models\Payment;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Mockery;

it('registers cart conditions correctly', function() {
    $extension = new Extension(app());

    $conditions = $extension->registerCartConditions();

    expect($conditions)->toBeArray()
        ->and($conditions)->toHaveKey(\Igniter\Cart\CartConditions\PaymentFee::class)
        ->and($conditions)->toHaveKey(\Igniter\Cart\CartConditions\Tax::class)
        ->and($conditions)->toHaveKey(\Igniter\Cart\CartConditions\Tip::class);
});

it('registers automation rules correctly', function() {
    $extension = new Extension(app());

    $rules = $extension->registerAutomationRules();

    expect($rules)->toBeArray()
        ->toHaveKeys(['events', 'conditions', 'actions'])
        ->and(OrderPlaced::class)->toBe($rules['events']['admin.order.paymentProcessed'])
        ->and(NewOrderStatus::class)->toBe($rules['events']['igniter.cart.orderStatusAdded'])
        ->and(OrderAssigned::class)->toBe($rules['events']['igniter.cart.orderAssigned'])
        ->and($rules['conditions'])->toContain(OrderAttribute::class, OrderStatusAttribute::class);
});

it('registers permissions correctly', function() {
    $extension = new Extension(app());

    $permissions = $extension->registerPermissions();

    expect($permissions)->toBeArray()
        ->and($permissions)->toHaveKey('Admin.Allergens')
        ->and($permissions)->toHaveKey('Admin.Categories')
        ->and($permissions)->toHaveKey('Admin.Menus')
        ->and($permissions)->toHaveKey('Admin.Mealtimes')
        ->and($permissions)->toHaveKey('Admin.Orders')
        ->and($permissions)->toHaveKey('Admin.DeleteOrders')
        ->and($permissions)->toHaveKey('Admin.AssignOrders')
        ->and($permissions)->toHaveKey('Module.CartModule');
});

it('registers settings correctly', function() {
    $extension = new Extension(app());

    $settings = $extension->registerSettings();

    expect($settings)->toBeArray()
        ->and($settings)->toHaveKey('settings')
        ->and($settings['settings']['model'])->toBe(CartSettings::class)
        ->and($settings['settings']['permissions'])->toBe(['Module.CartModule']);
});

it('registers mail templates correctly', function() {
    $extension = new Extension(app());

    $templates = $extension->registerMailTemplates();

    expect($templates)->toBeArray()
        ->and($templates)->toHaveKey('igniter.cart::mail.order')
        ->and($templates)->toHaveKey('igniter.cart::mail.order_alert')
        ->and($templates)->toHaveKey('igniter.cart::mail.order_update')
        ->and($templates)->toHaveKey('igniter.cart::mail.low_stock_alert');
});

it('registers navigation correctly', function() {
    $extension = new Extension(app());

    $navigation = $extension->registerNavigation();

    expect($navigation)->toBeArray()
        ->and($navigation)->toHaveKey('restaurant')
        ->and($navigation)->toHaveKey('sales')
        ->and($navigation['restaurant']['child'])->toHaveKey('menus')
        ->and($navigation['restaurant']['child'])->toHaveKey('mealtimes')
        ->and($navigation['sales']['child'])->toHaveKey('orders');
});

it('registers form widgets correctly', function() {
    $extension = new Extension(app());

    $widgets = $extension->registerFormWidgets();

    expect($widgets)->toBeArray()
        ->and($widgets)->toHaveKey(StockEditor::class);
});

it('restores cart session on login correctly', function() {
    CartSettings::set('abandoned_cart', 1);
    $customer = Customer::factory()->create();

    Cart::shouldReceive('content->isEmpty')->andReturnTrue();
    Cart::shouldReceive('restore')->with($customer->getKey())->once();

    Auth::shouldReceive('getId')->andReturn($customer->getKey());

    event('igniter.user.login', [$customer]);
});

it('destroys cart session on logout correctly', function() {
    CartSettings::set('destroy_on_logout', 1);
    $customer = Customer::factory()->create();

    Cart::shouldReceive('destroy')->once();

    event('igniter.user.logout', [$customer]);
});

it('adds tax info to paypalexpress request parameters', function() {
    $fields = $data = [];
    $payment = Payment::factory()->create(['code' => 'paypalexpress']);
    $order = Order::factory()->create();
    $order->totals()->create([
        'code' => 'tax',
        'title' => 'Tax',
        'value' => 10,
    ]);

    array_set($fields, 'purchase_units.0.amount.currency_code', 'GBP');

    event('payregister.paypalexpress.extendFields', [$payment, &$fields, $order, $data]);

    expect(array_get($fields, 'purchase_units.0.amount.breakdown.tax_total.value'))->toBe('10.00')
        ->and(array_get($fields, 'purchase_units.0.amount.breakdown.tax_total.currency_code'))->toBe('GBP');
});

it('subtracts stocks before payment is processed', function() {
    $orderMock = Mockery::mock(Order::class);
    $orderMock->shouldReceive('subtractStock')->once();

    event('admin.order.beforePaymentProcessed', [$orderMock]);
});

it('sends order confirmation after payment is processed', function() {
    Notification::fake();
    $notificationMock = Mockery::mock(OrderCreatedNotification::class);
    $notificationMock->shouldReceive('subject->broadcast')->andReturnSelf();
    app()->instance(OrderCreatedNotification::class, $notificationMock);

    $orderMock = Mockery::mock(Order::class);
    $orderMock->shouldReceive('mailSend')->with('igniter.cart::mail.order', 'customer')->once();
    $orderMock->shouldReceive('mailSend')->with('igniter.cart::mail.order_alert', 'location')->once();
    $orderMock->shouldReceive('mailSend')->with('igniter.cart::mail.order_alert', 'admin')->once();

    event('admin.order.paymentProcessed', [$orderMock]);
})->skip('Notification::fake() is not working');

it('sends order update after status is updated', function() {
    Mail::fake();
    Queue::fake();

    $orderMock = Mockery::mock(Order::class);
    $orderMock->shouldReceive('reloadRelations')->once();
    $orderMock->shouldReceive('mailSend')->with('igniter.cart::mail.order_update', 'customer')->once();
    $orderMock->shouldReceive('mailGetData')->andReturn([]);

    $statusHistoryMock = Mockery::mock(StatusHistory::class);
    $statusHistoryMock->shouldReceive('extendableGet')->with('notify')->andReturnTrue();

    event('igniter.cart.orderStatusAdded', [$orderMock, $statusHistoryMock]);
})->skip('Queue::fake() is not working');

it('adds cart middleware to frontend routes', function() {
    $middlewareGroups = Route::getMiddlewareGroups();
    expect($middlewareGroups)->toHaveKey('igniter')
        ->and($middlewareGroups['igniter'])->toContain(CartMiddleware::class);
});
