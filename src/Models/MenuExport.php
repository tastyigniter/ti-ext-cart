<?php

namespace Igniter\Cart\Models;

use IgniterLabs\ImportExport\Models\ExportModel;

/**
 * MenuExport Model Class
 *
 * @property int $menu_id
 * @property string $menu_name
 * @property string $menu_description
 * @property string $menu_price
 * @property int $minimum_qty
 * @property boolean $menu_status
 * @property int $menu_priority
 * @property string|null $order_restriction
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read mixed $categories
 * @mixin \Igniter\Flame\Database\Model
 */
class MenuExport extends ExportModel
{
    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    public $relation = [
        'belongsToMany' => [
            'menu_categories' => [\Igniter\Cart\Models\Category::class, 'table' => 'menu_categories', 'foreignKey' => 'menu_id'],
        ],
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'categories',
    ];

    public function exportData($columns)
    {
        return self::make()->with([
            'menu_categories',
        ])->get()->toArray();
    }

    public function getCategoriesAttribute()
    {
        return $this->encodeArrayValue($this->menu_categories?->pluck('name')->all() ?? []);
    }
}
