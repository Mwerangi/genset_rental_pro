<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'module', 'sort_order'];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_name', 'name');
    }
}
