<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalRecord extends Model
{
    use HasFactory;

    protected $table = 'educational_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'user_id',
        'degreeprogram_id',
        'year_start',
        'university_id',
        'year_graduated',
        'DOST_Scholarship',
        'latin_honor',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function degreeProgram()
    {
        return $this->belongsTo(DegreeProgram::class, 'degreeprogram_id', 'degreeprogram_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }
}
