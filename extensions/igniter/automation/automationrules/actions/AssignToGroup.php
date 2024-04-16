<?php

namespace Igniter\Automation\AutomationRules\Actions;

use Admin\ActivityTypes\AssigneeUpdated;
use Admin\Models\Staff_groups_model;
use Admin\Traits\Assignable;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Flame\Exception\ApplicationException;

class AssignToGroup extends BaseAction
{
    public function actionDetails()
    {
        return [
            'name' => 'Assign to staff group',
            'description' => 'Automatically assign an order/reservation to a staff group',
        ];
    }

    public function defineFormFields()
    {
        return [
            'fields' => [
                'staff_group_id' => [
                    'label' => 'lang:igniter.automation::default.label_assign_to_staff_group',
                    'type' => 'select',
                    'options' => [\Admin\Models\Staff_groups_model::class, 'getDropdownOptions'],
                ],
            ],
        ];
    }

    public function triggerAction($params)
    {
        if (!$groupId = $this->model->staff_group_id) {
            throw new ApplicationException('Missing valid staff group to assign to.');
        }

        if (!$assigneeGroup = Staff_groups_model::find($groupId)) {
            throw new ApplicationException('Invalid staff group to assign to.');
        }

        $assignable = array_get($params, 'order', array_get($params, 'reservation'));
        if (!in_array(Assignable::class, class_uses_recursive(get_class($assignable)))) {
            throw new ApplicationException('Missing assignable model.');
        }

        $log = $assignable->assignToGroup($assigneeGroup);

        AssigneeUpdated::log($log);
    }
}
