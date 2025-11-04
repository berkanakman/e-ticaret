<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Role -> Admin ilişkisi (Many-to-Many)
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_role', 'role_id', 'admin_id')
            ->withTimestamps();
    }
}
