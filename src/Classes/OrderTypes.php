<?php

namespace Igniter\Cart\Classes;

use Igniter\System\Classes\ExtensionManager;
use Illuminate\Support\Collection;

class OrderTypes
{
    protected ?array $registeredOrderTypes = null;

    protected static array $registeredCallbacks = [];

    public function makeOrderTypes($location): Collection
    {
        return collect($this->listOrderTypes())
            ->map(function ($orderType) use ($location) {
                return new $orderType['className']($location, $orderType);
            });
    }

    public function getOrderType($code): AbstractOrderType
    {
        return array_get($this->listOrderTypes(), $code);
    }

    public function listOrderTypes()
    {
        if (is_null($this->registeredOrderTypes)) {
            $this->loadOrderTypes();
        }

        return $this->registeredOrderTypes;
    }

    protected function loadOrderTypes()
    {
        foreach (self::$registeredCallbacks as $callback) {
            $callback($this);
        }

        $registeredConditions = resolve(ExtensionManager::class)->getRegistrationMethodValues('registerOrderTypes');
        foreach ($registeredConditions as $orderTypes) {
            $this->registerOrderTypes($orderTypes);
        }
    }

    public function registerOrderTypes(array $orderTypes)
    {
        foreach ($orderTypes as $className => $definition) {
            $this->registerOrderType($className, $definition);
        }
    }

    public function registerOrderType(string $className, array $definition)
    {
        $code = $definition['code'] ?? strtolower(basename($className));

        if (!array_key_exists('name', $definition)) {
            $definition['name'] = $code;
        }

        $this->registeredOrderTypes[$code] = array_merge($definition, [
            'className' => $className,
        ]);
    }

    public static function registerCallback(callable $definitions)
    {
        self::$registeredCallbacks[] = $definitions;
    }
}
