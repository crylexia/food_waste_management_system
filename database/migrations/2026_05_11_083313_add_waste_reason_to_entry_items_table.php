<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_add_waste_reason_to_entry_items_table.php
    public function up(): void
    {
        Schema::table('entry_items', function (Blueprint $table) {
            $table->string('waste_reason')->nullable()->after('wasted_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('entry_items', function (Blueprint $table) {
            $table->dropColumn('waste_reason');
        });
    }
};
