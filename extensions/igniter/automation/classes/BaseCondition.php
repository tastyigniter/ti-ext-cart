<?php

namespace Igniter\Automation\Classes;

class BaseCondition extends AbstractBase
{
    /**
     * @var \Igniter\Flame\Database\Model model object
     */
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;

        $this->initialize($model);
    }

    /**
     * Initialize method called when the action class is first loaded
     * with an existing model.
     * @return void
     */
    public function initialize($model)
    {
        if (!$model) {
            return;
        }

        if (!$model->exists) {
            $this->initConfigData($model);
        }
    }

    /**
     * Initializes configuration data when the action is first created.
     * @param \Igniter\Flame\Database\Model $model
     */
    public function initConfigData($model)
    {
    }

    /**
     * Returns information about this condition, including name and description.
     */
    public function conditionDetails()
    {
        return [
            'name' => 'Condition',
            'description' => 'Condition description',
        ];
    }

    public function getConditionName()
    {
        return array_get($this->conditionDetails(), 'name', 'Condition');
    }

    public function getConditionDescription()
    {
        return array_get($this->conditionDetails(), 'Condition description');
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params
     * @return bool
     */
    public function isTrue(&$params)
    {
        return false;
    }

    public static function findConditions()
    {
        $results = [];
        $ruleConditions = (array)BaseEvent::findRulesValues('conditions');
        foreach ($ruleConditions as $conditionClass) {
            if (!class_exists($conditionClass)) {
                continue;
            }

            $conditionObj = new $conditionClass;
            $results[$conditionClass] = $conditionObj;
        }

        return $results;
    }
}
