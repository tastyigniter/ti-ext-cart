<?php

namespace Igniter\Automation\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePrimaryKeyBigintAllTables extends Migration
{
    public function up()
    {
        Schema::table('igniter_automation_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
        });

        Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
        });

        Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
        });

        Schema::table('igniter_automation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
        });
    }

    public function down()
    {
    }
}
