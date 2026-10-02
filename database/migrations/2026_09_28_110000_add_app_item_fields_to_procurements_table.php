<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->string('category')->nullable()->after('project_title');
            $table->integer('quantity')->nullable()->after('category');
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('unit_cost', 15, 2)->nullable()->after('unit');
            $table->decimal('total_abc', 15, 2)->nullable()->after('unit_cost');
            $table->integer('remaining_quantity')->nullable()->after('total_abc');
            $table->decimal('remaining_budget', 15, 2)->nullable()->after('remaining_quantity');
            $table->string('procurement_strategy')->nullable()->after('remaining_budget');
            $table->string('procurement_status')->default('not yet started')->after('procurement_strategy');
            $table->string('bid_evaluation_criteria')->nullable()->after('procurement_status');
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'quantity',
                'unit',
                'unit_cost',
                'total_abc',
                'remaining_quantity',
                'remaining_budget',
                'procurement_strategy',
                'procurement_status',
                'bid_evaluation_criteria',
            ]);
        });
    }
};
