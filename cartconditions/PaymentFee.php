<?php

namespace Igniter\Cart\CartConditions;

use Admin\Models\Payments_model;
use Igniter\Flame\Cart\CartCondition;

class PaymentFee extends CartCondition
{
    protected $paymentModel;

    public $priority = 600;

    public function getLabel()
    {
        $paymentFeeType = (int)optional($this->paymentModel)->order_fee_type;
        $paymentFee = optional($this->paymentModel)->order_fee;

        return $paymentFeeType === 2
            ? lang($this->label)." [$paymentFee%]"
            : lang($this->label);
    }

    public function beforeApply()
    {
        if (!strlen($paymentCode = $this->getMetaData('code')))
            return FALSE;

        if (is_null($this->paymentModel))
            $this->paymentModel = Payments_model::whereCode($paymentCode)->first();

        // only apply if payment has applicable fee
        $paymentFee = optional($this->paymentModel)->order_fee;
        if (!($paymentFee > 0))
            return FALSE;
    }

    public function getActions()
    {
        $paymentFeeType = (int)optional($this->paymentModel)->order_fee_type;
        $paymentFee = optional($this->paymentModel)->order_fee ?? 0;

        return [
            ['value' => "+{$paymentFee}".($paymentFeeType === 2 ? '%' : '')],
        ];
    }
}