<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pr_meal_lot_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->string('lot_title')->nullable();
            $table->string('location')->nullable();
            $table->string('delivery_period')->nullable();

            $table->timestamps();
        });

        Schema::create('pr_meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_block_id')->constrained('pr_meal_lot_blocks')->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->unsignedInteger('pax_qty')->default(0);
            $table->string('meal_snack')->nullable();
            $table->string('arrangement')->nullable();
            $table->string('delivery_date')->nullable();

            $table->text('menu')->nullable();
            $table->text('other_requirement')->nullable();

            $table->decimal('qty', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 15, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('pr_accommodation_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_block_id')->constrained('pr_meal_lot_blocks')->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->string('accommodation_title')->nullable();
            $table->string('location')->nullable();
            $table->string('date')->nullable();

            $table->timestamps();
        });

        Schema::create('pr_accommodation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_block_id')->constrained('pr_accommodation_blocks')->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->unsignedInteger('no_days')->default(0);
            $table->string('room_type')->nullable();
            $table->string('room_arrangement')->nullable();
            $table->string('inclusive_dates')->nullable();

            $table->text('remarks')->nullable();
            $table->text('other_requirement')->nullable();

            $table->decimal('qty', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_accommodation_items');
        Schema::dropIfExists('pr_accommodation_blocks');
        Schema::dropIfExists('pr_meal_items');
        Schema::dropIfExists('pr_meal_lot_blocks');
    }
};
