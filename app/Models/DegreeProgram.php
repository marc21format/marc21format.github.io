<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeProgram extends Model
{
    use HasFactory;

    protected $table = 'degree_programs';
    protected $primaryKey = 'degreeprogram_id';

    protected $fillable = [
        'full_degree_program_name',
        'program_abbreviation',
        'degreelevel_id',
        'degreetype_id',
        'degreefield_id',
    ];

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'degreelevel_id', 'degreelevel_id');
    }

    public function degreeType()
    {
        return $this->belongsTo(DegreeType::class, 'degreetype_id', 'degreetype_id');
    }

    public function degreeField()
    {
        return $this->belongsTo(DegreeField::class, 'degreefield_id', 'degreefield_id');
    }

    public function educationalRecords()
    {
        return $this->hasMany(EducationalRecord::class, 'degreeprogram_id', 'degreeprogram_id');
    }
}
