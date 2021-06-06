<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;

class Tax extends CartCondition
{
    protected $taxMode;

    protected $taxInclusive;

    protected $taxRate;

    protected $taxRateLabel;

    public $priority = 300;

    protected $taxDelivery;

    public function getLabel()
    {
        $label = $this->taxInclusive ? "{$this->taxRateLabel}% included" : "{$this->taxRateLabel}%";

        return sprintf(lang($this->label), $label);
    }

    public function onLoad()
    {
        $this->taxMode = (bool)setting('tax_mode', 1);
        $this->taxInclusive = !((bool)setting('tax_menu_price', 1));
        $this->taxRate = $this->taxRateLabel = setting('tax_percentage', 0);
        if ($this->taxInclusive)
            $this->taxRate /= (100 + $this->taxRate) / 100;
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

    public function calculate($total)
    {
        if (Location::orderTypeIsDelivery() AND !$this->taxDelivery) {
            $deliveryCharge = Location::coveredArea()->deliveryAmount($total);
            $total -= (float)$deliveryCharge;
        }

        return parent::calculate($total);
    }
    
    protected function processActionValue($action, $total)
    {
        $action = parent::processActionValue($action, $total);

        $precision = app('currency')->getDefault() ? app('currency')->getDefault()->decimal_position : 2;
        $this->calculatedValue += round($this->calculatedValue, $precision);

        return $action;        
    }
}
