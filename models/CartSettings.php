<?php

namespace Igniter\Cart\Models;

use Igniter\Cart\Classes\CartConditionManager;
use Igniter\Flame\Database\Model;

/**
 * @method static instance()
 */
class CartSettings extends Model
{
    public $implement = [\System\Actions\SettingsModel::class];

    // A unique code
    public $settingsCode = 'igniter_cart_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'cartsettings';

    public function getConditionsAttribute($value)
    {
        $result = [];
        $registeredConditions = CartConditionManager::instance()->listRegisteredConditions();
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
