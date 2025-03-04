<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Model;
use IgniterLabs\ImportExport\Models\ExportModel;
use Override;

/**
 * MenuExport Model Class
 *
 * @property int $menu_id
 * @property string $menu_name
 * @property string $menu_description
 * @property string $menu_price
 * @property int $minimum_qty
 * @property bool $menu_status
 * @property int $menu_priority
 * @property string|null $order_restriction
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read mixed $categories
 * @mixin Model
 */
class MenuExport extends ExportModel
{
    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    public $relation = [
        'belongsToMany' => [
            'menu_categories' => [Category::class, 'table' => 'menu_categories', 'foreignKey' => 'menu_id'],
        ],
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array<int, string>
     */
    protected $appends = [
        'categories',
    ];

    #[Override]
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
