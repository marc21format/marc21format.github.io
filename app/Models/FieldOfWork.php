<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldOfWork extends Model
{
    use HasFactory;

    protected $table = 'fields_of_work';
    protected $primaryKey = 'fieldofwork_id';

    protected $fillable = ['name'];

    public function professionalCredentials()
    {
        return $this->hasMany(ProfessionalCredential::class, 'fieldofwork_id', 'fieldofwork_id');
    }
}
