<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'step_id',
        'step_number',
        'step_type',
        'data',
        'time_spent_seconds',
        'started_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the lead that owns this step
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Calculate time spent on this step
     */
    public function calculateTimeSpent(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return (int) abs($this->completed_at->diffInSeconds($this->started_at));
        }
        return null;
    }

    /**
     * Mark step as completed
     */
    public function markAsCompleted(): void
    {
        $completedAt = now();
        $timeSpent = $this->started_at
            ? (int) abs($completedAt->diffInSeconds($this->started_at))
            : null;

        $this->update([
            'completed_at' => $completedAt,
            'time_spent_seconds' => $timeSpent,
        ]);
    }
}
