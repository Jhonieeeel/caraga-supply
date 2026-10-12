<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = [
        'view-dashboard',
        'manage-supply',
        'manage-stock',
        'create-requisition',
        'approve-requisition',
        'manage-procurement',
        'manage-users',
    ];

    private const MATRIX = [
        'SUPER-ADMIN' => self::PERMISSIONS,
        'ADMIN' => self::PERMISSIONS,
        'GASU' => ['manage-supply', 'manage-stock', 'create-requisition', 'view-dashboard'],
        'PMU' => ['manage-procurement', 'create-requisition', 'view-dashboard'],
    ];

    public function up(): void
    {
        // Rename legacy roles in place so existing model_has_roles rows keep working.
        Role::where('name', 'Super Admin')->update(['name' => 'SUPER-ADMIN']);
        Role::where('name', 'Admin')->update(['name' => 'ADMIN']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'GASU', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'PMU', 'guard_name' => 'web']);

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::MATRIX as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            $role?->syncPermissions($permissions);
        }

        // Legacy `User` role: safety-net permissions only, never deleted.
        // Additional GASU/PMU/HRMU access for a `User`-role holder is derived
        // LIVE from their Employee.unit (designation) — see
        // App\Models\User::hasPermissionTo() — not assigned statically here,
        // so a unit change takes effect immediately without a migration.
        $legacyUserRole = Role::where('name', 'User')->first();
        $legacyUserRole?->syncPermissions(['create-requisition', 'view-dashboard']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::where('name', 'SUPER-ADMIN')->update(['name' => 'Super Admin']);
        Role::where('name', 'ADMIN')->update(['name' => 'Admin']);

        Role::where('name', 'GASU')->delete();
        Role::where('name', 'PMU')->delete();

        Permission::whereIn('name', self::PERMISSIONS)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
