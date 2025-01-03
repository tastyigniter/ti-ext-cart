<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Model;

/**
 * MenuCategory Model Class
 *
 * @property int $menu_id
 * @property int $category_id
 * @mixin \Igniter\Flame\Database\Model
 */
class MenuCategory extends Model
{
    /**
     * @var string The database table name
     */
    protected $table = 'menu_categories';

    /**
     * @var string The database table primary key
     */
    public $incrementing = false;

    protected $casts = [
        'menu_id' => 'integer',
        'category_id' => 'integer',
    ];

    public $relation = [
        'belongsTo' => [
            'menu' => [\Igniter\Cart\Models\Menu::class],
            'category' => [\Igniter\Cart\Models\Category::class],
        ],
    ];
}
