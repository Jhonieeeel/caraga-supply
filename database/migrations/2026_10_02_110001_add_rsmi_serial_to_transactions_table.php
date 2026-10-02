<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores the RSMI serial (Supply-YYYY-MM-N) a report was generated with,
     * so the next serial in a month is max(existing) + 1 instead of a count
     * that repeats when a report is regenerated.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('rsmi_serial')->nullable()->after('rsmi_file');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('rsmi_serial');
        });
    }
};
