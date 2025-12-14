<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'addresses';
    protected $primaryKey = 'address_id';

    protected $fillable = [
        'house_number',
        'street',
        'barangay_id',
        'city_id',
        'province_id',
        'created_at',
        'updated_at',
    ];

    /**
     * City the address belongs to.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    /**
     * Barangay the address belongs to.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    /**
     * Province relation (if using provinces table).
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    /**
     * User profiles that reference this address (one-to-many).
     */
    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class, 'address_id', 'address_id');
    }
}
