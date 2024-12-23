<?php

namespace Igniter\Cart\Classes;

use Igniter\System\Classes\ExtensionManager;

class CartConditionManager
{
    /**
     * @var array An array of registered conditions.
     */
    protected $registeredConditions;

    protected $registeredConditionHints = [];

    /**
     * @var array Cache of cart conditions registration callbacks.
     */
    protected $registeredCallbacks = [];

    public function makeCondition($className, array $config = [])
    {
        if (!array_key_exists($className, $this->registeredConditions ?? [])) {
            throw new \LogicException(sprintf("The Cart Condition class '%s' has not been registered", $className));
        }

        if (!class_exists($className)) {
            throw new \LogicException(sprintf("The Cart Condition class '%s' does not exist", $className));
        }

        return new $className(array_merge($this->registeredConditions[$className], $config));
    }

    public function listRegisteredConditions()
    {
        if ($this->registeredConditions === null) {
            $this->loadRegisteredConditions();
        }

        return $this->registeredConditions;
    }

    public function loadRegisteredConditions()
    {
        if (is_null($this->registeredConditions)) {
            $this->registeredConditions = [];
        }

        foreach ($this->registeredCallbacks as $callback) {
            $callback($this);
        }

        // Load extensions cart conditions
        $registeredConditions = resolve(ExtensionManager::class)->getRegistrationMethodValues('registerCartConditions');
        foreach ($registeredConditions as $cartConditions) {
            $this->registerConditions($cartConditions);
        }
    }

    public function registerConditions(array $conditions)
    {
        if ($this->registeredConditions === null) {
            $this->registeredConditions = [];
        }

        foreach ($conditions as $className => $condition) {
            $this->registerCondition($className, $condition);
        }
    }

    public function registerCondition($className, $conditionInfo = null)
    {
        if ($this->registeredConditions === null) {
            $this->registeredConditions = [];
        }

        $defaults = [
            'name' => 'default',
            'label' => '',
            'description' => '',
        ];

        $condition = array_merge($defaults, $conditionInfo);
        $conditionName = array_get($condition, 'name');
        $condition['className'] = $className;

        $this->registeredConditions[$className] = $condition;
        $this->registeredConditionHints[$conditionName] = $className;
    }

    public function registerCallback(callable $definitions)
    {
        $this->registeredCallbacks[] = $definitions;
    }
}
