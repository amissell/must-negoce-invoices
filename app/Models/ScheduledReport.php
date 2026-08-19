<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'params' => 'array',
        'recipients' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function runs()
    {
        return $this->hasMany(ScheduledReportRun::class);
    }

    public function computeNextRunAt(?Carbon $base = null): ?Carbon
    {
        if ($this->recurrence === 'none') {
            return null;
        }

        $baseDate = $base ?? now();

        return match ($this->recurrence) {
            'daily' => $baseDate->copy()->addDay(),
            'weekly' => $baseDate->copy()->addWeek(),
            'monthly' => $baseDate->copy()->addMonth(),
            default => null,
        };
    }
}
