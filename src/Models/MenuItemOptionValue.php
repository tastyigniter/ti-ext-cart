<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Carbon;

/**
 * MenuItemOptionValue Model Class
 *
 * @property int $menu_option_value_id
 * @property int $menu_option_id
 * @property int $option_value_id
 * @property float|null $override_price
 * @property int $priority
 * @property bool|null $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property null|MenuOptionValue $option_value
 * @property-read mixed $name
 * @property-read mixed $price
 * @mixin Model
 */
class MenuItemOptionValue extends Model
{
    use HasFactory;
    use Validation;

    protected static $optionValuesCollection;

    /**
     * @var string The database table name
     */
    protected $table = 'menu_item_option_values';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'menu_option_value_id';

    protected $fillable = ['menu_option_id', 'option_value_id', 'override_price', 'priority', 'is_default'];

    public $appends = ['name', 'price'];

    protected $casts = [
        'menu_option_value_id' => 'integer',
        'menu_option_id' => 'integer',
        'option_value_id' => 'integer',
        'override_price' => 'float',
        'priority' => 'integer',
        'is_default' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'menu' => [Menu::class],
            'option_value' => [MenuOptionValue::class],
            'menu_option' => [MenuItemOption::class],
        ],
    ];

    public $rules = [
        ['menu_option_id', 'igniter.cart::default.menu_options.label_option_value_id', 'required|integer'],
        ['option_value_id', 'igniter.cart::default.menu_options.label_option_value', 'required|integer'],
        ['override_price', 'igniter.cart::default.menu_options.label_option_price', 'nullable|numeric|min:0'],
    ];

    public $timestamps = true;

    public function getNameAttribute()
    {
        return $this->option_value->name ?? null;
    }

    public function getPriceAttribute()
    {
        if ($this->override_price) {
            return $this->override_price;
        }

        return $this->option_value->price ?? null;
    }

    public function isDefault(): bool
    {
        return $this->is_default == 1;
    }
}
