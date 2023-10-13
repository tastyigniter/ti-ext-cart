<?php

namespace Igniter\Cart\Models;

use Carbon\Carbon;
use Igniter\Cart\Contracts\Buyable;
use Igniter\Cart\Models\Concerns\Stockable;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Menu Model Class
 */
class Menu extends Model implements Buyable
{
    use HasFactory;
    use HasMedia;
    use Locationable;
    use Purgeable;
    use Stockable;
    use Switchable;

    public const LOCATIONABLE_RELATION = 'locations';

    public const SWITCHABLE_COLUMN = 'menu_status';

    /**
     * @var string The database table name
     */
    protected $table = 'menus';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'menu_id';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'menu_price' => 'float',
        'menu_category_id' => 'integer',
        'minimum_qty' => 'integer',
        'order_restriction' => 'array',
        'menu_priority' => 'integer',
    ];

    public $relation = [
        'hasMany' => [
            'menu_options' => [\Igniter\Cart\Models\MenuItemOption::class, 'delete' => true],
        ],
        'hasOne' => [
            'special' => [\Igniter\Cart\Models\MenuSpecial::class, 'delete' => true],
        ],
        'belongsToMany' => [
            'categories' => [\Igniter\Cart\Models\Category::class, 'table' => 'menu_categories'],
            'mealtimes' => [\Igniter\Cart\Models\Mealtime::class, 'table' => 'menu_mealtimes'],
        ],
        'morphToMany' => [
            'allergens' => [\Igniter\Cart\Models\Ingredient::class, 'name' => 'ingredientable', 'conditions' => 'is_allergen = 1'],
            'ingredients' => [\Igniter\Cart\Models\Ingredient::class, 'name' => 'ingredientable'],
            'locations' => [\Igniter\Local\Models\Location::class, 'name' => 'locationable'],
        ],
    ];

    protected $purgeable = ['menu_options', 'special'];

    public $mediable = ['thumb'];

    protected array $queryModifierFilters = [
        'enabled' => ['applySwitchable', 'default' => true],
        'group' => 'applyCategoryGroup',
        'location' => 'applyLocation',
        'category' => 'whereHasCategory',
        'orderType' => 'applyOrderType',
    ];

    protected array $queryModifierSorts = ['menu_priority asc', 'menu_priority desc'];

    protected array $queryModifierSearchableFields = ['menu_name', 'menu_description'];

    public function getMenuPriceFromAttribute()
    {
        if (!$this->menu_options) {
            return $this->menu_price;
        }

        return $this->menu_options->mapWithKeys(function ($option) {
            return $option->menu_option_values->keyBy('menu_option_value_id');
        })->min('price') ?: 0;
    }

    public function getMinimumQtyAttribute($value)
    {
        return $value ?: 1;
    }

    //
    // Helpers
    //

    public function hasOptions()
    {
        return count($this->menu_options);
    }

    /**
     * Subtract or add to menu stock quantity
     *
     * @param int $quantity
     * @param bool $subtract
     * @return bool TRUE on success, or FALSE on failure
     */
    public function updateStock($quantity = 0, $subtract = true)
    {
        traceLog('Menu::updateStock() has been deprecated, use Stock::updateStock() instead.');
    }

    /**
     * Create new or update existing menu allergens
     *
     * @param array $allergenIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuAllergens(array $allergenIds = [])
    {
        $this->addMenuIngredients($allergenIds);
    }

    /**
     * Create new or update existing menu categories
     *
     * @param array $categoryIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuCategories(array $categoryIds = [])
    {
        if (!$this->exists) {
            return false;
        }

        $this->categories()->sync($categoryIds);
    }

    /**
     * Create new or update existing menu ingredients
     *
     * @param array $ingredientIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuIngredients(array $ingredientIds = [])
    {
        if (!$this->exists) {
            return false;
        }

        $this->ingredients()->sync($ingredientIds);
    }

    /**
     * Create new or update existing menu mealtimes
     *
     * @param array $mealtimeIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuMealtimes(array $mealtimeIds = [])
    {
        if (!$this->exists) {
            return false;
        }

        $this->mealtimes()->sync($mealtimeIds);
    }

    /**
     * Create new or update existing menu options
     *
     * @param array $menuOptions if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuOption(array $menuOptions = [])
    {
        $menuId = $this->getKey();
        if (!is_numeric($menuId)) {
            return false;
        }

        $idsToKeep = [];
        foreach ($menuOptions as $option) {
            $option['menu_id'] = $menuId;
            $menuOption = $this->menu_options()->firstOrNew([
                'menu_option_id' => array_get($option, 'menu_option_id'),
            ])->fill(array_except($option, ['menu_option_id']));

            $menuOption->saveOrFail();
            $idsToKeep[] = $menuOption->getKey();
        }

        $this->menu_options()->whereNotIn('menu_option_id', $idsToKeep)->delete();

        return count($idsToKeep);
    }

    /**
     * Create new or update existing menu special
     *
     * @param bool $id
     *
     * @return bool
     */
    public function addMenuSpecial(array $menuSpecial = [])
    {
        $menuId = $this->getKey();
        if (!is_numeric($menuId)) {
            return false;
        }

        $menuSpecial['menu_id'] = $menuId;
        $this->special()->updateOrCreate([
            'special_id' => $menuSpecial['special_id'] ?? null,
        ], array_except($menuSpecial, 'special_id'));
    }

    /**
     * Is menu item available on a given datetime
     *
     * @param string | \Carbon\Carbon $datetime
     *
     * @return bool
     */
    public function isAvailable($datetime = null)
    {
        if (is_null($datetime)) {
            $datetime = Carbon::now();
        }

        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }

        if ($this->mealtimes->contains(fn($mealtime) => $mealtime->isEnabled() && !$mealtime->isAvailable($datetime))) {
            return false;
        }

        if ($this->ingredients->contains(fn($ingredient) => !$ingredient->isEnabled())) {
            return false;
        }

        if (($this->fireSystemEvent('admin.menu.isAvailable', [$datetime, true])) === false) {
            return false;
        }

        return true;
    }

    public static function findBy($menuId, $location = null)
    {
        return self::query()->whereIsEnabled()->whereKey($menuId)->first();
    }

    public function getMorphClass()
    {
        return 'menus';
    }

    public function isSpecial()
    {
        if (!$special = $this->special) {
            return false;
        }

        return $special->active();
    }

    public function checkMinQuantity($quantity = 0)
    {
        return $quantity >= $this->minimum_qty;
    }

    public function hasOrderTypeRestriction($orderType)
    {
        if (empty($this->order_restriction)) {
            return false;
        }

        return !in_array($orderType, $this->order_restriction);
    }

    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableName()
    {
        return $this->menu_name;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getBuyablePrice()
    {
        $price = $this->isSpecial()
            ? $this->special->getMenuPrice($this->menu_price) : $this->menu_price;

        return $price;
    }
}
