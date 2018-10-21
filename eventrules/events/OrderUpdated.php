<?php

namespace Igniter\Cart\EventRules\Events;

use Igniter\EventRules\Classes\BaseEvent;

class OrderUpdated extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Updated Event',
            'description' => 'An order is updated',
            'group' => 'order'
        ];
    }

}