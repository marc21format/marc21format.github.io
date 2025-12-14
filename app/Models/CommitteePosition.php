<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteePosition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'committee_positions';
    protected $primaryKey = 'committeeposition_id';
    public $incrementing = true;
    protected $fillable = [
        'position_id',
        'committee_id',
    ];
    protected $dates = ['deleted_at'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id', 'committee_id');
    }
}
