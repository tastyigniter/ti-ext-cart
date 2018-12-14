<?php

namespace Igniter\Cart\EventRules\Events;

use Admin\Models\Orders_model;
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

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $order = array_get($args, 0);
        if ($order instanceof Orders_model)
            $params = $order->mailGetData();

        $params['order'] = $order;

        return $params;
    }
}