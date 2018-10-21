<?php

namespace Igniter\Cart\EventRules\Events;

use Igniter\EventRules\Classes\BaseEvent;

class OrderPlaced extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Placed Event',
            'description' => 'An order is placed (after successful payment)',
            'group' => 'order'
        ];
    }

}