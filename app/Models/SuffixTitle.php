<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuffixTitle extends Model
{
    use HasFactory;

    protected $table = 'suffix_titles';
    protected $primaryKey = 'suffix_id';

    protected $fillable = [
        'title',
        'abbreviation',
        'fieldofwork_id',
    ];

    public function professionalCredentials()
    {
        return $this->hasMany(ProfessionalCredential::class, 'suffix_id', 'suffix_id');
    }
}
