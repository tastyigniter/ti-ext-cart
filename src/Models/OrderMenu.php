<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Casts\Serialize;

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
 * @mixin \Igniter\Flame\Database\Model
 */
class OrderMenu extends \Igniter\Flame\Database\Model
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
            'order' => \Igniter\Cart\Models\Order::class,
            'menu' => \Igniter\Cart\Models\Menu::class,
        ],
        'hasMany' => [
            'menu_options' => \Igniter\Cart\Models\OrderMenuOptionValue::class,
        ],
    ];
}
