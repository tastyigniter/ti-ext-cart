<?php

namespace Igniter\Automation\Database\Migrations;

use Exception;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyConstraintsToTables extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        // Commented out so foreign keys are not added on new installations.
        // For existing installations, another migration has been added to drop all foreign keys.

        Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id', DB::getTablePrefix().'igniter_actions_automation_rule_id_foreign')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id', DB::getTablePrefix().'igniter_conditions_automation_rule_id_foreign')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('igniter_automation_logs', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('rule_action_id')->nullable()->change();
            $table->foreign('rule_action_id')
                ->references('id')
                ->on('igniter_automation_rule_actions')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        try {
            Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
                $table->dropForeign('igniter_actions_automation_rule_id_foreign');
            });

            Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
                $table->dropForeign('igniter_conditions_automation_rule_id_foreign');
            });
        } catch (Exception $ex) {
        }

        try {
            Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
                $table->dropForeign(DB::getTablePrefix().'igniter_actions_automation_rule_id_foreign');
            });

            Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
                $table->dropForeign(DB::getTablePrefix().'igniter_conditions_automation_rule_id_foreign');
            });
        } catch (Exception $ex) {
        }
    }
}
