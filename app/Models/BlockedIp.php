<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'reason',
        'attempts_count',
        'blocked_until',
        'is_permanent',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    /**
     * Check if IP is currently blocked
     */
    public function isBlocked(): bool
    {
        if ($this->is_permanent) {
            return true;
        }

        if ($this->blocked_until === null) {
            return true;
        }

        return $this->blocked_until->isFuture();
    }

    /**
     * Block an IP address
     */
    public static function blockIp(string $ip, string $reason = null, int $minutes = 60): self
    {
        return static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_until' => now()->addMinutes($minutes),
                'attempts_count' => \DB::raw('attempts_count + 1'),
            ]
        );
    }

    /**
     * Block an IP permanently
     */
    public static function blockPermanently(string $ip, string $reason = null): self
    {
        return static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'is_permanent' => true,
                'blocked_until' => null,
            ]
        );
    }

    /**
     * Check if an IP is blocked
     */
    public static function isIpBlocked(string $ip): bool
    {
        $blocked = static::where('ip_address', $ip)->first();

        if (!$blocked) {
            return false;
        }

        return $blocked->isBlocked();
    }

    /**
     * Unblock an IP
     */
    public static function unblockIp(string $ip): bool
    {
        return static::where('ip_address', $ip)->delete() > 0;
    }

    /**
     * Scope for active blocks
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
                ->orWhere('blocked_until', '>', now());
        });
    }
}
