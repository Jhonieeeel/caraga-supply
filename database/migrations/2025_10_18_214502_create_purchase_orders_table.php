<?php

use App\Models\Procurement;
use App\Models\PurchaseRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PurchaseRequest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Procurement::class)->constrained()->cascadeOnDelete();
            $table->string('order_number')->nullable();
            $table->date('noa')->nullable();
            $table->decimal('variance')->nullable();
            $table->string('po_number')->nullable();
            $table->foreignId('date_posted')->nullable()->constrained('purchase_requests')->cascadeOnDelete();
            $table->date('po_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('ntp')->nullable();
            $table->date('resolution_number')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('contact_price')->nullable();
            $table->string('email_link')->nullable();
            $table->foreignId('abc_based_app')->nullable()->constrained('procurements')->cascadeOnDelete();
            $table->foreignId('abc')->nullable()->constrained('purchase_requests')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
