<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user access the SUPER-ADMIN has taken away, even though the user's
     * role or unit would normally grant it. Extra per-user access is stored
     * as ordinary direct Spatie permissions.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('revoked_permissions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('revoked_permissions');
        });
    }
};
