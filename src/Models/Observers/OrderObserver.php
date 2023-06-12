<?php

namespace Igniter\Cart\Models\Observers;

use Igniter\Cart\Models\Order;

class OrderObserver
{
    public function creating(Order $order)
    {
        $order->forceFill([
            'hash' => $order->generateHash(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
