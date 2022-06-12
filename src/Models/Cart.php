<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Model;

class Cart extends Model
{
    protected $table = 'igniter_cart_cart';

    protected static $unguarded = true;

    protected $primaryKey = 'identifier';

    public $incrementing = false;

    public $timestamps = true;
}
