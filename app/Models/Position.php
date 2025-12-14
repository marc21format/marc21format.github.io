<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'position_id';

    protected $fillable = [
        'position_name',
    ];

    public function committees()
    {
        return $this->hasMany(CommitteePosition::class, 'position_id', 'position_id');
    }
}
