<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReviewSeason extends Model
{
    protected $fillable = [
        'start_month',
        'start_year',
        'end_month',
        'end_year',
        'is_active',
        'set_by_user_id',
    ];

    protected $casts = [
        'start_month' => 'integer',
        'start_year' => 'integer',
        'end_month' => 'integer',
        'end_year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the currently active review season
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    /**
     * Get the start date of the review season
     */
    public function getStartDateAttribute(): Carbon
    {
        return Carbon::create($this->start_year, $this->start_month, 1)->startOfMonth();
    }

    /**
     * Get the end date of the review season
     */
    public function getEndDateAttribute(): Carbon
    {
        return Carbon::create($this->end_year, $this->end_month, 1)->endOfMonth();
    }

    /**
     * Get label for the range
     */
    public function getRangeLabelAttribute(): string
    {
        $start = Carbon::create($this->start_year, $this->start_month, 1)->format('M Y');
        $end = Carbon::create($this->end_year, $this->end_month, 1)->format('M Y');
        return $start === $end ? $start : "{$start} - {$end}";
    }

    /**
     * Check if a date is within the review season
     */
    public function isDateWithinSeason($date): bool
    {
        $checkDate = Carbon::parse($date);
        return $checkDate->between($this->start_date, $this->end_date);
    }

    /**
     * Get all weekend dates within the review season
     */
    public function getWeekendDates(): array
    {
        $dates = [];
        $current = $this->start_date->copy();
        $end = $this->end_date;

        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, [0, 6])) { // Saturday (6) or Sunday (0)
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Relationship with the user who set this season
     */
    public function setByUser()
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }
}
