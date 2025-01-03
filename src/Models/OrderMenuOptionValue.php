<?php

namespace Igniter\Cart\Models;

/**
 * OrderMenuOptionValue Model
 *
 * @property int $order_option_id
 * @property int $order_id
 * @property string $order_option_name
 * @property float|null $order_option_price
 * @property int $order_menu_id
 * @property int $menu_option_id
 * @property int $menu_option_value_id
 * @property int|null $quantity
 * @property-read mixed $order_option_category
 * @mixin \Igniter\Flame\Database\Model
 */
class OrderMenuOptionValue extends \Igniter\Flame\Database\Model
{
    protected $table = 'order_menu_options';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'order_option_id';

    public $guarded = [];

    public $appends = ['order_option_category'];

    protected $casts = [
        'order_menu_id' => 'integer',
        'menu_option_id' => 'integer',
        'menu_option_value_id' => 'integer',
        'quantity' => 'integer',
        'order_option_price' => 'float',
    ];

    public $relation = [
        'belongsTo' => [
            'order' => \Igniter\Cart\Models\Order::class,
            'order_menu' => \Igniter\Cart\Models\OrderMenu::class,
            'menu_option' => \Igniter\Cart\Models\MenuItemOption::class,
            'menu_option_value' => \Igniter\Cart\Models\MenuItemOptionValue::class,
        ],
    ];

    public function getOrderOptionCategoryAttribute()
    {
        return $this->menu_option->option_name;
    }
}
