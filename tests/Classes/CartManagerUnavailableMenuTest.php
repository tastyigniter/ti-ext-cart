<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\Classes\CartConditionManager;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\Menu;
use Igniter\Coupons\CartConditions\Coupon;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\Location as LocationModel;
use Mockery;

beforeEach(function(): void {
    $this->location = LocationModel::factory()->create();
    resolve('location')->setModel($this->location);

    $conditionManager = Mockery::mock(CartConditionManager::class);
    $conditionManager->shouldReceive('listRegisteredConditions')->andReturn([
        [
            'name' => 'coupon',
            'label' => 'Coupon',
        ],
        [
            'name' => 'disabled-condition',
            'label' => 'Disabled condition',
            'status' => false,
        ],
    ]);
    $conditionManager->shouldReceive('makeCondition')->andReturn(new Coupon([
        'label' => 'Coupon',
        'name' => 'coupon',
    ]));
    app()->instance(CartConditionManager::class, $conditionManager);

    $this->manager = new CartManager;
});

it('includes the menu name when adding an item that has become unavailable', function(): void {
    $menu = Menu::factory()->create();
    $menuName = $menu->getBuyableName();

    $menu->update(['menu_status' => 0]);
    $this->manager = new CartManager;

    expect(fn() => $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]))
        ->toThrow(ApplicationException::class, sprintf(
            lang('igniter.cart::default.alert_menu_not_found'),
            $menuName,
        ));
});

it('removes an unavailable menu item when its quantity is updated', function(): void {
    $menu = Menu::factory()->create();
    $item = $this->manager->addCartItem($menu->getKey(), ['quantity' => 1]);

    $menu->update(['menu_status' => 0]);
    $this->manager = new CartManager;

    $updatedItem = $this->manager->updateCartItemQty($item->rowId, 'minus');

    expect($updatedItem->qty)->toBe(0)
        ->and($this->manager->getCart()->content())->not->toHaveKey($item->rowId);
});

it('removes unavailable menu items during checkout validation and preserves available items', function(): void {
    $availableMenu = Menu::factory()->create();
    $unavailableMenu = Menu::factory()->create();

    $availableItem = $this->manager->addCartItem($availableMenu->getKey(), ['quantity' => 1]);
    $unavailableItem = $this->manager->addCartItem($unavailableMenu->getKey(), ['quantity' => 1]);

    $unavailableMenu->update(['menu_status' => 0]);
    $this->manager = new CartManager;

    expect(fn() => $this->manager->validateContents())
        ->toThrow(ApplicationException::class);

    expect($this->manager->getCart()->content())
        ->toHaveKey($availableItem->rowId)
        ->not->toHaveKey($unavailableItem->rowId);
});

it('removes all unavailable menu items during checkout validation', function(): void {
    $firstMenu = Menu::factory()->create();
    $secondMenu = Menu::factory()->create();

    $firstItem = $this->manager->addCartItem($firstMenu->getKey(), ['quantity' => 1]);
    $secondItem = $this->manager->addCartItem($secondMenu->getKey(), ['quantity' => 1]);

    $firstMenu->update(['menu_status' => 0]);
    $secondMenu->update(['menu_status' => 0]);
    $this->manager = new CartManager;

    expect(fn() => $this->manager->validateContents())
        ->toThrow(ApplicationException::class);

    expect($this->manager->getCart()->content())
        ->not->toHaveKey($firstItem->rowId)
        ->not->toHaveKey($secondItem->rowId);
});
