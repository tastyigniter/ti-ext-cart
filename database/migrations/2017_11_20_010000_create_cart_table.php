<?php namespace Igniter\Cart\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Schema;

/**
 * Create cart and fill with records from cart field on customers table
 */
class CreateCartTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('igniter_cart_cart'))
            return;

        Schema::create('igniter_cart_cart', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('identifier');
            $table->string('instance');
            $table->longText('content');
            $table->nullableTimestamps();
            $table->primary(['identifier', 'instance']);
        });
    }

    public function down()
    {
        Schema::drop('igniter_cart_cart');
    }
}