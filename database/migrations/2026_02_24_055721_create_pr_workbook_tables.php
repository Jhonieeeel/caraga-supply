<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pr_workbook_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->string('block_title')->nullable();

            $table->timestamps();
        });

        Schema::create('pr_workbook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workbook_block_id')->constrained('pr_workbook_blocks')->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->string('particular')->nullable();
            $table->string('delivery_date')->nullable();

            $table->decimal('qty', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_workbook_items');
        Schema::dropIfExists('pr_workbook_blocks');
    }
};
