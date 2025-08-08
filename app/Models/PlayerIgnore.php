<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerIgnore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ignorer_id',
        'ignored_id',
        'reason',
    ];

    /**
     * Get the user who is ignoring.
     */
    public function ignorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignorer_id');
    }

    /**
     * Get the user who is being ignored.
     */
    public function ignored(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignored_id');
    }

    /**
     * Scope to get ignores by ignorer.
     */
    public function scopeByIgnorer($query, $ignorerId)
    {
        return $query->where('ignorer_id', $ignorerId);
    }

    /**
     * Scope to get ignores by ignored user.
     */
    public function scopeByIgnored($query, $ignoredId)
    {
        return $query->where('ignored_id', $ignoredId);
    }

    /**
     * Check if a user is ignoring another user.
     */
    public static function isIgnoring(int $ignorerId, int $ignoredId): bool
    {
        return static::where('ignorer_id', $ignorerId)
                    ->where('ignored_id', $ignoredId)
                    ->exists();
    }

    /**
     * Check if two users have any ignore relationship (bidirectional).
     */
    public static function hasIgnoreRelationship(int $userId1, int $userId2): bool
    {
        return static::where(function ($query) use ($userId1, $userId2) {
            $query->where('ignorer_id', $userId1)
                  ->where('ignored_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('ignorer_id', $userId2)
                  ->where('ignored_id', $userId1);
        })->exists();
    }
}
