<?php

declare(strict_types=1);

namespace Igniter\Cart\Models\Observers;

use Igniter\Cart\Models\MenuItemOption;

class MenuItemOptionObserver
{
    public function saved(MenuItemOption $menuItemOption): void
    {
        $menuItemOption->restorePurgedValues();

        if (array_key_exists('menu_option_values', $attributes = $menuItemOption->getAttributes())) {
            $menuItemOption->addMenuOptionValues(array_filter(array_map(function(array $value): array|false {
                if (array_get($value, 'is_enabled')) {
                    unset($value['is_enabled']);

                    return $value;
                }

                return false;
            }, array_get($attributes, 'menu_option_values', []))));
        }
    }
}
