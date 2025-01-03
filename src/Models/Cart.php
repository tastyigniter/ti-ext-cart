<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Model;

/**
 * Cart Model Class
 *
 * @property string $identifier
 * @property string $instance
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Igniter\Flame\Database\Model
 */
class Cart extends Model
{
    protected $table = 'igniter_cart_cart';

    protected static $unguarded = true;

    protected $primaryKey = 'identifier';

    public $incrementing = false;

    public $timestamps = true;
}
