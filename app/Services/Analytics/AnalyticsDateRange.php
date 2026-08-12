<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyticsDateRange
{
    /** @return array{from: Carbon, to: Carbon} */
    public function fromRequest(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->subDays(89)->startOfDay();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();

        if ($from->gt($to)) {
            throw ValidationException::withMessages([
                'from' => 'La date de début doit précéder la date de fin.',
            ]);
        }

        return compact('from', 'to');
    }
}
