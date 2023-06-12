<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use IgniterLabs\ImportExport\Models\ExportModel;

class MenuExport extends ExportModel
{
    use HasMedia;

    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    public $relation = [
        'belongsToMany' => [
            'menu_categories' => [\Igniter\Cart\Models\Category::class, 'table' => 'menu_categories', 'foreignKey' => 'menu_id'],
            'menu_mealtimes' => [\Igniter\Cart\Models\Mealtime::class, 'table' => 'menu_mealtimes', 'foreignKey' => 'menu_id'],
        ],
    ];

    public $mediable = ['thumb'];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'categories',
        'thumb_url',
        'mealtimes',
    ];

    public function exportData($columns)
    {
        return self::make()->with([
            'menu_mealtimes',
            'menu_categories',
            'media',
        ])->get()->toArray();
    }

    public function getCategoriesAttribute()
    {
        if (!$this->menu_categories) {
            return '';
        }

        return $this->encodeArrayValue($this->menu_categories->pluck('name')->all());
    }

    public function getThumbUrlAttribute()
    {
        if (!$this->hasMedia('thumb')) {
            return '';
        }

        return $this->getFirstMedia('thumb')->getPath();
    }

    public function getMealtimesAttribute()
    {
        if (!$this->menu_mealtimes) {
            return '';
        }

        return $this->encodeArrayValue($this->menu_mealtimes->pluck('mealtime_name')->all());
    }
}
