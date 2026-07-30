<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\CompanyTarget;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Executive Dashboard — couche de calcul (KPIs, graphiques, listes).
 *
 * Réutilise :
 *   - CreanceService  → agrégats de créances (aging 30/60/90) + détail clients
 *   - AlerteService   → nombre d'alertes actives
 *
 * Toutes les requêtes coûteuses passent par Cache::remember (TTL 5 min).
 * La clé de cache inclut la date du jour → les compteurs "today_*"
 * basculent automatiquement à minuit sans flush manuel.
 *
 * Aucune règle métier existante n'est modifiée. Aucune migration ajoutée.
 */
class ExecutiveDashboardService
{
    private const CACHE_TTL    = 900;
    private const CACHE_PREFIX = 'exec_dashboard';

    public function __construct(
        private CreanceService $creanceService,
        private AlerteService $alerteService,
    ) {
        $this->amevoMissing = [];
    }

    /** @var array<int,string> Fields or endpoints missing from Amevo payload */
    private array $amevoMissing;

    private function recordAmevoMissing(string $key, string $reason): void
    {
        $this->amevoMissing[] = "{$key}: {$reason}";
    }

    // ─────────────────────────────────────────────────────────────
    // KPIs
    // ─────────────────────────────────────────────────────────────

    public function todayRevenue(): float
    {
        return Cache::remember($this->key('today_revenue'), self::CACHE_TTL, function () {
            $amevo = $this->creanceService->getAmevoData();
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['caJourDates'], $data['caJourData'])) {
                $todayKey = Carbon::today()->format('d/m');
                $index = array_search($todayKey, $data['caJourDates']);
                if ($index !== false) {
                    $sum = 0.0;
                    foreach ($data['caJourData'] as $entry) {
                        $sum += (float) ($entry['data'][$index] ?? 0);
                    }

                    return $sum;
                }
            }

            $this->recordAmevoMissing('todayRevenue', 'caJourDates/caJourData for today is missing');

            return (float) Invoice::whereDate('invoice_date', Carbon::today())->sum('total_ttc');
        });
    }

    public function todayPayments(): float
    {
        return Cache::remember($this->key('today_payments'), self::CACHE_TTL, function () {
            // Amevo payload does not expose payment transactions / modes.
            $this->recordAmevoMissing('todayPayments', 'payment transactions not present in Amevo payload');

            return (float) DB::table('paiements')
                ->whereDate('date_paiement', Carbon::today())
                ->sum('montant');
        });
    }

    public function currentMonthRevenue(): float
    {
        return Cache::remember($this->key('current_month_revenue'), self::CACHE_TTL, function () {
            $mois = (int) Carbon::now()->format('m');
            $annee = Carbon::now()->year;
            $amevo = $this->creanceService->getAmevoData($mois, $annee);
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['ca_total'])) {
                return (float) $data['ca_total'];
            }

            $this->recordAmevoMissing('currentMonthRevenue', 'ca_total missing for current month');

            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();

            return (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_ttc');
        });
    }

    public function previousMonthRevenue(): float
    {
        return Cache::remember($this->key('previous_month_revenue'), self::CACHE_TTL, function () {
            $dt = Carbon::now()->subMonthNoOverflow();
            $mois = (int) $dt->format('m');
            $annee = $dt->year;
            $amevo = $this->creanceService->getAmevoData($mois, $annee);
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['ca_total'])) {
                return (float) $data['ca_total'];
            }

            $this->recordAmevoMissing('previousMonthRevenue', "ca_total missing for {$mois}/{$annee}");

            $start = $dt->startOfMonth();
            $end   = $dt->endOfMonth();

            return (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_ttc');
        });
    }

    /** @return array{current:float, previous:float, delta:float, percent:?float} */
    public function monthOverMonthDelta(): array
    {
        $current  = $this->currentMonthRevenue();
        $previous = $this->previousMonthRevenue();
        $delta    = $current - $previous;
        $percent  = $previous > 0 ? ($delta / $previous) * 100 : null;

        return compact('current', 'previous', 'delta', 'percent');
    }

    /** @return array{total:float, j30:float, j60:float, j90_plus:float} */
    public function receivablesSummary(): array
    {
        return Cache::remember($this->key('receivables_summary'), self::CACHE_TTL, function () {
            $amevo = $this->creanceService->getAmevoData();
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['reste_a_payer'])) {
                // Amevo provides aggregated receivables total but not aging buckets.
                $result = [
                    'total' => (float) $data['reste_a_payer'],
                    'j30' => null,
                    'j60' => null,
                    'j90_plus' => null,
                ];

                $this->recordAmevoMissing('receivablesSummary', 'aging buckets (j30/j60/j90) are not provided by Amevo');

                // Provide DB fallback for buckets to avoid breaking callers
                $fallback = $this->creanceService->getCreancesData();
                $result['j30'] = (float) ($fallback['total30'] ?? 0);
                $result['j60'] = (float) ($fallback['total60'] ?? 0);
                $result['j90_plus'] = (float) ($fallback['total90'] ?? 0);

                return $result;
            }

            $this->recordAmevoMissing('receivablesSummary', 'reste_a_payer missing in Amevo payload');

            $data = $this->creanceService->getCreancesData();

            return [
                'total' => (float) ($data['totalCreances'] ?? 0),
                'j30' => (float) ($data['total30'] ?? 0),
                'j60' => (float) ($data['total60'] ?? 0),
                'j90_plus' => (float) ($data['total90'] ?? 0),
            ];
        });
    }

    public function alertesCount(): int
    {
        return Cache::remember($this->key('alertes_count'), self::CACHE_TTL,
            fn () => $this->alerteService->getAllAlertes()->count(),
        );
    }

    /**
     * CEO daily pulse, including comparisons with the same day last week and
     * the matching calendar date last month, plus a 14-day sparkline.
     *
     * @return array<string, mixed>
     */
    public function salesPulse(): array
    {
        return Cache::remember($this->key('sales_pulse'), self::CACHE_TTL, function (): array {
            $today = Carbon::today();
            $start = $today->copy()->subDays(13);

            $amevo = $this->creanceService->getAmevoData();
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['caJourDates'], $data['caJourData'])) {
                $dates = $data['caJourDates'];
                $sparkline = [];

                // build last-14-days sparkline from Amevo dates (if available)
                for ($d = $start->copy(); $d->lte($today); $d->addDay()) {
                    $key = $d->format('d/m');
                    $index = array_search($key, $dates);
                    $sum = 0.0;
                    if ($index !== false) {
                        foreach ($data['caJourData'] as $entry) {
                            $sum += (float) ($entry['data'][$index] ?? 0);
                        }
                    }
                    $sparkline[] = $sum;
                }

                $current = end($sparkline);

                // Try to compute single-day comparisons from Amevo series as well
                $vsLastWeek = null;
                $vsLastMonth = null;

                $todayKey = $today->format('d/m');
                $weekKey = $today->copy()->subWeek()->format('d/m');
                $monthKey = $today->copy()->subMonthNoOverflow()->format('d/m');

                $dates = $data['caJourDates'];
                $todayIdx = array_search($todayKey, $dates);
                $weekIdx = array_search($weekKey, $dates);
                $monthIdx = array_search($monthKey, $dates);

                if ($todayIdx !== false && $weekIdx !== false) {
                    $sumWeek = 0.0;
                    foreach ($data['caJourData'] as $entry) {
                        $sumWeek += (float) ($entry['data'][$weekIdx] ?? 0);
                    }
                    $vsLastWeek = $this->comparison((float) $current, $sumWeek);
                }

                if ($todayIdx !== false && $monthIdx !== false) {
                    $sumMonth = 0.0;
                    foreach ($data['caJourData'] as $entry) {
                        $sumMonth += (float) ($entry['data'][$monthIdx] ?? 0);
                    }
                    $vsLastMonth = $this->comparison((float) $current, $sumMonth);
                }

                // If Amevo cannot provide comparators, fallback to DB for those comparators
                if ($vsLastWeek === null) {
                    $this->recordAmevoMissing('salesPulse.vs_last_week', 'last-week single-day comparator not available in Amevo');
                    $weekAgo = (float) Invoice::whereDate('invoice_date', $today->copy()->subWeek())->sum('total_ttc');
                    $vsLastWeek = $this->comparison((float) $current, $weekAgo);
                }

                if ($vsLastMonth === null) {
                    $this->recordAmevoMissing('salesPulse.vs_last_month', 'last-month single-day comparator not available in Amevo');
                    $monthAgo = (float) Invoice::whereDate('invoice_date', $today->copy()->subMonthNoOverflow())->sum('total_ttc');
                    $vsLastMonth = $this->comparison((float) $current, $monthAgo);
                }

                return [
                    'current' => (float) $current,
                    'vs_last_week' => $vsLastWeek,
                    'vs_last_month' => $vsLastMonth,
                    'sparkline' => $sparkline,
                ];
            }

            $this->recordAmevoMissing('salesPulse', 'caJourDates/caJourData missing');

            // fallback to DB behaviour
            $lastWeek = $today->copy()->subWeek();
            $lastMonth = $today->copy()->subMonthNoOverflow();

            $daily = Invoice::query()
                ->whereBetween('invoice_date', [$start, $today])
                ->selectRaw('DATE(invoice_date) as day, SUM(total_ttc) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            $sparkline = [];
            for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
                $sparkline[] = (float) ($daily[$date->toDateString()] ?? 0);
            }

            $current = (float) ($daily[$today->toDateString()] ?? 0);

            return [
                'current' => $current,
                'vs_last_week' => $this->comparison($current, (float) Invoice::whereDate('invoice_date', $lastWeek)->sum('total_ttc')),
                'vs_last_month' => $this->comparison($current, (float) Invoice::whereDate('invoice_date', $lastMonth)->sum('total_ttc')),
                'sparkline' => $sparkline,
            ];
        });
    }

    /** @return array<string, mixed> */
    public function companyTarget(): array
    {
        return Cache::remember($this->key('company_target'), self::CACHE_TTL, function (): array {
            $today = Carbon::today();
            $actual = $this->currentMonthRevenue();
            $target = (float) (CompanyTarget::query()
                ->where('month', $today->month)
                ->where('year', $today->year)
                ->value('target_amount') ?? 0);
            $elapsedDays = $today->day;
            $daysInMonth = $today->daysInMonth;
            $projection = $elapsedDays > 0 ? ($actual / $elapsedDays) * $daysInMonth : 0.0;

            return [
                'actual' => $actual,
                'target' => $target,
                'attainment_percent' => $target > 0 ? ($actual / $target) * 100 : null,
                'projection' => round($projection, 2),
                'days_elapsed' => $elapsedDays,
                'days_in_month' => $daysInMonth,
            ];
        });
    }

    /** @return array<string, int> */
    public function activeClients(): array
    {
        return Cache::remember($this->key('active_clients'), self::CACHE_TTL, function (): array {
            $today = Carbon::today();

            return collect([30, 60, 90])->mapWithKeys(function (int $days) use ($today): array {
                $count = Invoice::query()
                    ->whereNotNull('client_id')
                    ->whereDate('invoice_date', '>=', $today->copy()->subDays($days - 1))
                    ->distinct('client_id')
                    ->count('client_id');

                return ["last_{$days}_days" => $count];
            })->all();
        });
    }

    public function overdueInvoiceCount(): int
    {
        return Cache::remember($this->key('overdue_invoice_count'), self::CACHE_TTL,
            fn (): int => Invoice::query()
                ->whereDate('date_echeance', '<', Carbon::today())
                ->whereRaw('total_ttc > COALESCE(montant_paye, 0)')
                ->count(),
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function revenueComparisonGrid(): array
    {
        return Cache::remember($this->key('revenue_comparison_grid'), self::CACHE_TTL, function (): array {
            $today = Carbon::today();
            $currentStart = $today->copy()->startOfMonth();
            $previousStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
            $previousEnd = $previousStart->copy()->addDays(min($today->day, $previousStart->daysInMonth) - 1);
            $yearAgoStart = $currentStart->copy()->subYear();
            $yearAgoEnd = $today->copy()->subYearNoOverflow();
            $current = $this->revenueBetween($currentStart, $today);
            $previous = $this->revenueBetween($previousStart, $previousEnd);
            $yearAgo = $this->revenueBetween($yearAgoStart, $yearAgoEnd);

            return [
                ['label' => 'MTD', 'current' => $current, 'comparison_label' => 'Mois précédent (même durée)', 'comparison' => $previous, ...$this->comparison($current, $previous)],
                ['label' => 'MTD', 'current' => $current, 'comparison_label' => 'Même période N-1', 'comparison' => $yearAgo, ...$this->comparison($current, $yearAgo)],
            ];
        });
    }

    /**
     * Margin is calculated from the line's stored sale price and the current
     * product cost. Both values are TTC in the current schema. Lines without a
     * cost are deliberately excluded rather than overstating profitability.
     *
     * @return array<string, array<string, mixed>>
     */
    public function grossMarginAnalysis(): array
    {
        return Cache::remember($this->key('gross_margin_analysis'), self::CACHE_TTL, function (): array {
            return [
                'products' => $this->marginBy('product'),
                'clients' => $this->marginBy('client'),
                'sellers' => $this->marginBy('seller'),
                'missing_cost_lines' => (int) DB::table('invoice_items')
                    ->join('products', 'products.id', '=', 'invoice_items.product_id')
                    ->whereNull('products.cost_price')
                    ->count(),
            ];
        });
    }

    /** @return array{labels:array<int,string>, margins:array<int,float>, percentages:array<int,float>, revenues:array<int,float>} */
    private function marginBy(string $dimension): array
    {
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereNotNull('products.cost_price');

        $labelSql = match ($dimension) {
            'client' => 'COALESCE(clients.nom, invoices.client_name, \'Client inconnu\')',
            'seller' => 'COALESCE(users.name, \'Non attribué\')',
            default => 'products.name',
        };

        if ($dimension === 'client') {
            $query->leftJoin('clients', 'clients.id', '=', 'invoices.client_id');
        }

        if ($dimension === 'seller') {
            $query->leftJoin('users', 'users.id', '=', 'invoices.created_by');
        }

        $rows = $query
            ->selectRaw("{$labelSql} as label")
            ->selectRaw('SUM((invoice_items.unit_price - products.cost_price) * invoice_items.quantity) as margin')
            ->selectRaw('SUM(invoice_items.unit_price * invoice_items.quantity) as revenue')
            ->groupBy('label')
            ->orderByDesc('margin')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($value): string => (string) $value)->all(),
            'margins' => $rows->pluck('margin')->map(fn ($value): float => (float) $value)->all(),
            'revenues' => $rows->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
            'percentages' => $rows->map(fn ($row): float => (float) $row->revenue > 0
                ? ((float) $row->margin / (float) $row->revenue) * 100
                : 0.0)->values()->all(),
        ];
    }

    /**
     * Build a query for margin details grouped by a dimension (product/client/seller).
     * Returns a Query Builder which callers may paginate or cursor() for streaming.
     */
    public function marginDetailsQuery(string $dimension, ?Carbon $from = null, ?Carbon $to = null)
    {
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereNotNull('products.cost_price');

        $labelSql = match ($dimension) {
            'client' => 'COALESCE(clients.nom, invoices.client_name, \'Client inconnu\')',
            'seller' => "COALESCE(users.name, 'Non attribué')",
            default => 'products.name',
        };

        if ($dimension === 'client') {
            $query->leftJoin('clients', 'clients.id', '=', 'invoices.client_id');
        }

        if ($dimension === 'seller') {
            $query->leftJoin('users', 'users.id', '=', 'invoices.created_by');
        }

        if ($from && $to) {
            $query->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()]);
        } elseif ($from) {
            $query->where('invoices.invoice_date', '>=', $from->toDateString());
        } elseif ($to) {
            $query->where('invoices.invoice_date', '<=', $to->toDateString());
        }

        $query->selectRaw("{$labelSql} as label")
            ->selectRaw('SUM((invoice_items.unit_price - products.cost_price) * invoice_items.quantity) as margin')
            ->selectRaw('SUM(invoice_items.unit_price * invoice_items.quantity) as revenue')
            ->selectRaw('SUM(invoice_items.quantity) as qty')
            ->groupBy('label');

        return $query;
    }

    /**
     * Paginated margin details convenience wrapper.
     * Returns a LengthAwarePaginator-friendly Query Builder executed by caller.
     */
    public function marginDetails(string $dimension, ?Carbon $from = null, ?Carbon $to = null)
    {
        return $this->marginDetailsQuery($dimension, $from, $to);
    }

    /**
     * Build a query for TVA (tax) details at invoice level.
     */
    public function tvaDetailsQuery(?Carbon $from = null, ?Carbon $to = null)
    {
        $query = Invoice::query()->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->select([
                'invoices.id as invoice_id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                DB::raw("COALESCE(clients.nom, invoices.client_name, 'Client inconnu') as client_label"),
                'invoices.tva as tva_amount',
            ]);

        if ($from && $to) {
            $query->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()]);
        } elseif ($from) {
            $query->where('invoices.invoice_date', '>=', $from->toDateString());
        } elseif ($to) {
            $query->where('invoices.invoice_date', '<=', $to->toDateString());
        }

        return $query;
    }

    /**
     * Paginated TVA details wrapper.
     */
    public function tvaDetails(?Carbon $from = null, ?Carbon $to = null)
    {
        return $this->tvaDetailsQuery($from, $to);
    }

    /** @return array{labels:array<int,string>, values:array<int,float>, configured_rate:float} */
    public function tvaLiabilityByMonth(int $months = 12): array
    {
        return Cache::remember($this->key("tva_liability_{$months}"), self::CACHE_TTL, function () use ($months): array {
            $start = Carbon::today()->subMonthsNoOverflow($months - 1)->startOfMonth();
            $rows = Invoice::query()
                ->where('invoice_date', '>=', $start)
                ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month_key, SUM(tva) as total")
                ->groupBy('month_key')
                ->pluck('total', 'month_key');
            $labels = [];
            $values = [];

            for ($i = 0; $i < $months; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);
                $labels[] = $month->translatedFormat('M Y');
                $values[] = (float) ($rows[$month->format('Y-m')] ?? 0);
            }

            return [
                'labels' => $labels,
                'values' => $values,
                'configured_rate' => (float) CompanySetting::current()->tva_rate,
            ];
        });
    }

    /** @return array{labels:array<int,string>, invoiced:array<int,float>, collected:array<int,float>, collection_ratios:array<int,?float>} */
    public function cashCollectedVsInvoiced(int $months = 12): array
    {
        return Cache::remember($this->key("cash_collected_vs_invoiced_{$months}"), self::CACHE_TTL, function () use ($months): array {
            $start = Carbon::today()->subMonthsNoOverflow($months - 1)->startOfMonth();
            $labels = [];
            $invoicedValues = [];
            $collectedValues = [];
            $ratios = [];

            for ($i = 0; $i < $months; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);
                $mois = (int) $month->format('m');
                $annee = $month->year;
                $amevo = $this->creanceService->getAmevoData($mois, $annee);
                $data = $amevo['data'] ?? null;

                $labels[] = $month->translatedFormat('M Y');

                if ($data && isset($data['ca_total'])) {
                    $invoiceTotal = (float) $data['ca_total'];
                    $collectionTotal = (float) ($data['ca_encaisse'] ?? 0);
                } else {
                    $this->recordAmevoMissing('cashCollectedVsInvoiced', "month {$mois}/{$annee} missing in Amevo");
                    $monthStart = $month->copy()->startOfMonth();
                    $monthEnd = $month->copy()->endOfMonth();
                    $invoiceTotal = (float) Invoice::whereBetween('invoice_date', [$monthStart, $monthEnd])->sum('total_ttc');
                    $collectionTotal = (float) DB::table('paiements')->whereBetween('date_paiement', [$monthStart, $monthEnd])->sum('montant');
                }

                $invoicedValues[] = $invoiceTotal;
                $collectedValues[] = $collectionTotal;
                $ratios[] = $invoiceTotal > 0 ? ($collectionTotal / $invoiceTotal) * 100 : null;
            }

            return ['labels' => $labels, 'invoiced' => $invoicedValues, 'collected' => $collectedValues, 'collection_ratios' => $ratios];
        });
    }

    /** @return array{labels:array<int,string>, datasets:array<int,array<string,mixed>>} */
    public function paymentModeMix(int $months = 12): array
    {
        return Cache::remember($this->key("payment_mode_mix_{$months}"), self::CACHE_TTL, function () use ($months): array {
            // Amevo does not include payment-mode breakdowns.
            $this->recordAmevoMissing('paymentModeMix', 'payment-mode time series not present in Amevo');

            $start = Carbon::today()->subMonthsNoOverflow($months - 1)->startOfMonth();
            $modes = ['espèces', 'chèque', 'virement', 'traite'];
            $rows = DB::table('paiements')
                ->where('date_paiement', '>=', $start)
                ->selectRaw("DATE_FORMAT(date_paiement, '%Y-%m') as month_key, mode, SUM(montant) as total")
                ->groupBy('month_key', 'mode')
                ->get()
                ->groupBy('mode');
            $monthsIndex = [];
            $labels = [];

            for ($i = 0; $i < $months; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);
                $monthsIndex[] = $month->format('Y-m');
                $labels[] = $month->translatedFormat('M Y');
            }

            $colors = ['#14B8A6', '#f59e0b', '#2d5380', '#ef4444'];
            $datasets = collect($modes)->map(function (string $mode, int $index) use ($rows, $monthsIndex, $colors): array {
                $values = $rows->get($mode, collect())->pluck('total', 'month_key');

                return [
                    'label' => ucfirst($mode),
                    'data' => collect($monthsIndex)->map(fn (string $key): float => (float) ($values[$key] ?? 0))->all(),
                    'backgroundColor' => $colors[$index],
                    'borderColor' => $colors[$index],
                    'fill' => true,
                ];
            })->all();

            return compact('labels', 'datasets');
        });
    }

    private function revenueBetween(Carbon $start, Carbon $end): float
    {
        // Try to compute by summing Amevo monthly totals for covered months,
        // and fallback to DB for any missing months.
        $cursor = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->endOfMonth();
        $total = 0.0;

        while ($cursor->lte($endMonth)) {
            $mois = (int) $cursor->format('m');
            $annee = $cursor->year;
            $amevo = $this->creanceService->getAmevoData($mois, $annee);
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['ca_total'])) {
                $total += (float) $data['ca_total'];
            } else {
                // For months not covered by Amevo, sum invoices in that month's date range intersection
                $monthStart = $cursor->copy()->startOfMonth();
                $monthEnd = $cursor->copy()->endOfMonth();
                $rangeStart = $start->greaterThan($monthStart) ? $start : $monthStart;
                $rangeEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;
                $this->recordAmevoMissing('revenueBetween', "ca_total missing for {$mois}/{$annee}");
                $total += (float) Invoice::query()->whereBetween('invoice_date', [$rangeStart, $rangeEnd])->sum('total_ttc');
            }

            $cursor->addMonth();
        }

        return $total;
    }

    /** @return array{delta:float, percent:?float} */
    private function comparison(float $current, float $baseline): array
    {
        $delta = $current - $baseline;

        return [
            'delta' => $delta,
            'percent' => $baseline > 0 ? ($delta / $baseline) * 100 : null,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Listes (top clients / produits / factures récentes)
    // ─────────────────────────────────────────────────────────────

    /**
     * Top N clients par CA facturé sur les 12 derniers mois glissants.
     * Utilise invoices.client_id → clients.nom, fallback sur invoices.client_name.
     *
     * @return Collection<int, array{client:string, total:float, nb_factures:int}>
     */
    public function topClients(int $limit = 5): Collection
    {
        return Cache::remember($this->key("top_clients_{$limit}"), self::CACHE_TTL, function () use ($limit) {
            // Amevo payload does not expose per-client aggregates in this endpoint.
            $this->recordAmevoMissing('topClients', 'per-client aggregates not present in Amevo payload');

            $since = Carbon::now()->subYear();

            return Invoice::query()
                ->select([
                    DB::raw('COALESCE(clients.nom, invoices.client_name) as client_label'),
                    DB::raw('SUM(invoices.total_ttc) as total'),
                    DB::raw('COUNT(invoices.id) as nb_factures'),
                ])
                ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
                ->where('invoices.invoice_date', '>=', $since)
                ->groupBy('client_label')
                ->orderByDesc('total')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'client'      => $row->client_label ?: 'Client inconnu',
                    'total'       => (float) $row->total,
                    'nb_factures' => (int) $row->nb_factures,
                ]);
        });
    }

    /**
     * Top N produits par CA sur les 12 derniers mois glissants.
     *
     * @return Collection<int, array{produit:string, quantite:int, total:float}>
     */
    public function topProducts(int $limit = 5): Collection
    {
        return Cache::remember($this->key("top_products_{$limit}"), self::CACHE_TTL, function () use ($limit) {
            // Amevo payload does not expose per-product aggregates in this endpoint.
            $this->recordAmevoMissing('topProducts', 'per-product aggregates not present in Amevo payload');

            $since = Carbon::now()->subYear();

            return DB::table('invoice_items')
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->join('products', 'products.id', '=', 'invoice_items.product_id')
                ->where('invoices.invoice_date', '>=', $since)
                ->select([
                    'products.name as produit',
                    DB::raw('SUM(invoice_items.quantity) as quantite'),
                    DB::raw('SUM(invoice_items.total) as total'),
                ])
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'produit'  => (string) $row->produit,
                    'quantite' => (int) $row->quantite,
                    'total'    => (float) $row->total,
                ]);
        });
    }

    /**
     * Dernières factures créées (tout type).
     */
    public function recentInvoices(int $limit = 8): Collection
    {
        return Cache::remember($this->key("recent_invoices_{$limit}"), self::CACHE_TTL, function () use ($limit) {
            $this->recordAmevoMissing('recentInvoices', 'invoice-level listing not present in Amevo payload');

            return Invoice::with(['client', 'creator'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Datasets pour Chart.js
    // ─────────────────────────────────────────────────────────────

    /**
     * CA facturé par mois sur les N derniers mois (inclus le mois courant).
     *
     * @return array{labels:array<int,string>, values:array<int,float>}
     */
    public function revenueByMonth(int $months = 12): array
    {
        return Cache::remember($this->key("revenue_by_month_{$months}"), self::CACHE_TTL, function () use ($months) {
            $start = Carbon::now()->subMonthsNoOverflow($months - 1)->startOfMonth();

            $labels = [];
            $values = [];

            for ($i = 0; $i < $months; $i++) {
                $d  = $start->copy()->addMonthsNoOverflow($i);
                $mois = (int) $d->format('m');
                $annee = $d->year;
                $amevo = $this->creanceService->getAmevoData($mois, $annee);
                $data = $amevo['data'] ?? null;

                $labels[] = $d->translatedFormat('M Y');
                if ($data && isset($data['ca_total'])) {
                    $values[] = (float) $data['ca_total'];
                } else {
                    $this->recordAmevoMissing('revenueByMonth', "month {$mois}/{$annee} missing in Amevo");
                    $ym = $d->format('Y-m');
                    $rows = Invoice::query()
                        ->where('invoice_date', '>=', $d->copy()->startOfMonth())
                        ->where('invoice_date', '<=', $d->copy()->endOfMonth())
                        ->sum('total_ttc');
                    $values[] = (float) $rows;
                }
            }

            return compact('labels', 'values');
        });
    }

    /**
     * CA par catégorie sur le mois courant.
     * Source : table `suivi_categories` (données locales), agrégée sur
     * les suivis vendeur du mois. Pas de lien direct produit→catégorie
     * dans le schéma actuel — c'est la source la plus fiable disponible
     * localement, cohérente avec /dashboard/categories.
     *
     * Renvoie un tableau vide si la table n'existe pas.
     *
     * @return array{labels:array<int,string>, values:array<int,float>}
     */
    public function revenueByCategory(): array
    {
        return Cache::remember($this->key('revenue_by_category'), self::CACHE_TTL, function () {
            // Prefer Amevo payload categoriesData for current month when available.
            $amevo = $this->creanceService->getAmevoData();
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['categoriesData']) && is_array($data['categoriesData'])) {
                $rows = collect($data['categoriesData'])->sortByDesc('ca')->slice(0, 10);

                return [
                    'labels' => $rows->pluck('libelle')->map(fn ($v) => (string) $v)->all(),
                    'values' => $rows->pluck('ca')->map(fn ($v) => (float) $v)->all(),
                ];
            }

            $this->recordAmevoMissing('revenueByCategory', 'categoriesData missing in Amevo; falling back to suivi_categories table');

            if (!Schema::hasTable('suivi_categories') || !Schema::hasTable('suivi_vendeurs')) {
                return ['labels' => [], 'values' => []];
            }

            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();

            $rows = DB::table('suivi_categories')
                ->join('suivi_vendeurs', 'suivi_vendeurs.id', '=', 'suivi_categories.suivi_vendeur_id')
                ->whereBetween('suivi_vendeurs.date', [$start->toDateString(), $end->toDateString()])
                ->select([
                    'suivi_categories.categorie',
                    DB::raw('SUM(suivi_categories.ca) as total'),
                ])
                ->groupBy('suivi_categories.categorie')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            return [
                'labels' => $rows->pluck('categorie')->map(fn ($v) => (string) $v)->all(),
                'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
            ];
        });
    }

    /**
     * CA facturé par utilisateur (created_by) sur le mois courant.
     *
     * @return array{labels:array<int,string>, values:array<int,float>}
     */
    public function revenueByUser(): array
    {
        return Cache::remember($this->key('revenue_by_user'), self::CACHE_TTL, function () {
            $amevo = $this->creanceService->getAmevoData();
            $data = $amevo['data'] ?? null;

            if ($data && isset($data['vendorCards']) && is_array($data['vendorCards'])) {
                $rows = collect($data['vendorCards'])->sortByDesc('ca_total');

                return [
                    'labels' => $rows->pluck('nom')->map(fn ($v) => (string) $v)->all(),
                    'values' => $rows->pluck('ca_total')->map(fn ($v) => (float) $v)->all(),
                ];
            }

            $this->recordAmevoMissing('revenueByUser', 'vendorCards missing in Amevo; falling back to invoices.created_by');

            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();

            $rows = Invoice::query()
                ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
                ->whereBetween('invoices.invoice_date', [$start, $end])
                ->select([
                    DB::raw("COALESCE(users.name, 'Système') as user_label"),
                    DB::raw('SUM(invoices.total_ttc) as total'),
                ])
                ->groupBy('user_label')
                ->orderByDesc('total')
                ->get();

            return [
                'labels' => $rows->pluck('user_label')->map(fn ($v) => (string) $v)->all(),
                'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Agrégat unique consommé par le contrôleur / la vue
    // ─────────────────────────────────────────────────────────────

    /** @return array<string,mixed> */
    public function getDashboardData(): array
    {
        // reset amevo missing list for this run
        $this->amevoMissing = [];
        $mom = $this->monthOverMonthDelta();

        return [
            'currency' => CompanySetting::current()->currency,
            'kpis' => [
                'today_revenue'          => $this->todayRevenue(),
                'today_payments'         => $this->todayPayments(),
                'current_month_revenue'  => $mom['current'],
                'previous_month_revenue' => $mom['previous'],
                'month_delta'            => $mom['delta'],
                'month_delta_percent'    => $mom['percent'],
                'receivables'            => $this->receivablesSummary(),
                'alertes_count'          => $this->alertesCount(),
                'active_clients'         => $this->activeClients(),
                'overdue_invoice_count'  => $this->overdueInvoiceCount(),
            ],
            'sales_pulse' => $this->salesPulse(),
            'company_target' => $this->companyTarget(),
            'revenue_comparison' => $this->revenueComparisonGrid(),
            'financial_analysis' => $this->grossMarginAnalysis(),
            'cash_flow' => [
                'collected_vs_invoiced' => $this->cashCollectedVsInvoiced(),
                'payment_mode_mix' => $this->paymentModeMix(),
            ],
            'top_clients'     => $this->topClients(5),
            'top_products'    => $this->topProducts(5),
            'recent_invoices' => $this->recentInvoices(8),
            'charts' => [
                'revenue_by_month'    => $this->revenueByMonth(12),
                'revenue_by_category' => $this->revenueByCategory(),
                'revenue_by_user'     => $this->revenueByUser(),
                'tva_liability'       => $this->tvaLiabilityByMonth(),
            ],
            'amevo_missing' => array_values(array_unique($this->amevoMissing)),
        ];
    }

    /** Vide tous les caches du dashboard exécutif. */
    public function flushCache(): void
    {
        foreach ([
            'today_revenue', 'today_payments',
            'current_month_revenue', 'previous_month_revenue',
            'sales_pulse', 'company_target', 'active_clients', 'overdue_invoice_count', 'revenue_comparison_grid',
            'gross_margin_analysis', 'tva_liability_12',
            'cash_collected_vs_invoiced_12', 'payment_mode_mix_12',
            'receivables_summary', 'alertes_count',
            'top_clients_5', 'top_products_5', 'recent_invoices_8',
            'revenue_by_month_12', 'revenue_by_category', 'revenue_by_user',
        ] as $k) {
            Cache::forget($this->key($k));
        }
    }

    private function key(string $suffix): string
    {
        return sprintf('%s:%s:%s', self::CACHE_PREFIX, Carbon::today()->toDateString(), $suffix);
    }
}
