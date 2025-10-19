<?php

use App\Models\Annual;
use App\Models\PurchaseOrder;
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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('notice_of_award');
            $table->string('project_title');
            $table->string('contract_signing');
            $table->string('source_of_funds');
            $table->decimal('estimated_budget_total');
            $table->decimal('estimated_budget_mooe');
            $table->decimal('estimated_budget_co');
            $table->string('pmo_end_user');
            $table->string('early_activity');
            $table->string('mode_of_procurement');
            $table->string('advertisement_posting');
            $table->string('submission_bids');
            $table->decimal('app_year');
            $table->text('remarks')->nullable();
            $table->foreignId('end_user')->nullable()->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
