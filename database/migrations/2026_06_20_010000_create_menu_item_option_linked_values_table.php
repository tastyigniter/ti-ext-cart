<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_option_linked_values', function (Blueprint $table): void {
            $table->unsignedInteger('menu_item_option_value_id');
            $table->unsignedInteger('menu_option_id');
            $table->primary(['menu_item_option_value_id', 'menu_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_option_linked_values');
    }
};
