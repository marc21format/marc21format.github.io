<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HighschoolRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'highschool_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'user_id',
        'highschool_id',
        'year_start',
        'level',
        'year_end',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function highschool()
    {
        return $this->belongsTo(Highschool::class, 'highschool_id', 'highschool_id');
    }
}
