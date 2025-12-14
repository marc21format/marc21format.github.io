<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CommitteeMember extends Model
{
    use HasFactory;

    protected $table = 'committee_members';
    protected $primaryKey = 'member_id';

    protected $fillable = [
        'user_id',
        'committee_id',
        'position_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id', 'committee_id');
    }

    public function position()
    {
        // If a master `positions` table exists, position_id refers to `positions.position_id`.
        if (Schema::hasTable('positions')) {
            return $this->belongsTo(\App\Models\Position::class, 'position_id', 'position_id');
        }
        // Fallback: legacy committee_positions table stores the position rows itself
        return $this->belongsTo(CommitteePosition::class, 'position_id', 'position_id');
    }
}
