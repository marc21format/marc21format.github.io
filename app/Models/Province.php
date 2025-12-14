<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';
    protected $primaryKey = 'province_id';

    protected $fillable = [
        'province_name',
    ];

    public function cities()
    {
        return $this->hasMany(City::class, 'province_id', 'province_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'province_id', 'province_id');
    }
}
