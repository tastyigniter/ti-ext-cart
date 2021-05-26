<?php

namespace Igniter\Cart\Models;

use Admin\Models\Menus_model as BaseMenus_model;
use Igniter\Flame\Cart\Contracts\Buyable;

class Menus_model extends BaseMenus_model implements Buyable
{
    public $with = [
        'special',
        'media',
        'allergens',
        'allergens.media',
        'mealtimes',
        'menu_options',
        'menu_options.option',
    ];

    public static function findBy($menuId, $location = null)
    {
        $query = self::query();

        if (!is_null($location)) {
            $query->with(['menu_options' => function ($query) use ($location) {
                $query->whereHas('option', function ($query) use ($location) {
                    $query->whereHasOrDoesntHaveLocation($location);
                });
            }]);
        }

        return $query->isEnabled()->whereKey($menuId)->first();
    }

    public function getMorphClass()
    {
        return 'menus';
    }

    public function iSpecial()
    {
        if (!$special = $this->special)
            return FALSE;

        return $special->active();
    }

    public function checkMinQuantity($quantity = 0)
    {
        return $quantity >= $this->minimum_qty;
    }

    public function outOfStock()
    {
        return $this->stock_qty < 0;
    }

    public function checkStockLevel($quantity = 0)
    {
        if ($this->stock_qty == 0)
            return TRUE;

        return $this->stock_qty >= $quantity;
    }

    public function hasOrderTypeRestriction($orderType)
    {
        if (empty($this->order_restriction))
            return FALSE;

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
        $price = $this->iSpecial()
            ? $this->special->getMenuPrice($this->menu_price) : $this->menu_price;

        return $price;
    }
}
