<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename content column
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('igniter_cart_cart', function (Blueprint $table) {
            $table->renameColumn('content', 'data');
        });
    }

    public function down()
    {
    }
};
