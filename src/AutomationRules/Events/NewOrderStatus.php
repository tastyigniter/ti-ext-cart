<?php

namespace Igniter\Cart\AutomationRules\Events;

use Igniter\Admin\Models\Order;
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
        $order = array_get($args, 0);
        $status = array_get($args, 1);
        if ($order instanceof Order) {
            $params = $order->mailGetData();
        }

        $params['status'] = $status;

        return $params;
    }
}
