<?php namespace Igniter\Cart\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Schema;

/**
 * Rename content column
 */
class RenameContentFieldOnCartTable extends Migration
{
    public function up()
    {
        Schema::table('igniter_cart_cart', function (Blueprint $table) {
            $table->renameColumn('content', 'data');
        });
    }

    public function down()
    {
    }
}