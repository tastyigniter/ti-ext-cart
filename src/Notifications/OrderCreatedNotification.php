<?php

declare(strict_types=1);

namespace Igniter\Cart\Notifications;

use Igniter\Flame\Database\Model;
use Igniter\User\Classes\Notification;
use Igniter\User\Models\User;

class OrderCreatedNotification extends Notification
{
    public function getRecipients(): array
    {
        return User::query()->whereIsEnabled()
            ->whereHasOrDoesntHaveLocation($this->subject->location?->getKey())
            ->get()->all();
    }

    public function getTitle(): string
    {
        return lang('igniter.cart::default.checkout.notify_order_created_title');
    }

    public function getUrl(): string
    {
        $url = 'orders';
        if ($this->subject instanceof Model) {
            $url .= '/edit/'.$this->subject->getKey();
        }

        return admin_url($url);
    }

    public function getMessage(): string
    {
        return sprintf(lang('igniter.cart::default.checkout.notify_order_created'), $this->subject->customer_name);
    }

    public function getIcon(): ?string
    {
        return 'fa-clipboard-list';
    }

    public function getAlias(): string
    {
        return 'order-created';
    }
}
