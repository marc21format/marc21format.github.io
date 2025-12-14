<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'professional_credentials';
    protected $primaryKey = 'credential_id';

    protected $fillable = [
        'user_id',
        'fieldofwork_id',
        'prefix_id',
        'suffix_id',
        'issued_on',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function fieldOfWork()
    {
        return $this->belongsTo(FieldOfWork::class, 'fieldofwork_id', 'fieldofwork_id');
    }

    public function prefix()
    {
        return $this->belongsTo(PrefixTitle::class, 'prefix_id', 'prefix_id');
    }

    public function suffix()
    {
        return $this->belongsTo(SuffixTitle::class, 'suffix_id', 'suffix_id');
    }
}
