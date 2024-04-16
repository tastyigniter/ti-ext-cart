<?php

namespace Igniter\Automation\Classes;

use System\Classes\ExtensionManager;

class BaseEvent extends AbstractBase
{
    /**
     * @var \Igniter\Flame\Database\Model model object
     */
    protected $model;

    /**
     * @var array Contains the event parameter values.
     */
    protected $params = [];

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Returns information about this event, including name and description.
     */
    public function eventDetails()
    {
        return [
            'name' => 'Event',
            'description' => 'Event description',
            'group' => 'groupcode',
        ];
    }

    /**
     * Generates event parameters based on arguments from the triggering system event.
     * @param string $eventName
     * @return array
     */
    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        return [];
    }

    /**
     * Sets multiple params.
     * @param array $params
     * @return void
     */
    public function setEventParams($params)
    {
        $this->params = $params;
    }

    /**
     * Returns all params.
     * @return array
     */
    public function getEventParams()
    {
        return $this->params;
    }

    /**
     * Returns the event name.
     * @return array
     */
    public function getEventName()
    {
        return array_get($this->eventDetails(), 'name', 'Event');
    }

    /**
     * Returns the event description.
     * @return array
     */
    public function getEventDescription()
    {
        return array_get($this->eventDetails(), 'description');
    }

    /**
     * Returns the event group.
     * @return array
     */
    public function getEventGroup()
    {
        return array_get($this->eventDetails(), 'group');
    }

    /**
     * Resolves an event or action identifier from the called class name or object.
     * @param mixed Class name or object
     * @return string Identifier in format of vendor-extension-class
     */
    public function getEventIdentifier()
    {
        $namespace = normalize_class_name(get_called_class());
        if (strpos($namespace, '\\') === null) {
            return $namespace;
        }

        $parts = explode('\\', $namespace);
        $class = array_pop($parts);
        $slice = array_slice($parts, 1, 2);
        $code = strtolower(implode('-', $slice).'-'.$class);

        return $code;
    }

    public static function findRulesValues($key = null)
    {
        $results = [];
        $automationRules = ExtensionManager::instance()->getRegistrationMethodValues('registerAutomationRules');
        if (is_null($key)) {
            return $automationRules;
        }

        foreach ($automationRules as $extension => $automationRule) {
            if (!$values = array_get($automationRule, $key)) {
                continue;
            }

            if (!is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $index => $value) {
                if (is_string($index)) {
                    $results[$index] = $value;
                } else {
                    $results[] = $value;
                }
            }
        }

        return $results;
    }

    public static function findEvents()
    {
        $results = [];
        foreach (self::findRulesValues('events') as $eventCode => $eventClass) {
            if (!class_exists($eventClass)) {
                continue;
            }

            $eventObj = new $eventClass;
            $results[$eventClass] = [$eventCode, $eventObj];
        }

        return $results;
    }

    public static function findEventObjects()
    {
        $results = [];
        foreach (self::findEvents() as $eventClass => [$eventCode, $eventObj]) {
            $results[$eventClass] = $eventObj;
        }

        return $results;
    }

    public static function findEventPresets()
    {
        return self::findRulesValues('presets');
    }
}
