<?php

declare(strict_types=1);

namespace Igniter\Cart\Models\Observers;

use Igniter\Cart\Models\MenuOption;

class MenuOptionObserver
{
    public function saved(MenuOption $menuOption): void
    {
        $menuOption->restorePurgedValues();

        if (array_key_exists('values', $attributes = $menuOption->getAttributes())) {
            $menuOption->addOptionValues(
                array_get($attributes, 'values', []),
                (bool)array_get($attributes, 'update_menu_prices', false),
            );
        }
    }

    public function deleting(MenuOption $menuOption): void
    {
        $menuOption->locations()->detach();
    }
}
