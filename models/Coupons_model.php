<?php

namespace Igniter\Cart\Models;

class Coupons_model extends \Igniter\Coupons\Models\Coupons_model
{
    public static function getByCode($code)
    {
        return self::isEnabled()->whereCode($code)->first();
    }

    public function getMorphClass()
    {
        return 'coupons';
    }
}
