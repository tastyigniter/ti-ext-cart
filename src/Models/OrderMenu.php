<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Casts\Serialize;
use Igniter\Flame\Database\Model;

/**
 * OrderMenu Model
 *
 * @property int $order_menu_id
 * @property int $order_id
 * @property int $menu_id
 * @property string $name
 * @property int $quantity
 * @property float|null $price
 * @property float|null $subtotal
 * @property mixed|null $option_values
 * @property string|null $comment
 * @mixin Model
 */
class OrderMenu extends Model
{
    protected $table = 'order_menus';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'order_menu_id';

    public $guarded = [];

    protected $casts = [
        'order_id' => 'integer',
        'menu_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'float',
        'subtotal' => 'float',
        'option_values' => Serialize::class,
    ];

    public $relation = [
        'belongsTo' => [
            'order' => Order::class,
            'menu' => Menu::class,
        ],
        'hasMany' => [
            'menu_options' => OrderMenuOptionValue::class,
        ],
    ];
}
