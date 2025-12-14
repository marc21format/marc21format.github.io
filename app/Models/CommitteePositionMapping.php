<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteePositionMapping extends Model
{
    use HasFactory;

    protected $table = 'committee_position_mappings';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'committee_id',
        'committeeposition_id',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id', 'committee_id');
    }

    public function committeePosition()
    {
        return $this->belongsTo(CommitteePosition::class, 'committeeposition_id', 'committeeposition_id');
    }
}
