<?php namespace Igniter\Cart\Database\Migrations;

use Igniter\Cart\Models\CartSettings;
use Illuminate\Database\Migrations\Migration;
use System\Models\Extensions_model;

/**
 * Add new cart total extension records as type 'total' in extensions table
 */
class CreateConditionsSettings extends Migration
{
    public function up()
    {
        $conditions = [];
        $seedConditions = $this->getConditions();

        foreach ($seedConditions as $condition) {

            $data = array_get($condition, 'data');
            if (!is_array($data))
                $data = unserialize($data);

            $conditions[$data['priority']] = array_get($data, 'name');
        }

        if (!CartSettings::get('conditions'))
            CartSettings::set('conditions', $conditions);
    }

    public function down()
    {

    }

    protected function getConditions()
    {
        $existingConditions = Extensions_model::getQuery()->select('data')
                                              ->where('type', 'cart_total')->get();
        if (!count($existingConditions))
            return [
                [
                    'data' => [
                        'priority' => '3',
                        'name' => 'coupon',
                        'title' => 'Coupon {coupon}',
                        'status' => '1',
                    ],
                ],
                [
                    'data' => [
                        'priority' => '4',
                        'name' => 'delivery',
                        'title' => 'Delivery',
                        'status' => '1',
                    ],
                ],
                [
                    'data' => [
                        'priority' => '5',
                        'name' => 'taxes',
                        'title' => 'VAT {tax}',
                        'status' => '1',
                    ],
                ],
            ];

        return $existingConditions;
    }
}
