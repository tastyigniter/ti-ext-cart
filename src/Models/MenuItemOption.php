<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Carbon;

/**
 * MenuItemOption Model Class
 *
 * @property int $menu_option_id
 * @property int $option_id
 * @property int $menu_id
 * @property bool $is_required
 * @property int $priority
 * @property int $min_selected
 * @property int $max_selected
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $display_type
 * @property-read mixed $option_name
 * @property-read mixed $option_values
 * @property-read Collection<int, MenuItemOptionValue> $menu_option_values
 * @mixin Model
 */
class MenuItemOption extends Model
{
    use HasFactory;
    use Purgeable;
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'menu_item_options';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'menu_option_id';

    protected $fillable = ['option_id', 'menu_id', 'is_required', 'priority', 'min_selected', 'max_selected'];

    protected $casts = [
        'menu_option_id' => 'integer',
        'option_id' => 'integer',
        'menu_id' => 'integer',
        'is_required' => 'boolean',
        'priority' => 'integer',
        'min_selected' => 'integer',
        'max_selected' => 'integer',
    ];

    public $relation = [
        'hasMany' => [
            'menu_option_values' => [
                MenuItemOptionValue::class,
                'foreignKey' => 'menu_option_id',
                'delete' => true,
            ],
        ],
        'belongsTo' => [
            'menu' => [Menu::class],
            'option' => [MenuOption::class],
        ],
    ];

    public $appends = ['option_name', 'display_type'];

    public $rules = [
        ['menu_id', 'igniter.cart::default.menus.label_menu_id', 'required|integer'],
        ['option_id', 'igniter.cart::default.menus.label_option_id', 'required|integer'],
        ['priority', 'igniter.cart::default.menu_options.label_option', 'integer'],
        ['is_required', 'igniter.cart::default.menu_options.label_option_required', 'boolean'],
        ['min_selected', 'igniter.cart::default.menu_options.label_min_selected', 'integer|lte:max_selected'],
        ['max_selected', 'igniter.cart::default.menu_options.label_max_selected', 'integer|gte:min_selected'],
    ];

    protected $purgeable = ['menu_option_values'];

    public $with = ['option'];

    public $timestamps = true;

    public function getOptionNameAttribute($value = null)
    {
        return $value ?: optional($this->option)->option_name;
    }

    public function getDisplayTypeAttribute()
    {
        return optional($this->option)->display_type;
    }

    public function getOptionValuesAttribute()
    {
        return $this->option->option_values->map(function($optionValue) {
            $menuOptionValue = $this->menu_option_values->firstWhere('option_value_id', $optionValue->getKey());

            $optionValue->menu_option_value_id = $menuOptionValue?->menu_option_value_id;
            $optionValue->menu_option_id = $menuOptionValue?->menu_option_id ?? $optionValue->option_id;
            $optionValue->option_value_id = $menuOptionValue?->option_value_id ?? $optionValue->getKey();
            $optionValue->price = $menuOptionValue?->price ?? $optionValue->price;
            $optionValue->override_price = $menuOptionValue?->override_price;
            $optionValue->is_default = $menuOptionValue?->is_default;
            $optionValue->is_enabled = !is_null($menuOptionValue);

            return $optionValue;
        });
    }

    //
    // Helpers
    //
    public function isRequired()
    {
        return $this->is_required;
    }

    /**
     * Create new or update existing menu option values
     *
     * @param array $optionValues if empty all existing records will be deleted
     */
    public function addMenuOptionValues(array $optionValues = []): int
    {
        $menuOptionId = $this->getKey();
        $idsToKeep = [];
        foreach ($optionValues as $value) {
            $menuOptionValue = $this->menu_option_values()->firstOrNew([
                'menu_option_value_id' => array_get($value, 'menu_option_value_id'),
                'menu_option_id' => $menuOptionId,
            ])->fill(array_except($value, ['menu_option_value_id', 'menu_option_id']));
            $menuOptionValue->saveOrFail();
            $idsToKeep[] = $menuOptionValue->getKey();
        }

        $this->menu_option_values()
            ->where('menu_option_id', $menuOptionId)
            ->whereNotIn('menu_option_value_id', $idsToKeep)
            ->delete();

        return count($idsToKeep);
    }
}
