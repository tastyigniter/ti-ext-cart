<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;
use System\Models\Currencies_model;

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
        $label = $this->taxInclusive ? "{$this->taxRateLabel}% ".lang('igniter.cart::default.text_vat_included') : "{$this->taxRateLabel}%";

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
        if (!$this->taxMode || !$this->taxRate)
            return false;
    }

    public function getActions()
    {
        $precision = optional(Currencies_model::getDefault())->decimal_position ?? 2;

        return [
            [
                'value' => "+{$this->taxRate}%",
                'inclusive' => $this->taxInclusive,
                'valuePrecision' => $precision,
            ],
        ];
    }

    public function calculate($total)
    {
        $excludeDeliveryCharge = Location::orderTypeIsDelivery() && !$this->taxDelivery;
        if ($excludeDeliveryCharge) {
            $deliveryCharge = Location::coveredArea()->deliveryAmount($total);
            $total -= (float)$deliveryCharge;
        }

        $result = parent::calculate($total);

        if ($excludeDeliveryCharge) {
            $result += (float)$deliveryCharge;
        }

        return $result;
    }
}
