<?php

namespace Igniter\Automation\Models;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Validation;
use Igniter\Flame\Exception\SystemException;

class RuleAction extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rule_actions';

    public $timestamps = true;

    protected $guarded = [];

    public $relation = [
        'belongsTo' => [
            'automation_rule' => [AutomationRule::class, 'key' => 'automation_rule_id'],
        ],
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public $rules = [
        'class_name' => 'required',
    ];

    //
    // Attributes
    //

    public function getNameAttribute()
    {
        return $this->getActionObject()->getActionName();
    }

    public function getDescriptionAttribute()
    {
        return $this->getActionObject()->getActionDescription();
    }

    //
    // Events
    //

    protected function afterFetch()
    {
        $this->applyActionClass();
        $this->loadCustomData();
    }

    protected function beforeSave()
    {
        $this->setCustomData();
    }

    public function applyCustomData()
    {
        $this->setCustomData();
        $this->loadCustomData();
    }

    /**
     * Extends this model with the action class
     * @param string $class Class name
     * @return bool
     */
    public function applyActionClass($class = null)
    {
        if (!$class) {
            $class = $this->class_name;
        }

        if (!$class) {
            return false;
        }

        if (!$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->class_name = $class;

        return true;
    }

    /**
     * @return \Igniter\Automation\Classes\BaseAction
     */
    public function getActionObject()
    {
        $this->applyActionClass();

        return $this->asExtension($this->getActionClass());
    }

    public function getActionClass()
    {
        return $this->class_name;
    }

    protected function loadCustomData()
    {
        $this->setRawAttributes((array)$this->getAttributes() + (array)$this->options, true);
    }

    protected function setCustomData()
    {
        if (!$actionObj = $this->getActionObject()) {
            throw new SystemException(sprintf('Unable to find action object [%s]', $this->getActionClass()));
        }

        $config = $actionObj->getFieldConfig();
        if (!$fields = array_get($config, 'fields')) {
            return;
        }

        $fieldAttributes = array_keys($fields);
        $this->options = array_only($this->getAttributes(), $fieldAttributes);
        $this->setRawAttributes(array_except($this->getAttributes(), $fieldAttributes));
    }
}
