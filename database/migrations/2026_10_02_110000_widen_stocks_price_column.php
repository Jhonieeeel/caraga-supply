<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * decimal(8,2) caps a unit price at 999,999.99 (MySQL strict mode rejects
     * anything larger); widen it to match StockForm's max validation.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });
    }
};
