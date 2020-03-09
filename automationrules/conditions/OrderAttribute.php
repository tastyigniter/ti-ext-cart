<?php namespace Igniter\Cart\AutomationRules\Conditions;

use ApplicationException;
use Igniter\Automation\Classes\BaseModelAttributesCondition;

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
        ];
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
