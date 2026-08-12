<?php

namespace App\Models;

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
}
