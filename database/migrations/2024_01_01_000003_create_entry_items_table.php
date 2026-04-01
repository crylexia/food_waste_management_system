<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->decimal('used_quantity', 10, 2)->default(0.00);
            $table->decimal('wasted_quantity', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('daily_entry_id');
            $table->index('item_id');
        });
        
        // Add check constraints via raw SQL only for MySQL
        // SQLite doesn't support ALTER TABLE ADD CONSTRAINT for CHECK
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE entry_items ADD CONSTRAINT chk_quantities CHECK (used_quantity >= 0 AND wasted_quantity >= 0)');
            DB::statement('ALTER TABLE entry_items ADD CONSTRAINT chk_at_least_one CHECK (used_quantity > 0 OR wasted_quantity > 0)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entry_items');
    }
};
