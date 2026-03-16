<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();

            $table->string('delivery_period')->nullable();
            $table->string('delivery_site')->nullable();

            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 12, 2)->default(0);
            $table->text('technical_specifications')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_services');
    }
};
