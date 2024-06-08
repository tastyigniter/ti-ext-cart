<?php

namespace Igniter\Cart\AutomationRules\Conditions;

use Igniter\Automation\AutomationException;
use Igniter\Automation\Classes\BaseModelAttributesCondition;

class OrderAttribute extends BaseModelAttributesCondition
{
    protected $modelClass = \Igniter\Cart\Models\Order::class;

    protected $modelAttributes;

    public function conditionDetails()
    {
        return [
            'name' => 'Order attribute',
            'description' => 'Order attributes',
        ];
    }

    public function defineModelAttributes()
    {
        return [
            'first_name' => [
                'label' => 'First Name',
            ],
            'last_name' => [
                'label' => 'Last Name',
            ],
            'email' => [
                'label' => 'Email address',
            ],
            'location_id' => [
                'label' => 'Location ID',
            ],
            'status_id' => [
                'label' => 'Last order status ID',
            ],
            'total_items' => [
                'label' => 'Cart total items',
            ],
            'order_type' => [
                'label' => 'Order type (eg. delivery or collection)',
            ],
            'payment' => [
                'label' => 'Payment Code (eg. cod or stripe)',
            ],
            'hours_since' => [
                'label' => 'Hours since order delivery/collection time',
            ],
            'hours_until' => [
                'label' => 'Hours until order delivery/collection time',
            ],
            'days_since' => [
                'label' => 'Days since order delivery/collection time',
            ],
            'days_until' => [
                'label' => 'Days until order delivery/collection time',
            ],
            'history_status_id' => [
                'label' => 'Recent order status IDs (eg. 1,2,3)',
            ],
        ];
    }

    public function getHoursSinceAttribute($value, $order)
    {
        $currentDateTime = now();

        return $currentDateTime->isAfter($order->order_datetime)
            ? floor($order->order_datetime->diffInUTCHour($currentDateTime))
            : 0;
    }

    public function getHoursUntilAttribute($value, $order)
    {
        $currentDateTime = now();

        return $currentDateTime->isBefore($order->order_datetime)
            ? floor($currentDateTime->diffInUTCHour($order->order_datetime))
            : 0;
    }

    public function getDaysSinceAttribute($value, $order)
    {
        $currentDateTime = now();

        return $currentDateTime->isAfter($order->order_datetime)
            ? floor($order->order_datetime->diffInDays($currentDateTime))
            : 0;
    }

    public function getDaysUntilAttribute($value, $order)
    {
        $currentDateTime = now();

        return $currentDateTime->isBefore($order->order_datetime)
            ? floor($currentDateTime->diffInDays($order->order_datetime))
            : 0;
    }

    public function getHistoryStatusIdAttribute($value, $order)
    {
        return $order->status_history()->pluck('status_id')->implode(',');
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params Specifies a list of parameters as an associative array.
     * @return bool
     */
    public function isTrue(&$params)
    {
        if (!$order = array_get($params, 'order')) {
            throw new AutomationException('Error evaluating the order attribute condition: the order object is not found in the condition parameters.');
        }

        return $this->evalIsTrue($order);
    }
}
