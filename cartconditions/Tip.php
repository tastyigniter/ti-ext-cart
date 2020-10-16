<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Cart\Models\CartSettings;
use Igniter\Flame\Cart\CartCondition;

class Tip extends CartCondition
{
    protected $tippingEnabled = FALSE;

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
            return FALSE;

        // if amount is not set, empty or 0
        if (!$tipAmount = $this->getMetaData('amount'))
            return FALSE;

        $value = $this->getMetaData('amount');
        if (preg_match('/^\d+([\.\d]{2})?([%])?$/', $value) === FALSE) {
            $this->removeMetaData('amount');
            flash()->warning(lang('igniter.cart::default.alert_tip_not_applied'))->now();
        }
    }

    public function getActions()
    {
        $amountType = $this->getMetaData('amountType');
        $amount = $this->getMetaData('amount');
        if ($amountType == 'amount' AND $this->tipValueType != 'F')
            $amount .= '%';

        return [
            ['value' => "+{$amount}"],
        ];
    }
}
