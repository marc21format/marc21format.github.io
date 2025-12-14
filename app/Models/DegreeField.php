<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeField extends Model
{
    use HasFactory;

    protected $table = 'degree_fields';
    protected $primaryKey = 'degreefield_id';

    protected $fillable = [
        'field_name',
        'abbreviation',
    ];

    public function degreePrograms()
    {
        return $this->hasMany(DegreeProgram::class, 'degreefield_id', 'degreefield_id');
    }

    public function mappings()
    {
        return $this->hasMany(DegreeFieldMapping::class, 'degreefield_id', 'degreefield_id');
    }
}
