<?php namespace Igniter\Cart\Models;

use ApplicationException;

class Coupons_model extends \Admin\Models\Coupons_model
{
    public static function getByCode($code)
    {
        return self::isEnabled()->whereCode($code)->first();
    }

    public function validateCoupon($orderType, $user)
    {
        if ($this->isExpired())
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_expired'));

        if ($this->hasRestriction($orderType))
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_coupon_order_restriction'), $orderType
            ));

        if (!$this->hasReachedMaxRedemption())
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_maximum_reached'));

        if ($user AND $this->customerHasMaxRedemption($user))
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_maximum_reached'));
    }
}