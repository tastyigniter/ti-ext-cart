<?php

namespace Igniter\Cart\AutomationRules\Conditions;

use Carbon\Carbon;
use Igniter\Automation\Classes\BaseModelAttributesCondition;
use Igniter\Flame\Exception\ApplicationException;

class OrderAttribute extends BaseModelAttributesCondition
{
    protected $modelClass = \Admin\Models\Orders_model::class;

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
        ];
    }

    public function getHoursSinceAttribute($value, $order)
    {
        $currentDateTime = Carbon::now();
        $orderDateTime = Carbon::parse($order->order_date->format('Y-m-d').' '.$order->order_time);

        return $currentDateTime->isAfter($orderDateTime)
            ? $orderDateTime->diffInRealHours($currentDateTime)
            : 0;
    }

    public function getHoursUntilAttribute($value, $order)
    {
        $currentDateTime = Carbon::now();
        $orderDateTime = Carbon::parse($order->order_date->format('Y-m-d').' '.$order->order_time);

        return $currentDateTime->isBefore($orderDateTime)
            ? $currentDateTime->diffInRealHours($orderDateTime)
            : 0;
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params Specifies a list of parameters as an associative array.
     * @return bool
     */
    public function isTrue(&$params)
    {
        if (!$order = array_get($params, 'order')) {
            throw new ApplicationException('Error evaluating the order attribute condition: the order object is not found in the condition parameters.');
        }

        return $this->evalIsTrue($order);
    }
}
