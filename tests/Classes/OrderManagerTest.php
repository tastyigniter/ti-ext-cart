<?php

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\CartConditions\Tip;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Cart\Models\CartSettings;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\PayRegister\Models\Payment;
use Igniter\PayRegister\Payments\Cod;
use Igniter\User\Models\Address;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->location = LocationModel::factory()->create();
    resolve('location')->setModel($this->location);

    $this->customer = Customer::factory()->create();
    $this->manager = new OrderManager();
});

it('gets customer id', function() {
    $this->actingAs($this->customer, 'igniter-customer');

    expect($this->manager->getCustomerId())->toBe($this->customer->id);
});

it('loads order', function() {
    $order = $this->manager->loadOrder();

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->customer_id)->toBe($this->customer->id)
        ->and($order->location_id)->toBe($this->location->getKey());
});

it('gets order by hash', function() {
    $order = Order::factory()->create([
        'hash' => 'test-hash',
    ]);

    expect($this->manager->getOrderByHash($order->hash))->toBeInstanceOf(Order::class);
});

it('gets default payment with valid class_name', function() {
    Payment::factory()->create([
        'code' => 'cod',
        'class_name' => Cod::class,
        'status' => 1,
        'is_default' => 1,
    ]);

    expect($this->manager->getDefaultPayment())->toBeInstanceOf(Payment::class);
});

it('gets payment with valid class_name', function() {
    $payment = Payment::factory()->create([
        'code' => 'cod',
        'class_name' => Cod::class,
        'status' => 1,
        'is_default' => 1,
    ]);

    expect($this->manager->getPayment($payment->code))->toBeInstanceOf(Payment::class);
});

it('has location payments', function() {
    Payment::factory()->create([
        'code' => 'cod',
        'class_name' => Cod::class,
        'status' => 1,
        'is_default' => 1,
    ]);

    $this->location->settings()->create([
        'item' => 'checkout.payments',
        'data' => ['invalid-code'],
    ]);

    expect($this->manager->getPaymentGateways()->all())->toBeEmpty();
});

it('finds delivery address', function() {
    $address = Address::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    expect($this->manager->findDeliveryAddress($address->getKey()))
        ->toBeInstanceOf(Address::class);
});

it('saves order', function() {
    Event::fake();

    $order = Order::factory()->create();
    $data = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com'
    ];

    expect($this->manager->saveOrder($order, $data))->toBeInstanceOf(Order::class);

    Event::assertDispatched('igniter.checkout.beforeSaveOrder');
});

it('processes payment', function() {
    Event::fake([
        'igniter.checkout.beforePayment',
        'admin.order.paymentProcessed',
    ]);

    $payment = Payment::factory()->create([
        'code' => 'cod',
        'class_name' => Cod::class,
        'status' => 1,
        'is_default' => 1,
    ]);

    $order = Order::factory()->create([
        'payment' => $payment->code,
    ]);

    expect($this->manager->processPayment($order, []))->toBeNull();
});

it('applies required attributes', function() {
    $order = Order::factory()->create();

    $this->manager->applyRequiredAttributes($order);

    $locationService = App::make('location');
    expect($order->customer_id)->toBe($this->manager->getCustomerId())
        ->and($order->location_id)->toBe($locationService->current()->getKey())
        ->and($order->order_type)->toBe($locationService->orderType());
});

it('gets cart totals', function() {
    CartSettings::set('enable_tipping', true);
    CartSettings::set('tip_value_type', 'F');

    $order = Order::factory()->create();
    $menu = Menu::factory()->create();

    $cartManager = resolve(CartManager::class);
    $cartManager->addCartItem($menu->getKey());

    $cartManager->getCart()->loadCondition(
        new Tip([
            'name' => 'tip',
            'label' => 'Test Tip',
            'metaData' => [
                'amount' => 10,
                'isCustom' => true
            ]
        ])
    );

    $totals = $this->manager->getCartTotals($order);

    expect(collect($totals)->keyBy('code')->all())
        ->toHaveKeys(['tip', 'subtotal', 'total']);
});

it('applies payment fee cart condition', function() {
    $menu = Menu::factory()->create();

    $cartManager = resolve(CartManager::class);
    $cartManager->addCartItem($menu->getKey());

    $condition = $this->manager->applyCurrentPaymentFee('payment-cod');

    expect($condition->getMetaData())->toBe(['code' => 'payment-cod'])
        ->and($this->manager->getSession('paymentCode'))->toBe('payment-cod');
});

it('returns default payment code when current payment code is not set', function() {
    $payment = Payment::factory()->create([
        'code' => 'cod',
        'class_name' => Cod::class,
        'status' => 1,
        'is_default' => 1,
    ]);

    expect($this->manager->getCurrentPaymentCode())->toBe($payment->code);
});

it('returns null when default payment is not set', function() {
    expect($this->manager->getCurrentPaymentCode())->toBeNull();
});
