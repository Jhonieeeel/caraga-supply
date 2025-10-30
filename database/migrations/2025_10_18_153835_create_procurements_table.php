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
            $table->string('notice_of_award')->nullable();
            $table->string('project_title')->nullable();
            $table->string('contract_signing')->nullable();
            $table->string('source_of_funds')->nullable();
            $table->decimal('estimated_budget_total')->nullable();
            $table->decimal('estimated_budget_mooe')->nullable();
            $table->decimal('estimated_budget_co')->nullable();
            $table->string('pmo_end_user')->nullable();
            $table->string('early_activity')->nullable();
            $table->string('mode_of_procurement')->nullable();
            $table->string('advertisement_posting')->nullable();
            $table->string('submission_bids')->nullable();
            $table->integer('app_year')->nullable();
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
