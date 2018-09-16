<?php namespace Igniter\Cart\Models;

use Admin\Models\Orders_model as BaseOrders_model;
use Admin\Models\Statuses_model;

class Orders_model extends BaseOrders_model
{
    protected $fillable = ['customer_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'payment'];

    /**
     * Complete order by sending email confirmation and,
     * updating order status
     *
     * @param $status
     *
     * @return bool
     */
    public function completeOrder($status)
    {
        if (!$status instanceof Statuses_model)
            return FALSE;

        $this->status_id = $status->getKey();
        $this->save();

        $this->mailSend('igniter.cart::mail.order', 'customer');
        $this->mailSend('igniter.cart::mail.order_alert', 'location');
        $this->mailSend('igniter.cart::mail.order_alert', 'admin');

        $this->addStatusHistory(['notify' => 1]);
        // @todo: fire order.completed event
    }
}