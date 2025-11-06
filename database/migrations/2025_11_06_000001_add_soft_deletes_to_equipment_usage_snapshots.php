<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            // Drop old unique index on (entity_equipment_id, invoice_id)
            $table->dropUnique(['entity_equipment_id', 'invoice_id']);
            // Add deleted_at column for soft deletes
            $table->softDeletes();
            // Recreate unique index including deleted_at so soft-deleted rows don't conflict
            $table->unique(['entity_equipment_id', 'invoice_id', 'deleted_at'], 'snapshots_equipment_invoice_deleted_unique');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            // Drop the composite unique including deleted_at
            $table->dropUnique('snapshots_equipment_invoice_deleted_unique');
            // Drop deleted_at column
            $table->dropSoftDeletes();
            // Restore original unique index
            $table->unique(['entity_equipment_id', 'invoice_id']);
        });
    }
};
