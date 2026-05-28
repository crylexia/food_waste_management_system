<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->date('expiration_date')->nullable();
            $table->date('received_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('notes')->nullable();
            $table->enum('status', ['active', 'depleted', 'discarded'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_items');
    }
};