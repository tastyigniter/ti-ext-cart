<?php

declare(strict_types=1);

namespace Igniter\Cart\Database\Factories;

use Igniter\Cart\Models\Stock;
use Igniter\Flame\Database\Factories\Factory;
use Igniter\Local\Models\Location;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition()
    {
        return [
            'location_id' => Location::factory(),
            'is_tracked' => 1,
            'low_stock_alert' => 1,
            'low_stock_threshold' => 1,
        ];
    }
}
