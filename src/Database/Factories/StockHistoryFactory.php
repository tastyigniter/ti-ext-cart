<?php

declare(strict_types=1);

namespace Igniter\Cart\Database\Factories;

use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\Stock;
use Igniter\Cart\Models\StockHistory;
use Igniter\Flame\Database\Factories\Factory;
use Igniter\User\Models\User;

class StockHistoryFactory extends Factory
{
    protected $model = StockHistory::class;

    public function definition()
    {
        return [
            'stock_id' => Stock::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'state' => Stock::STATE_SOLD,
            'quantity' => 1,
        ];
    }
}
