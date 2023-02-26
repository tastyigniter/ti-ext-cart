<?php

namespace Igniter\Cart\Notifications;

use Igniter\Admin\Models\User;
use Igniter\System\Classes\Notification;

class OrderCreatedNotification extends Notification
{
    public function getRecipients(): array
    {
        return User::isEnabled()
            ->whereHasLocation($this->subject->location->getKey())
            ->get()->all();
    }

    public function getTitle(): string
    {
        return lang('igniter.cart::default.checkout.notify_order_created_title');
    }

    public function getUrl(): string
    {
        $url = 'orders';
        if ($this->subject)
            $url .= '/edit/'.$this->subject->getKey();

        return admin_url($url);
    }

    public function getMessage(): string
    {
        return sprintf(lang('igniter.cart::default.checkout.notify_order_created'), $this->subject->customer_name);
    }
}
