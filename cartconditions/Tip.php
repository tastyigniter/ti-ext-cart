<?php

namespace Igniter\Cart\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;

class Tip extends CartCondition
{
    public $priority = 100;
    public $removeable = TRUE;

    public function getLabel()
    {
        return lang($this->label);
    }
    
    public function getValue()
    {
        return $this->getMetaData('amount');
    }

    public function beforeApply()
    {
	    $value = $this->getMetaData('amount');
	    if (preg_match('/^\d+([\.\d]{2})?$/', $value) === false) {
		    $this->removeMetaData('amount');
	        flash()->warning(lang('igniter.cart::default.alert_tip_not_applied'))->now();		    
	    }
    }
}