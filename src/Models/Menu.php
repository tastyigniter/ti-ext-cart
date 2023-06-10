<?php

namespace Igniter\Cart\Models;

use Igniter\Admin\Models\Menu as BaseMenu;
use Igniter\Cart\Contracts\Buyable;

class Menu extends BaseMenu implements Buyable
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
