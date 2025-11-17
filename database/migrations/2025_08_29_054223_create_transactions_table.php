<?php

use App\Models\Requisition;
use App\Models\Stock;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Requisition::class)->nullable();
            $table->foreignIdFor(Stock::class)->nullable();
            $table->string('type_of_transaction'); // PO or RIS
            $table->integer('quantity');
            $table->integer('initial_quantity')->nullable();
            $table->string('rsmi_file')->nullable();
            $table->string('rpci_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
