<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_transportations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();

            // Markers
            $table->string('delivery_period')->nullable();
            $table->string('delivery_site')->nullable();
            $table->string('pick_up')->nullable();
            $table->string('reqs_vehicle')->nullable();
            $table->string('reqs_model')->nullable();
            $table->string('reqs_number')->nullable();

            $table->timestamps();
        });

        Schema::create('pr_transportation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_transportation_id')
                ->constrained('pr_transportations')
                ->cascadeOnDelete();

            $table->unsignedInteger('pax_qty')->default(0);
            $table->string('itinerary')->nullable();
            $table->string('date_time')->nullable();
            $table->unsignedInteger('no_of_vehicles')->default(0);
            $table->decimal('estimated_unit_cost', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_transportation_items');
        Schema::dropIfExists('pr_transportations');
    }
};
