<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeType extends Model
{
    use HasFactory;

    protected $table = 'degree_types';
    protected $primaryKey = 'degreetype_id';

    protected $fillable = [
        'degreelevel_id',
        'type_name',
        'abbreviation',
    ];

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'degreelevel_id', 'degreelevel_id');
    }

    /**
     * Many-to-Many: degree types can belong to many degree levels
     */
    public function degreeLevels()
    {
        return $this->belongsToMany(DegreeLevel::class, 'degree_level_type', 'degreetype_id', 'degreelevel_id')
            ->withTimestamps();
    }

    public function degreePrograms()
    {
        return $this->hasMany(DegreeProgram::class, 'degreetype_id', 'degreetype_id');
    }
}
