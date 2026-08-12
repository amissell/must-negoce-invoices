<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

/**
 * @property string $type
 * @property string $severity
 * @property string $message
 * @property string|null $related_type
 * @property int|null $related_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $read_at
 */
class Alert extends Model
{
    public const TYPE_CREDIT_LIMIT_BREACH = 'credit_limit_breach';
    public const TYPE_CHURN_RISK = 'churn_risk';
    public const TYPE_INVOICE_ANOMALY = 'invoice_anomaly';
    public const TYPE_OVERDUE_SPIKE = 'overdue_spike';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    protected $fillable = [
        'type',
        'severity',
        'message',
        'related_type',
        'related_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'related_id' => 'integer',
        'user_id' => 'integer',
        'read_at' => 'datetime',
    ];

    /** @return array<string, array<int, mixed>> */
    public static function validationRules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(self::types())],
            'severity' => ['required', 'string', Rule::in(self::severities())],
        ];
    }

    /** @return array<int, string> */
    public static function types(): array
    {
        return [
            self::TYPE_CREDIT_LIMIT_BREACH,
            self::TYPE_CHURN_RISK,
            self::TYPE_INVOICE_ANOMALY,
            self::TYPE_OVERDUE_SPIKE,
        ];
    }

    /** @return array<int, string> */
    public static function severities(): array
    {
        return [self::SEVERITY_INFO, self::SEVERITY_WARNING, self::SEVERITY_CRITICAL];
    }
    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
