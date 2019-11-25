<?php

namespace Igniter\Cart\Classes;

use Igniter\Flame\Traits\Singleton;
use System\Classes\ExtensionManager;

class CartConditionManager
{
    use Singleton;

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
        if (!class_exists($className))
            return;

        return new $className($config);
    }

    public function listRegisteredConditions()
    {
        if ($this->registeredConditions === null) {
            $this->loadRegisteredConditions();
        }

        if (!is_array($this->registeredConditions)) {
            return [];
        }

        return $this->registeredConditions;
    }

    public function loadRegisteredConditions()
    {
        foreach ($this->registeredCallbacks as $callback) {
            $callback($this);
        }

        // Load extensions cart conditions
        $registeredConditions = ExtensionManager::instance()->getRegistrationMethodValues('registerCartConditions');
        foreach ($registeredConditions as $extensionCode => $cartConditions) {
            $this->registerConditions($cartConditions);
        }
    }

    public function registerConditions(array $conditions)
    {
        if ($this->registeredConditions === null)
            $this->registeredConditions = [];

        foreach ($conditions as $className => $condition) {
            $this->registerCondition($className, $condition);
        }
    }

    public function registerCondition($className, $conditionInfo = null)
    {
        if ($this->registeredConditions === null)
            $this->registeredConditions = [];

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