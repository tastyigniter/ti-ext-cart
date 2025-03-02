<?php

namespace Igniter\Cart\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\FormWidgets\StockEditor;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Cart\Models\Menu;
use Igniter\Local\Models\Location;

beforeEach(function() {
    $this->menuItem = Menu::factory()->create();
    $this->stockEditorWidget = new StockEditor(
        resolve(Menus::class),
        new FormField('test_field', 'Stock editor'),
        [
            'model' => $this->menuItem,
        ]
    );
});

it('initializes with config', function() {
    expect($this->stockEditorWidget->form)->toBe('stock')
        ->and($this->stockEditorWidget->quantityKeyFrom)->toBe('stock_qty');
});

it('prepares vars', function() {
    $this->stockEditorWidget->prepareVars();

    expect($this->stockEditorWidget->vars['field'])->toBeInstanceOf(FormField::class)
        ->and($this->stockEditorWidget->vars['value'])->toBe(0)
        ->and($this->stockEditorWidget->vars['previewMode'])->toBeFalse();
});

it('gets save value', function() {
    expect($this->stockEditorWidget->getSaveValue('test'))->toBe(FormField::NO_SAVE_DATA);
});

it('loads record', function() {
    expect($this->stockEditorWidget->onLoadRecord())->toBeString();
});

it('saves record', function() {
    $this->menuItem->locations()->save(Location::factory()->create());

    expect($this->stockEditorWidget->onSaveRecord())->toBeArray();
});

it('loads history', function() {
    expect($this->stockEditorWidget->onLoadHistory())->toBeString();
});
