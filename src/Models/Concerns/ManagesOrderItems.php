<?php

declare(strict_types=1);

namespace Igniter\Cart\Models\Concerns;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\OrderMenu;
use Igniter\Cart\Models\OrderMenuOptionValue;
use Illuminate\Support\Collection;

trait ManagesOrderItems
{
    /**
     * Subtract cart item quantity from menu stock quantity
     */
    public function subtractStock(): void
    {
        $this->getOrderMenus()->each(function($orderMenu) {
            /** @var null|Menu $menu */
            $menu = Menu::find($orderMenu->menu_id);
            if (!$menu) {
                return true;
            }

            $menu->getStockByLocation($this->location)->updateStockSold($this->getKey(), $orderMenu->quantity);

            $this->menu_options
                ->where('order_menu_id', $orderMenu->order_menu_id)
                ->each(function(OrderMenuOptionValue $orderMenuOption) {
                    /** @var null|MenuItemOptionValue $menuItemOptionValue */
                    $menuItemOptionValue = MenuItemOptionValue::find($orderMenuOption->menu_option_value_id);
                    if (!$menuItemOptionValue) {
                        return true;
                    }

                    if (!$menuOptionValue = $menuItemOptionValue->option_value) {
                        return true;
                    }

                    $menuOptionValue->getStockByLocation($this->location)->updateStockSold(
                        $this->getKey(), $orderMenuOption->quantity,
                    );
                });
        });
    }

    /**
     * Return all order menu by order_id
     */
    public function getOrderMenus(): Collection
    {
        return $this->menus;
    }

    /**
     * Return all order menu options by order_id
     */
    public function getOrderMenuOptions(): Collection
    {
        return $this->menu_options->groupBy('order_menu_id');
    }

    /**
     * Return all order menus merged with order menu options
     */
    public function getOrderMenusWithOptions(): Collection
    {
        $this->load('menus.menu_options');

        $this->menus->each(function(OrderMenu $orderMenu): void {
            $orderMenuOptions = $orderMenu->menu_options
                ->groupBy(fn(OrderMenuOptionValue $orderMenuOptionValue) => $orderMenuOptionValue->menu_option->option_name);
            $orderMenu->setRelation('menu_options', $orderMenuOptions);
        });

        return $this->menus;
    }

    /**
     * Return all order totals by order_id
     */
    public function getOrderTotals(): Collection
    {
        return $this->totals->sortBy('priority');
    }

    /**
     * Add cart menu items to order by order_id
     */
    public function addOrderMenus(array $content): void
    {
        $this->menus()->delete();
        $this->menu_options()->delete();

        foreach ($content as $cartItem) {
            $orderMenu = $this->menus()->create([
                'menu_id' => $cartItem->id,
                'name' => $cartItem->name,
                'quantity' => $cartItem->qty,
                'price' => $cartItem->price,
                'subtotal' => $cartItem->subtotal,
                'comment' => $cartItem->comment,
                'option_values' => serialize($cartItem->options),
            ]);

            if ($orderMenu && count($cartItem->options)) {
                $this->addOrderMenuOptions($orderMenu->getKey(), $cartItem->id, $cartItem->options);
            }
        }
    }

    /**
     * Add cart menu item options to menu and order by,
     * order_id and menu_id
     *
     * @return bool
     */
    protected function addOrderMenuOptions($orderMenuId, $menuId, $menuOptions)
    {
        foreach ($menuOptions as $menuOption) {
            foreach ($menuOption->values as $menuOptionValue) {
                $this->menu_options()->create([
                    'order_menu_id' => $orderMenuId,
                    'menu_option_id' => $menuOption->id,
                    'menu_option_value_id' => $menuOptionValue->id,
                    'order_option_name' => $menuOptionValue->name,
                    'order_option_price' => $menuOptionValue->price,
                    'quantity' => $menuOptionValue->qty,
                ]);
            }
        }
    }

    /**
     * Add cart totals to order by order_id
     */
    public function addOrderTotals(array $totals = []): void
    {
        foreach ($totals as $total) {
            $this->addOrUpdateOrderTotal($total);
        }

        $this->calculateTotals();
    }

    public function addOrUpdateOrderTotal(array $total)
    {
        return $this->totals()->updateOrCreate([
            'code' => $total['code'],
        ], array_except($total, ['order_id', 'code']));
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->menus()->sum('subtotal');
        $totalItems = $this->menus()->sum('quantity');

        $total = $this->totals()->where('is_summable', true)->sum('value');

        $orderTotal = max(0, $subtotal + $total);

        $this->totals()->where('code', 'subtotal')->update(['value' => $subtotal]);

        $this->totals()
            ->where('order_id', $this->getKey())
            ->where('code', 'total')
            ->update(['value' => $orderTotal]);

        $this->newQuery()->where('order_id', $this->getKey())->update([
            'total_items' => $totalItems,
            'order_total' => $orderTotal,
        ]);
    }
}
