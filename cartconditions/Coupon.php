<?php

namespace Igniter\Cart\CartConditions;

use Admin\Models\Menus_model;
use ApplicationException;
use Auth;
use Exception;
use Igniter\Cart\Models\Coupons_model;
use Igniter\Flame\Cart\CartCondition;
use Location;

class Coupon extends CartCondition
{
    public $removeable = TRUE;

    public $priority = 200;

    protected $couponCode;

    /**
     * @var Coupons_model
     */
    protected $couponModel;

    public function getLabel()
    {
        return sprintf(lang($this->label), $this->getMetaData('code'));
    }

    public function getValue()
    {
        return 0 - $this->calculatedValue;
    }

    public function getModel()
    {
        return $this->couponModel;
    }

    public function beforeApply()
    {
        if (!strlen($couponCode = $this->getMetaData('code')))
            return FALSE;

        if (is_null($this->couponModel))
            $this->couponModel = Coupons_model::getByCode($couponCode);

        try {
            if (!$this->couponModel)
                throw new ApplicationException(lang('igniter.cart::default.alert_coupon_invalid'));

            $this->validateCoupon();
        }
        catch (Exception $ex) {
            flash()->alert($ex->getMessage())->now();
            $this->removeMetaData('code');

            return FALSE;
        }
    }

    public function getActions()
    {
        return [
            [
                'value' => $this->couponModel->discountWithOperand(), 
                'calculateValue' => [$this, 'calculateValue'],
            ],
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

    protected function validateCoupon()
    {
        $user = Auth::getUser();
        $locationId = Location::getId();
        $orderType = Location::orderType();

        if ($this->couponModel->isExpired())
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_expired'));

        if ($this->couponModel->hasRestriction($orderType))
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_coupon_order_restriction'), $orderType
            ));

        if ($this->couponModel->hasLocationRestriction($locationId))
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_location_restricted'));

        if ($this->couponModel->hasReachedMaxRedemption())
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_maximum_reached'));

        if ($user AND $this->couponModel->customerHasMaxRedemption($user))
            throw new ApplicationException(lang('igniter.cart::default.alert_coupon_maximum_reached'));
    }

    protected function calculateValue($condition, $actionValue)
    {
        $cartContent = $this->getCartContent(); 
        $limitToMenus = $this->couponModel->menus->pluck('menu_id');
        $limitToCategories = $this->couponModel->categories->pluck('category_id');
        
        if (count($limitToMenus) OR count($limitToCategories))
        {
            $total = 0;

            foreach ($limitToMenus as $menuId){
                foreach ($cartContent as $cartRow){
                    if ($cartRow->id == $menuId)
                        $total += $cartRow->subtotal();
                }
            }

            foreach ($cartContent as $cartRow){
                $menu = Menus_model::with(['categories'])->where('menu_id', $cartRow->id)->first();
                if ($menu AND $menu->categories->pluck('category_id')->intersect($limitToCategories)->count() > 0)
                    $total += $cartRow->subtotal();
            }
          
            if ($condition->valueIsPercentage($actionValue)) {
                $cleanValue = $condition->cleanValue($actionValue);
                $value = ($total * ($cleanValue / 100));
            }
            else {
                $value = (float)$condition->cleanValue($actionValue);
            }            
            
            return $value;    
        }
    }    
}
