<?php

namespace Igniter\Cart\Models\Scopes;

use Igniter\Flame\Database\Scope;
use Illuminate\Database\Eloquent\Builder;

class CategoryScope extends Scope
{
    public function addWhereHasMenus()
    {
        return function(Builder $builder) {
            return $builder->whereHas('menus')->where('status', 1);
        };
    }
}
