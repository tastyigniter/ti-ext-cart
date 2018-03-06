<?php namespace SamPoyigi\Cart\Models;

use ApplicationException;

class Coupons_model extends \Admin\Models\Coupons_model
{
    public static function getByCode($code, $orderType, $user = null)
    {
        $coupon = self::isEnabled()->whereCode($code)->first();

        if (!$coupon)
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_coupon_invalid'));

        if ($coupon->isExpired())
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_coupon_expired'));

        if ($coupon->hasRestriction($orderType))
            throw new ApplicationException(sprintf(
                lang('sampoyigi.cart::default.alert_coupon_order_restriction'), $orderType
            ));

        if (!$coupon->hasReachedMaxRedemption())
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_coupon_maximum_reached'));

        if ($user AND $coupon->customerHasMaxRedemption($user))
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_coupon_maximum_reached'));

        return $coupon;
    }
}