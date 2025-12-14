<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Highschool extends Model
{
    use HasFactory;

    // Ensure table/primary key match your database schema used in controllers/routes
    protected $table = 'highschools';
    protected $primaryKey = 'highschool_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Allow mass assignment for the column used across the app
    protected $fillable = [
        'highschool_name',
        'abbreviation',
        'type',
    ];

    public function records()
    {
        return $this->hasMany(HighschoolRecord::class, 'highschool_id', 'highschool_id');
    }
}
