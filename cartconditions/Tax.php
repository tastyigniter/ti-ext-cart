<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;

class Tax extends CartCondition
{
    protected $taxMode;

    protected $taxInclusive;

    protected $taxRate;

    public $priority = 300;

    public function getLabel()
    {
        $label = $this->taxInclusive ? "{$this->taxRate}% included" : "{$this->taxRate}%";

        return sprintf(lang($this->label), $label);
    }

    public function onLoad()
    {
        $this->taxMode = (bool)setting('tax_mode', 1);
        $this->taxInclusive = !((bool)setting('tax_menu_price', 1));
        $this->taxRate = setting('tax_percentage', 0);
        $this->taxDelivery = (bool)setting('tax_delivery_charge', 0);
    }

    public function beforeApply()
    {
        // only calculate taxes if enabled
        if (!$this->taxMode OR !$this->taxRate)
            return FALSE;
    }

    public function getActions()
    {
        return [
            [
                'value' => "+{$this->taxRate}%",
                'inclusive' => $this->taxInclusive,
            ],
        ];
    }

    protected function processValue($total)
    {
        if ($this->taxDelivery) {
            $deliveryCharge = Location::coveredArea()->deliveryAmount($total);
            $total += (float)$deliveryCharge;
        }

        parent::processValue($total);
    }
}