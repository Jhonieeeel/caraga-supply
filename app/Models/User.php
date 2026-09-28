<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles {
        HasRoles::hasPermissionTo as baseHasPermissionTo;
    }

    /**
     * Permissions granted live to a base "User"-role holder based on their
     * Employee's org unit (designation), on top of whatever the User role
     * itself grants. Kept dynamic (not assigned at migration time) so a
     * change to someone's unit takes effect immediately without an admin
     * having to reassign a role.
     *
     * Deliberately has no 'GASU' entry: a base User's GASU access is always
     * limited to create-requisition/view-dashboard (granted to the User role
     * itself) — Supply/Stock management (manage-supply/manage-stock) is never
     * derived from designation, only from an actually-assigned GASU (or
     * ADMIN/SUPER-ADMIN) role.
     *
     * @var array<string, list<string>>
     */
    private const UNIT_PERMISSIONS = [
        'PMU' => ['manage-procurement'],
        'HRMU' => ['manage-users'],
    ];

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->baseHasPermissionTo($permission, $guardName)) {
            return true;
        }

        if (! is_string($permission) || ! $this->hasRole('User')) {
            return false;
        }

        $unitName = $this->employee?->unit?->name;

        return in_array($permission, self::UNIT_PERMISSIONS[$unitName] ?? [], true);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'dtr_number',
        'designation',
        'office_position'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guest() {
        return $this->hasOne(Guest::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

}
