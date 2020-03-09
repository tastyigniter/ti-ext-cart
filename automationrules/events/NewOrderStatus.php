<?php

namespace Igniter\Cart\AutomationRules\Events;

use Admin\Models\Orders_model;
use Igniter\Automation\Classes\BaseEvent;

class NewOrderStatus extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Status Update Event',
            'description' => 'When an order status is updated',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $status = array_get($args, 0);
        $order = array_get($args, 1);
        if ($order instanceof Orders_model)
            $params = $order->mailGetData();

        $status->save();
        $params['order'] = $order;
        $params['status'] = $status;

        return $params;
    }
}