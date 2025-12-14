<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrefixTitle extends Model
{
    use HasFactory;

    protected $table = 'prefix_titles';
    protected $primaryKey = 'prefix_id';

    protected $fillable = [
        'title',
        'abbreviation',
        'fieldofwork_id',
    ];

    public function professionalCredentials()
    {
        return $this->hasMany(ProfessionalCredential::class, 'prefix_id', 'prefix_id');
    }
}
