<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';
    protected $primaryKey = 'city_id';

    protected $fillable = [
        'city_name',
        'province_id',
    ];

    /**
     * Barangays inside this city (one-to-many).
     */
    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'city_id', 'city_id');
    }

    /**
     * Addresses inside this city (one-to-many).
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'city_id', 'city_id');
    }

    /**
     * The province this city belongs to.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
