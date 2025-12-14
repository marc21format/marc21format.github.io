<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $table = 'barangays';
    protected $primaryKey = 'barangay_id';

    protected $fillable = [
        'city_id',
        'barangay_name',
    ];

    /**
     * The city this barangay belongs to.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    /**
     * Addresses in this barangay.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'barangay_id', 'barangay_id');
    }
}
