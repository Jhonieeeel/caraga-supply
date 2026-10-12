<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A stock lot's identity inside its Supply aggregate's event stream is
     * its natural key, stock_number — this index is the real invariant the
     * aggregate relies on. Before this is applied against a database with
     * existing data, `gasu:backfill-events`'s preflight check must first
     * confirm no duplicate (supply_id, stock_number) pairs already exist.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unique(['supply_id', 'stock_number']);
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['supply_id', 'stock_number']);
        });
    }
};
