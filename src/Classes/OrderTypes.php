<?php

namespace Igniter\Cart\Classes;

use Igniter\System\Classes\ExtensionManager;
use Illuminate\Support\Collection;

class OrderTypes
{
    protected ?array $registeredOrderTypes = null;

    protected array $registeredCallbacks = [];

    public function makeOrderTypes($location): Collection
    {
        return collect($this->listOrderTypes())
            ->map(function($orderType) use ($location) {
                return resolve($orderType['className'], ['location' => $location, 'config' => $orderType]);
            });
    }

    public function getOrderType($code): array
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
        foreach ($this->registeredCallbacks as $callback) {
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

    public function registerCallback(callable $definitions)
    {
        $this->registeredCallbacks[] = $definitions;
    }
}
