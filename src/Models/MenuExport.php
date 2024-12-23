<?php

namespace Igniter\Cart\Models;

use IgniterLabs\ImportExport\Models\ExportModel;

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
