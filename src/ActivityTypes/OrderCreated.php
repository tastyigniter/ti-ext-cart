<?php

namespace Igniter\Cart\ActivityTypes;

use Igniter\Admin\Models\Order;
use Igniter\Admin\Models\User;
use Igniter\Flame\ActivityLog\Contracts\ActivityInterface;
use Igniter\Flame\ActivityLog\Models\Activity;

class OrderCreated implements ActivityInterface
{
    public $type;

    public $subject;

    public function __construct(string $type, Order $subject)
    {
        $this->type = $type;
        $this->subject = $subject;
    }

    public static function log($order)
    {
        $recipients = User::isEnabled()
            ->whereHasLocation($order->location->getKey())
            ->get()->all();

        activity()->pushLog(new static('orderCreated', $order), $recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getCauser()
    {
        return $this->subject->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return [
            'order_id' => $this->subject->getKey(),
            'full_name' => $this->subject->customer_name,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel()
    {
        return Order::class;
    }

    public static function getTitle(Activity $activity)
    {
        return lang('igniter.cart::default.checkout.activity_order_created_title');
    }

    public static function getUrl(Activity $activity)
    {
        $url = 'orders';
        if ($activity->subject)
            $url .= '/edit/'.$activity->subject->getKey();

        return admin_url($url);
    }

    public static function getMessage(Activity $activity)
    {
        return lang('igniter.cart::default.checkout.activity_order_created');
    }
}
