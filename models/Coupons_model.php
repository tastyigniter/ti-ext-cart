<?php namespace Igniter\Cart\Models;

class Coupons_model extends \Admin\Models\Coupons_model
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