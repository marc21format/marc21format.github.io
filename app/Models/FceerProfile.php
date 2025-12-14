<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FceerProfile extends Model
{
    use HasFactory;

    protected $table = 'fceer_profiles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'volunteer_number',
        'student_number',
        'fceer_batch',
        'student_group',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Room::class, 'student_group', 'room_id');
    }
}
