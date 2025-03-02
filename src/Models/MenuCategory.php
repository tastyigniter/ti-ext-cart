<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Model;

/**
 * MenuCategory Model Class
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
