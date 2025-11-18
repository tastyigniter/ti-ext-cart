<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models\Observers;

use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Cart\Models\Observers\MenuOptionValueObserver;
use Mockery;

beforeEach(function(): void {
    $this->observer = new MenuOptionValueObserver;
    $this->menuOptionValue = Mockery::mock(MenuOptionValue::class)->makePartial();
});

it('validates menu option value on saving', function(): void {
    $this->menuOptionValue->shouldReceive('validate')->once();

    $this->observer->saving($this->menuOptionValue);
});

it('detaches ingredients when deleting', function(): void {
    $this->menuOptionValue->shouldReceive('ingredients->detach')->once();

    $this->observer->deleting($this->menuOptionValue);
});
