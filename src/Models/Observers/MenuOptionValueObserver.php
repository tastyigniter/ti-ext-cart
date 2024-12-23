<?php

namespace Igniter\Cart\Models\Observers;

use Igniter\Cart\Models\MenuOptionValue;

class MenuOptionValueObserver
{
    public function deleting(MenuOptionValue $menuOptionValue)
    {
        $menuOptionValue->ingredients()->detach();
    }
}
