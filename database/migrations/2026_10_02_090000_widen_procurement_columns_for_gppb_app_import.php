<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The GPPB APP template has project titles over 1,000 characters, budgets
     * in the millions (decimal(8,2) caps at 999,999.99), and a Start/End of
     * Procurement Activity schedule instead of per-milestone dates.
     */
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->text('project_title')->nullable()->change();
            $table->decimal('estimated_budget_total', 15, 2)->nullable()->change();
            $table->decimal('estimated_budget_mooe', 15, 2)->nullable()->change();
            $table->decimal('estimated_budget_co', 15, 2)->nullable()->change();
            $table->string('procurement_start')->nullable()->after('mode_of_procurement');
            $table->string('procurement_end')->nullable()->after('procurement_start');
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->dropColumn(['procurement_start', 'procurement_end']);
            $table->string('project_title')->nullable()->change();
            $table->decimal('estimated_budget_total')->nullable()->change();
            $table->decimal('estimated_budget_mooe')->nullable()->change();
            $table->decimal('estimated_budget_co')->nullable()->change();
        });
    }
};
