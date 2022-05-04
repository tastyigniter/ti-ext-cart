<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Cart\Models\CartSettings;
use Igniter\Flame\Cart\CartCondition;
use System\Models\Currencies_model;

class Tip extends CartCondition
{
    protected $tippingEnabled = false;

    protected $tipValueType;

    public $priority = 100;

    public function onLoad()
    {
        $this->tippingEnabled = (bool)CartSettings::get('enable_tipping');
        $this->tipValueType = CartSettings::get('tip_value_type', 'F');
    }

    public function getLabel()
    {
        return lang($this->label);
    }

    public function beforeApply()
    {
        if (!$this->tippingEnabled)
            return false;

        // if amount is not set, empty or 0
        if (!$tipAmount = $this->getMetaData('amount'))
            return false;

        $value = $this->getMetaData('amount');
        if (preg_match('/^\d+([\.\d]{2})?([%])?$/', $value) === false || $value < 0) {
            $this->removeMetaData('amount');
            flash()->warning(lang('igniter.cart::default.alert_tip_not_applied'))->now();
        }
    }

    public function getActions()
    {
        $amountType = $this->getMetaData('amountType');
        $amount = $this->getMetaData('amount');
        if ($amountType == 'amount' && $this->tipValueType != 'F')
            $amount .= '%';

        $precision = optional(Currencies_model::getDefault())->decimal_position ?? 2;

        return [
            ['value' => "+{$amount}", 'valuePrecision' => $precision],
        ];
    }
}
