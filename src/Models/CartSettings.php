<?php

namespace Igniter\Cart\Models;

use Igniter\Cart\Classes\CartConditionManager;
use Igniter\Flame\Database\Model;

/**
 * @method static instance()
 */
class CartSettings extends Model
{
    public array $implement = [\Igniter\System\Actions\SettingsModel::class];

    // A unique code
    public string $settingsCode = 'igniter_cart_settings';

    // Reference to field configuration
    public string $settingsFieldsConfig = 'cartsettings';

    public function getConditionsAttribute($value)
    {
        $result = [];
        $registeredConditions = resolve(CartConditionManager::class)->listRegisteredConditions();
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
}
