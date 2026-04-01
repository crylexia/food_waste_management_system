<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // The measurement unit (e.g. kg, L, pcs, pack)
            $table->string('unit', 30)->nullable()->after('price');

            // How much of that unit one "piece" of this item represents
            // e.g. a bag of rice = 25 (kg), a bottle of oil = 1 (L)
            $table->decimal('unit_quantity', 10, 3)->default(1)->after('unit');

            // Current stock: how many units are on hand
            $table->decimal('stock_quantity', 10, 3)->default(0)->after('unit_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'unit_quantity', 'stock_quantity']);
        });
    }
};
