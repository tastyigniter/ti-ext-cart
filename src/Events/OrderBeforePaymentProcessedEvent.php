<?php

namespace Igniter\Cart\Events;

use Igniter\Cart\Models\Order;
use Igniter\Flame\Traits\EventDispatchable;

class OrderBeforePaymentProcessedEvent
{
    use EventDispatchable;

    public function __construct(public Order $order) {}

    public static function eventName()
    {
        return 'admin.order.beforePaymentProcessed';
    }
}
