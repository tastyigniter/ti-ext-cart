<?php

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\CartItem;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Coupons\CartConditions\Coupon;
use Igniter\Coupons\Models\Coupon as CouponModel;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\Location as LocationModel;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->location = LocationModel::factory()->create();
    resolve('location')->setModel($this->location);

    $this->manager = new CartManager();
});

it('gets cart', function() {
    expect($this->manager->getCart()->currentInstance())
        ->toBe(App::make('cart')->currentInstance());
});

it('finds menu item', function() {
    $menu = Menu::factory()->create();

    expect($this->manager->findMenuItem($menu->getKey())?->getKey())
        ->toBe($menu->getKey());
});

it('throws exception for invalid menu id', function() {
    expect(fn() => $this->manager->findMenuItem('invalid'))->toThrow(ApplicationException::class);
});

it('adds cart item', function() {
    $menu = Menu::factory()->create();

    $item = $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    expect($item)->toBeInstanceOf(CartItem::class)
        ->and($item->id)->toBe($menu->getKey())
        ->and($item->qty)->toBe(1);
});

it('throws exception when adding menu item quantity lower that minimum quantity', function() {
    $menu = Menu::factory()->create(['minimum_qty' => 2]);

    expect(fn() => $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]))->toThrow(ApplicationException::class);
});

it('throws exception when adding out of stock menu item', function() {
    $menu = Menu::factory()->create();
    $menu->locations()->attach($this->location);
    $menu->stocks()->create([
        'is_tracked' => 1,
        'location_id' => $this->location->getKey(),
    ]);

    expect(fn() => $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]))->toThrow(ApplicationException::class);
});

it('throws exception when adding menu option not between the min and max selected', function() {
    $menu = Menu::factory()->create();

    $option = $menu->menu_options()->create([
        'option_id' => 1,
        'min_selected' => 3,
        'max_selected' => 5,
    ]);

    expect(fn() => $this->manager->addCartItem($menu->getKey(), [
        'quantity' => 1,
        'menu_options' => [
            $option->getKey() => ['option_values' => [1, 2]],
        ],
    ]))->toThrow(ApplicationException::class);
});

it('throws exception when required menu item option is not selected', function() {
    $menu = Menu::factory()->create();

    $option = $menu->menu_options()->create([
        'option_id' => 1,
        'is_required' => 1,
    ]);

    expect(fn() => $this->manager->addCartItem($menu->getKey(), [
        'quantity' => 1,
        'menu_options' => [
            $option->getKey() => ['option_values' => []],
        ],
    ]))->toThrow(ApplicationException::class);
});

it('throws exception when adding menu item that does not belong to current location', function() {
    $menu = Menu::factory()->create();
    $menu->locations()->attach(LocationModel::factory()->create());

    expect(fn() => $this->manager->addCartItem($menu->getKey(), [
        'quantity' => 1
    ]))->toThrow(ApplicationException::class);
});

it('updates cart item', function() {
    $menu = Menu::factory()->create();

    $item = $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    $updatedItem = $this->manager->updateCartItem($item->rowId, ['quantity' => 2]);

    expect($updatedItem->id)->toBe($menu->getKey())
        ->and($updatedItem->qty)->toBe(2);
});

it('removes cart item', function() {
    $menu = Menu::factory()->create();

    $item = $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    $this->manager->removeCartItem($item->rowId);

    expect($this->manager->getCart()->content())->not->toHaveKey($item->rowId);
});

it('updates cart item quantity', function() {
    $menu = Menu::factory()->create();

    $item = $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    $updatedItem = $this->manager->updateCartItemQty($item->rowId, 'plus');

    expect($updatedItem->qty)->toBe(2);
});

it('applies condition', function() {
    CouponModel::factory()->create(['code' => 'TEST']);

    $this->manager->getCart()->loadCondition(
        new Coupon(['label' => 'Coupon', 'name' => 'coupon'])
    );

    $condition = $this->manager->applyCondition('coupon', ['code' => 'TEST']);

    expect($condition->getMetaData())->toBe(['code' => 'TEST']);
});

it('removes condition', function() {
    CouponModel::factory()->create(['code' => 'TEST']);

    $this->manager->getCart()->loadCondition(
        new Coupon(['label' => 'Coupon', 'name' => 'coupon'])
    );

    $this->manager->applyCondition('coupon', ['code' => 'TEST']);

    $this->manager->removeCondition('coupon');

    expect($this->manager->getCart()->conditions())->not->toHaveKey('coupon');
});

it('checks cart total is below minimum order total', function() {
    $menu = Menu::factory()->create([
        'menu_price' => 10,
    ]);

    $this->location->settings()->create([
        'item' => 'delivery',
        'data' => ['min_order_amount' => 20],
    ]);

    $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    expect($this->manager->cartTotalIsBelowMinimumOrder())->toBeTrue();
});

it('checks cart total is above minimum order total', function() {
    $menu = Menu::factory()->create([
        'menu_price' => 30,
    ]);

    $this->location->settings()->create([
        'item' => 'delivery',
        'data' => ['min_order_amount' => 20],
    ]);

    $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    expect($this->manager->cartTotalIsBelowMinimumOrder())->toBeFalse();
});

it('checks delivery charge is unavailable', function() {
    $menu = Menu::factory()->create([
        'menu_price' => 30,
    ]);

    $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    expect($this->manager->deliveryChargeIsUnavailable())->toBeFalse();
});

it('restores cart with order menu items', function() {
    $menu = Menu::factory()->create();
    $order = Order::factory()->create();

    $order->addOrderMenus([
        (object)[
            'id' => $menu->getKey(),
            'name' => $menu->menu_name,
            'qty' => $menu->minimum_qty,
            'price' => $menu->menu_price,
            'subtotal' => $menu->menu_price,
            'comment' => 'Special instructions',
            'options' => [],
        ],
    ]);

    $this->manager->restoreWithOrderMenus($order->getOrderMenus());

    expect($this->manager->getCart()->content())->toHaveCount(1);
});
