<?php

declare(strict_types=1);

namespace Igniter\Cart\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Cart\Models\Order;

class NewOrderStatus extends BaseEvent
{
    public function eventDetails(): array
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
