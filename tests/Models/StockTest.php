<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Stock;
use Igniter\Flame\Database\Model;
use Igniter\Local\Models\Location;
use Igniter\System\Mail\AnonymousTemplateMailable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

it('applies stockable scope with correct model type and id', function(): void {
    $query = mock(Builder::class);
    $model = mock(Model::class)->makePartial();
    $model->shouldReceive('getMorphClass')->andReturn('TestModel');
    $model->shouldReceive('getKey')->andReturn(1);
    $query->shouldReceive('where')->with('stockable_type', 'TestModel')->andReturnSelf();
    $query->shouldReceive('where')->with('stockable_id', 1)->andReturnSelf();

    $result = (new Stock)->scopeApplyStockable($query, $model);

    expect($result)->toBe($query);
});

it('updates stock correctly', function(): void {
    Event::fake();
    Mail::fake();

    $menu = Menu::factory()->create();
    $stock = Stock::factory()->create([
        'stockable_id' => $menu->getKey(),
        'stockable_type' => $menu->getMorphClass(),
        'quantity' => 1,
        'is_tracked' => true,
        'low_stock_alert' => true,
        'low_stock_threshold' => 10,
    ]);

    $stock->updateStock(5, Stock::STATE_RESTOCK);

    expect($stock->quantity)->toBe(6);

    Event::assertDispatched('admin.stock.updated', function($event, $args) use ($stock): bool {
        [$updatedStock, $history, $stockQty] = $args;

        return $updatedStock->getKey() === $stock->getKey();
    });

    Mail::assertQueued(AnonymousTemplateMailable::class, fn($mailable): bool => $mailable->getTemplateCode() === 'igniter.cart::mail.low_stock_alert');
});

it('recounts stock correctly', function(): void {
    Event::fake();

    $menu = Menu::factory()->create();
    $stock = Stock::factory()->create([
        'stockable_id' => $menu->getKey(),
        'stockable_type' => $menu->getMorphClass(),
        'quantity' => 100,
        'low_stock_alert' => false,
        'low_stock_threshold' => 700,
    ]);

    $stock->updateStock(500, Stock::STATE_RECOUNT);

    expect($stock->quantity)->toBe(500);
});

it('does not update stock when not tracked', function(): void {
    Event::fake();

    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => false,
    ]);

    $stock->updateStock(5, Stock::STATE_SOLD);

    expect($stock->quantity)->toBe(10);
    Event::assertNotDispatched('admin.stock.updated');
});

it('throws exception when marking untrackable as out of stock', function(): void {
    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => false,
    ]);

    expect(fn() => $stock->markAsOutOfStock())->toThrow(sprintf(
        lang('igniter.cart::default.stocks.alert_stock_not_tracked'), $stock->stockable_name,
    ));
});

it('checks stock correctly', function(): void {
    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => true,
    ]);

    expect($stock->checkStock(5))->toBeTrue()
        ->and($stock->checkStock(15))->toBeFalse();

    $stock->is_tracked = false;

    expect($stock->checkStock(5))->toBeTrue();
});

it('checks if stock is out of stock correctly', function(): void {
    $stock = Stock::factory()->create([
        'quantity' => 0,
        'is_tracked' => true,
    ]);

    expect($stock->outOfStock())->toBeTrue();
});

it('checks if stock has low stock correctly', function(): void {
    $stock = Stock::factory()->create([
        'quantity' => 5,
        'low_stock_threshold' => 10,
        'is_tracked' => true,
    ]);

    expect($stock->hasLowStock())->toBeTrue();
});

it('gets mail recipients correctly', function(): void {
    $location = Location::factory()->create();
    $stock = Stock::factory()->for($location)->create();

    $recipients = $stock->mailGetRecipients('location');

    expect($recipients)->toBe([[$location->location_email, $location->location_name]]);
});

it('gets mail data correctly', function(): void {
    $menu = Menu::factory()->create(['menu_name' => 'Test Stock']);
    $location = Location::factory()->create(['location_name' => 'Test Location']);
    $stock = Stock::factory()->for($location)->create([
        'stockable_id' => $menu->getKey(),
        'stockable_type' => $menu->getMorphClass(),
    ]);

    $mailData = $stock->mailGetData();

    expect($mailData['stock_name'])->toBe('Test Stock')
        ->and($mailData['location_name'])->toBe('Test Location');
});

it('configures stock model correctly', function(): void {
    $stock = new Stock;
    expect($stock->getTable())->toBe('stocks')
        ->and($stock->getKeyName())->toBe('id')
        ->and($stock->getGuarded())->toBe(['quantity'])
        ->and($stock->timestamps)->toBeTrue()
        ->and($stock->getMorphClass())->toBe('stocks');
});
