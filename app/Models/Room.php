<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';
    protected $primaryKey = 'room_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'group',
        'adviser_id',
        'co_adviser_id',
        'president_id',
        'secretary_id',
    ];

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id', 'id');
    }

    public function coAdviser()
    {
        return $this->belongsTo(User::class, 'co_adviser_id', 'id');
    }

    public function president()
    {
        return $this->belongsTo(User::class, 'president_id', 'id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id', 'id');
    }
}
