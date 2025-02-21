<?php

declare(strict_types=1);

namespace Igniter\Cart\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Cart\Models\Order;

class OrderPlaced extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Placed Event',
            'description' => 'When an order is placed (after successful payment)',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $order = array_get($args, 0);
        if ($order instanceof Order) {
            $params = $order->mailGetData();
        }

        $params['status'] = $order->status ?? null;

        return $params;
    }
}
