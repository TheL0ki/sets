<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'is_available',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
    ];

    /**
     * Get the time slot as a string (e.g., "14:30-15:00")
     */
    public function getTimeSlotString(): string
    {
        return $this->start_time->format('H:i') . '-' . $this->end_time->format('H:i');
    }

    /**
     * Get the day of week (0 = Sunday, 1 = Monday, etc.)
     */
    public function getDayOfWeek(): int
    {
        return $this->start_time->dayOfWeek;
    }

    /**
     * Get the user that owns this availability.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only available time slots.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to get availabilities for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_time', '>=', $startDate)
                    ->where('end_time', '<=', $endDate);
    }

    /**
     * Scope to get availabilities that overlap with a time range.
     */
    public function scopeOverlapping($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });
    }
} 