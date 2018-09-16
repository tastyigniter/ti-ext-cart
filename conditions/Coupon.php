<?php

namespace Igniter\Cart\Conditions;

use ApplicationException;
use Auth;
use Exception;
use Igniter\Cart\Models\Coupons_model;
use Igniter\Flame\Cart\CartCondition;
use Location;

class Coupon extends CartCondition
{
    public $removeable = TRUE;

    protected $couponCode;

    /**
     * @var Coupons_model
     */
    protected $couponModel;

    public function getLabel()
    {
        return sprintf(lang($this->label), $this->getMetaData('code'));
    }

    public function getModel()
    {
        return $this->couponModel;
    }

    public function onLoad()
    {
        $this->couponCode = $this->getMetaData('code');
        $this->couponModel = Coupons_model::getByCode($this->couponCode);
    }

    public function beforeApply()
    {
        if (!strlen($this->couponCode))
            return FALSE;

        try {
            if (!$this->couponModel)
                throw new ApplicationException(lang('igniter.cart::default.alert_coupon_invalid'));

            $this->couponModel->validateCoupon(Location::orderType(), Auth::getUser());
        }
        catch (Exception $ex) {
            flash()->alert($ex->getMessage())->now();

            return FALSE;
        }
    }

    public function getActions()
    {
        return [
            ['value' => $this->couponModel->discountWithOperand()],
        ];
    }

    public function getRules()
    {
        $minimumOrder = $this->couponModel->minimumOrderTotal();

        return ["subtotal > {$minimumOrder}"];
    }

    public function whenInvalid()
    {
        $minimumOrder = $this->couponModel->minimumOrderTotal();
        flash()->warning(sprintf(
            lang('igniter.cart::default.alert_coupon_not_applied'),
            currency_format($minimumOrder)
        ))->now();

        $this->removeMetaData('code');
    }
}