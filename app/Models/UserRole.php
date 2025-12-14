<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    protected $primaryKey = 'role_id';

    public $incrementing = true;

    protected $fillable = [
        'role_title',
        'role_description',
    ];

    /**
     * Users that belong to this role (one-to-many).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
