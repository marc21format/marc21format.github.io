<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeLevel extends Model
{
    use HasFactory;

    protected $table = 'degree_levels';
    protected $primaryKey = 'degreelevel_id';

    protected $fillable = [
        'level_name',
        'degree_level',
        'abbreviation',
    ];

    public function degreeTypes()
    {
        return $this->belongsToMany(DegreeType::class, 'degree_level_type', 'degreelevel_id', 'degreetype_id')
            ->withTimestamps();
    }

    public function degreePrograms()
    {
        return $this->hasMany(DegreeProgram::class, 'degreelevel_id', 'degreelevel_id');
    }
}
