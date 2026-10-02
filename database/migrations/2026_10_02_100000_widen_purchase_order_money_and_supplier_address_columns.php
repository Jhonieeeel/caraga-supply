<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * decimal(8,2) caps PO amounts at 999,999.99, and supplier addresses are
     * validated up to 1,000 characters (and copied onto the PO when a supplier
     * is picked) while the columns were varchar(255).
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('variance', 15, 2)->nullable()->change();
            $table->decimal('contract_price', 15, 2)->nullable()->change();
            $table->text('supplier_address')->nullable()->change();
            $table->text('supplier_contacts')->nullable()->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('variance')->nullable()->change();
            $table->decimal('contract_price')->nullable()->change();
            $table->string('supplier_address')->nullable()->change();
            $table->string('supplier_contacts')->nullable()->change();
        });
    }
};
