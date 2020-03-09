<?php namespace Igniter\Cart\AutomationRules\Conditions;

use ApplicationException;
use Igniter\Automation\Classes\BaseModelAttributesCondition;

class OrderStatusAttribute extends BaseModelAttributesCondition
{
    protected $modelClass = \Admin\Models\Statuses_model::class;

    protected $modelAttributes;

    public function conditionDetails()
    {
        return [
            'name' => 'Order status attribute',
            'description' => 'Order status attributes',
        ];
    }

    public function defineModelAttributes()
    {
        return [
            'status_id' => [
                'label' => 'Status ID',
            ],
            'status_name' => [
                'label' => 'Status Name',
            ],
            'notify_customer' => [
                'label' => 'Notify Customer',
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
        if (!$status = array_get($params, 'status')) {
            throw new ApplicationException('Error evaluating the status attribute condition: the status object is not found in the condition parameters.');
        }

        return $this->evalIsTrue($status);
    }
}
