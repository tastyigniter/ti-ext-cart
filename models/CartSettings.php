<?php namespace Igniter\Cart\Models;

use Model;
use System\Classes\ExtensionManager;

class CartSettings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'igniter_cart_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'cartsettings';

    /**
     * @var array An array of registered conditions.
     */
    protected static $registeredConditions;

    protected static $registeredConditionHints = [];

    /**
     * @var array Cache of cart conditions registration callbacks.
     */
    protected static $registeredConditionsCallbacks = [];

    protected static $conditions;

    public function getConditionsAttribute($value)
    {
        $result = [];
        $registeredConditions = $this->listRegisteredConditions();
        foreach ($registeredConditions as $registeredCondition) {
            $name = array_get($registeredCondition, 'name');
            $dbCondition = $value[$name] ?? [];
            $result[$name] = array_merge($registeredCondition, $dbCondition);
        }

        return $result;
    }

    //
    //
    //

    public function findCondition($name)
    {
        $conditions = $this->listConditions();
        if (empty($conditions[$name])) {
            return null;
        }

        return $conditions[$name];
    }

    public function listConditions()
    {
        if (self::$conditions)
            return self::$conditions;

        $result = [];

        $availableConditions = (array)self::get('conditions');
        foreach ($availableConditions as $name => $condition) {
            $className = array_get($condition, 'className');
            if (!class_exists($className))
                continue;

            $result[$name] = new $className($condition);
        }

        return self::$conditions = $result;
    }

    public function listRegisteredConditions()
    {
        if (self::$registeredConditions === null) {
            $this->loadRegisteredConditions();
        }

        if (!is_array(self::$registeredConditions)) {
            return [];
        }

        return self::$registeredConditions;
    }

    public function loadRegisteredConditions()
    {
        foreach (self::$registeredConditionsCallbacks as $callback) {
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
        if (self::$registeredConditions === null)
            self::$registeredConditions = [];

        foreach ($conditions as $className => $condition) {
            $this->registerCondition($className, $condition);
        }
    }

    public function registerCondition($className, $conditionInfo = null)
    {
        if (self::$registeredConditions === null)
            self::$registeredConditions = [];

        $defaults = [
            'name' => 'default',
            'label' => '',
            'description' => '',
        ];

        $condition = array_merge($defaults, $conditionInfo);
        $conditionName = array_get($condition, 'name');
        $condition['className'] = $className;

        self::$registeredConditions[$className] = $condition;
        self::$registeredConditionHints[$conditionName] = $className;
    }

    public static function registerConditionsCallback(callable $definitions)
    {
        self::$registeredConditionsCallbacks[] = $definitions;
    }
}
