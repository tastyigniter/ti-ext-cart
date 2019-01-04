<?php

namespace Igniter\Cart\Conditions;

use Igniter\Flame\Cart\CartCondition;

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
}