<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_admin_janitorial_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')
                ->constrained('purchase_requests')
                ->cascadeOnDelete();

            $table->string('block_title')->nullable();
            $table->string('delivery_period')->nullable();
            $table->string('delivery_site')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pr_admin_janitorial_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_admin_janitorial_block_id')
                ->constrained('pr_admin_janitorial_blocks')
                ->cascadeOnDelete();

            // 'administrative' or 'janitorial'
            $table->string('item_group');

            $table->string('item_name')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 12, 2)->default(0);

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_admin_janitorial_items');
        Schema::dropIfExists('pr_admin_janitorial_blocks');
    }
};
