<?php

namespace Igniter\Cart\Models\Observers;

use Igniter\Cart\Models\MenuItemOption;

class MenuItemOptionObserver
{
    public function saved(MenuItemOption $menuItemOption)
    {
        $menuItemOption->restorePurgedValues();

        if (array_key_exists('menu_option_values', $attributes = $menuItemOption->getAttributes())) {
            $menuItemOption->addMenuOptionValues(array_filter(array_map(function ($value) {
                if (array_get($value, 'is_enabled')) {
                    unset($value['is_enabled']);

                    return $value;
                }

                return false;
            }, array_get($attributes, 'menu_option_values', []))));
        }
    }
}
