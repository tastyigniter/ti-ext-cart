<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Create cart and fill with records from cart field on customers table
 */

class CreateCartTable extends Migration
{
    public function up()
    {
        Schema::create(config('cart.database.table'), function (Blueprint $table) {
            $table->string('identifier');
            $table->string('instance');
            $table->longText('content');
            $table->nullableTimestamps();
            $table->primary(['identifier', 'instance']);
        });
    }

    public function down()
    {
        Schema::drop(config('cart.database.table'));
    }
}