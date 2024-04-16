<?php

namespace Igniter\Automation\Models;

use Igniter\Flame\Database\Model;

class RuleCondition extends Model
{
    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rule_conditions';

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
        return $this->getConditionObject()->getConditionName();
    }

    public function getDescriptionAttribute()
    {
        return $this->getConditionObject()->getConditionDescription();
    }

    //
    // Events
    //

    protected function afterFetch()
    {
        $this->applyConditionClass();
    }

    /**
     * Extends this model with the condition class
     * @param string $class Class name
     * @return bool
     */
    public function applyConditionClass($class = null)
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
     * @return \Igniter\Automation\Classes\BaseCondition
     */
    public function getConditionObject()
    {
        $this->applyConditionClass();

        return $this->asExtension($this->getConditionClass());
    }

    public function getConditionClass()
    {
        return $this->class_name;
    }
}
