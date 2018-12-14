<?php

namespace Igniter\Cart\EventRules\Events;

use Admin\Models\Orders_model;
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