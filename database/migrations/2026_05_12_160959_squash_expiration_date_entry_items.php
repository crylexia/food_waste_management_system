<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Squashed migration: a previous iteration added then immediately removed
 * an expiration_date column from entry_items. This no-op migration preserves
 * the migration history timestamp without touching the schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: column was added and removed in the same development session.
        // Expiration tracking is handled by the storage_items table instead.
    }

    public function down(): void
    {
        // No-op.
    }
};
