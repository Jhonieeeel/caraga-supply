<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            // Nullable for now: only populated going forward (new SupplyProjector
            // writes) or by the gasu:backfill-events command for existing rows.
            // Left nullable rather than flipped non-null here, since that flip
            // depends on a real backfill having actually run against production
            // first — a separate, later migration once that's done.
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
