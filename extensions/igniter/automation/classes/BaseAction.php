<?php

namespace Igniter\Automation\Classes;

class BaseAction extends AbstractBase
{
    /**
     * @var \Igniter\Flame\Database\Model model object
     */
    protected $model;

    /**
     * @var mixed Extra field configuration for the action.
     */
    protected $fieldConfig;

    public function __construct($model = null)
    {
        $this->model = $model;

        $this->fieldConfig = $this->defineFormFields();

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

        // Apply validation rules
        $model->rules = array_merge($model->rules, $this->defineValidationRules());
    }

    /**
     * Initializes configuration data when the action is first created.
     * @param \Igniter\Flame\Database\Model $model
     */
    public function initConfigData($model)
    {
    }

    /**
     * Returns information about this action, including name and description.
     */
    public function actionDetails()
    {
        return [
            'name' => 'Action',
            'description' => 'Action description',
        ];
    }

    /**
     * Extra field configuration for the action.
     */
    public function defineFormFields()
    {
        return [];
    }

    /**
     * Defines validation rules for the custom fields.
     * @return array
     */
    public function defineValidationRules()
    {
        return [];
    }

    public function hasFieldConfig()
    {
        return (bool)$this->fieldConfig;
    }

    public function getFieldConfig()
    {
        return $this->fieldConfig;
    }

    public function triggerAction($params)
    {
    }

    public function getActionName()
    {
        return array_get($this->actionDetails(), 'name', 'Action');
    }

    public function getActionDescription()
    {
        return array_get($this->actionDetails(), 'description');
    }

    public static function findActions()
    {
        $results = [];
        $ruleActions = (array)BaseEvent::findRulesValues('actions');
        foreach ($ruleActions as $actionClass) {
            if (!class_exists($actionClass)) {
                continue;
            }

            $actionObj = new $actionClass;
            $results[$actionClass] = $actionObj;
        }

        return $results;
    }
}
