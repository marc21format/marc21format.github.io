<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeLevelType extends Model
{
    use HasFactory;

    protected $table = 'degree_level_type';

    protected $fillable = [
        'degreelevel_id',
        'degreetype_id',
    ];

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'degreelevel_id', 'degreelevel_id');
    }

    public function degreeType()
    {
        return $this->belongsTo(DegreeType::class, 'degreetype_id', 'degreetype_id');
    }
}
