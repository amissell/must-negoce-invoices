<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $month
 * @property int $year
 * @property string $target_amount
 */
class CompanyTarget extends Model
{
    protected $fillable = ['month', 'year', 'target_amount'];
    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'target_amount' => 'decimal:2',
    ];
}
