<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'display_name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    // Get permission_id in lists type
    public static function permission_role($id)
    {
        return DB::table('permission_role')->where('role_id', $id)->pluck('permission_id')->toArray();
    }

    // Get role_user data by using given id
    public static function role_user($id)
    {
        return DB::table('role_user')->where('user_id', $id)->first();
    }
}
