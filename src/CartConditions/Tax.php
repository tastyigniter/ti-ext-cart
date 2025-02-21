<?php

declare(strict_types=1);

namespace Igniter\Cart\CartConditions;

use Igniter\Cart\CartCondition;
use Igniter\Local\Facades\Location;
use Igniter\System\Models\Currency;

class Tax extends CartCondition
{
    protected $taxMode;

    protected $taxInclusive;

    protected $taxRate;

    protected $taxRateLabel;

    public ?int $priority = 300;

    protected $taxDelivery;

    public function getLabel(): string
    {
        $label = $this->taxInclusive ? "{$this->taxRateLabel}% ".lang('igniter.cart::default.text_vat_included') : "{$this->taxRateLabel}%";

        return sprintf(lang($this->label), $label);
    }

    public function onLoad(): void
    {
        $this->taxMode = (bool)setting('tax_mode', 1);
        $this->taxInclusive = !((bool)setting('tax_menu_price', 1));
        $this->taxRate = $this->taxRateLabel = (int)setting('tax_percentage', 0);
        if ($this->taxInclusive) {
            $this->taxRate /= (100 + $this->taxRate) / 100;
        }

        $this->taxDelivery = (bool)setting('tax_delivery_charge', 0);
    }

    public function beforeApply(): ?bool
    {
        // only calculate taxes if enabled
        return $this->taxMode && $this->taxRate;
    }

    public function getActions(): array
    {
        $precision = optional(Currency::getDefault())->decimal_position ?? 2;

        return [
            [
                'value' => "+{$this->taxRate}%",
                'inclusive' => $this->taxInclusive,
                'valuePrecision' => (int)$precision,
            ],
        ];
    }

    public function calculate($subTotal)
    {
        $excludeDeliveryCharge = Location::orderTypeIsDelivery() && !$this->taxDelivery;
        if ($excludeDeliveryCharge) {
            $deliveryCharge = Location::coveredArea()->deliveryAmount($subTotal);
            $subTotal -= (float)$deliveryCharge;
        }

        $result = parent::calculate($subTotal);

        if ($excludeDeliveryCharge) {
            $result += (float)$deliveryCharge;
        }

        return $result;
    }
}
