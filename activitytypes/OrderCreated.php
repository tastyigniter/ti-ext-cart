<?php

namespace Igniter\Cart\ActivityTypes;

use Admin\Models\Orders_model;
use Admin\Models\Staffs_model;
use Auth;
use Igniter\Flame\ActivityLog\Contracts\ActivityInterface;
use Igniter\Flame\ActivityLog\Models\Activity;
use Igniter\Flame\Auth\Models\User;

class OrderCreated implements ActivityInterface
{
    public $order;

    public $user;

    public function __construct(Orders_model $order, User $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    public static function pushActivityLog($model)
    {
        if (!$user = Auth::user())
            return;

        $recipients = Staffs_model::isEnabled()->get()->map(function ($model) {
            return $model->user;
        })->all();

        activity()->pushLog(new static($model, $user), $recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function getCauser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return [
            'order_id' => $this->order->order_id,
            'full_name' => $this->order->customer_name,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return 'orderCreated';
    }

    public static function getUrl(Activity $activity)
    {
        return admin_url('orders/edit/'.$activity->subject->order_id);
    }

    public static function getMessage(Activity $activity)
    {
        return lang('igniter.cart::default.checkout.activity_order_created');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel()
    {
        return Orders_model::class;
    }
}