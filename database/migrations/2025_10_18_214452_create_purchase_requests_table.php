<?php

use App\Models\Procurement;
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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Procurement::class)->constrained()->cascadeOnDelete();
            $table->date('closing_date')->nullable();
            $table->date('input_date')->nullable();
            $table->string('app_spp_pdf_file')->nullable();
            $table->string('app_spp_pdf_filename')->nullable();
            $table->string('philgeps_pdf_file')->nullable();
            $table->string('philgeps_pdf_filename')->nullable();
            $table->string('pr_number')->nullable();
            $table->foreignId('abc_based_app')->nullable()->constrained('procurements');
            $table->decimal('abc', 15, 2)->nullable();
            $table->string('email_posting')->nullable();
            $table->date('date_posted')->nullable();
            $table->foreignId('app_year')->nullable()->constrained('procurements');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
