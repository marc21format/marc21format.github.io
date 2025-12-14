<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FceerBatch extends Model
{
    use HasFactory;

    protected $table = 'fceer_batch';
    protected $primaryKey = 'batch_id';
    public $timestamps = false;

    protected $fillable = [
        'batch_no',
        'year',
    ];
}
