<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $table = 'universities';
    protected $primaryKey = 'university_id';

    protected $fillable = [
        'university_name',
        'abbreviation',
    ];

    public function degreePrograms()
    {
        return $this->hasMany(DegreeProgram::class, 'university_id', 'university_id');
    }
}
