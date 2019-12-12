<?php namespace Igniter\Cart\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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

            $data = array_get((array)$condition, 'data');
            if (!is_array($data))
                $data = unserialize($data);

            $conditions[$data['priority']] = array_get($data, 'name');
        }

        $table = DB::table('extension_settings')->where('item', 'igniter_cart_settings');
        if (!$table->exists())
            $table->update(['data' => serialize(['conditions' => $conditions])]);
    }

    public function down()
    {

    }

    protected function getConditions()
    {
        $existingConditions = DB::table('extensions')->select('data')
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
