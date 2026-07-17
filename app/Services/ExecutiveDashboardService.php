<?php

namespace App\Services;

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
    private const CACHE_TTL    = 300;
    private const CACHE_PREFIX = 'exec_dashboard';

    public function __construct(
        private CreanceService $creanceService,
        private AlerteService $alerteService,
    ) {
    }

    // ─────────────────────────────────────────────────────────────
    // KPIs
    // ─────────────────────────────────────────────────────────────

    public function todayRevenue(): float
    {
        return Cache::remember($this->key('today_revenue'), self::CACHE_TTL,
            fn () => (float) Invoice::whereDate('invoice_date', Carbon::today())->sum('total_ttc'),
        );
    }

    public function todayPayments(): float
    {
        return Cache::remember($this->key('today_payments'), self::CACHE_TTL,
            fn () => (float) DB::table('paiements')
                ->whereDate('date_paiement', Carbon::today())
                ->sum('montant'),
        );
    }

    public function currentMonthRevenue(): float
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        return Cache::remember($this->key('current_month_revenue'), self::CACHE_TTL,
            fn () => (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_ttc'),
        );
    }

    public function previousMonthRevenue(): float
    {
        $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $end   = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        return Cache::remember($this->key('previous_month_revenue'), self::CACHE_TTL,
            fn () => (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_ttc'),
        );
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
            $data = $this->creanceService->getCreancesData();

            return [
                'total'    => (float) ($data['totalCreances'] ?? 0),
                'j30'      => (float) ($data['total30'] ?? 0),
                'j60'      => (float) ($data['total60'] ?? 0),
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
        return Cache::remember($this->key("recent_invoices_{$limit}"), self::CACHE_TTL,
            fn () => Invoice::with(['client', 'creator'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get(),
        );
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

            $rows = Invoice::query()
                ->where('invoice_date', '>=', $start)
                ->select([
                    DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as ym"),
                    DB::raw('SUM(total_ttc) as total'),
                ])
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $labels = [];
            $values = [];
            for ($i = 0; $i < $months; $i++) {
                $d  = $start->copy()->addMonthsNoOverflow($i);
                $ym = $d->format('Y-m');
                $labels[] = $d->translatedFormat('M Y');
                $values[] = (float) ($rows[$ym] ?? 0);
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
        $mom = $this->monthOverMonthDelta();

        return [
            'kpis' => [
                'today_revenue'          => $this->todayRevenue(),
                'today_payments'         => $this->todayPayments(),
                'current_month_revenue'  => $mom['current'],
                'previous_month_revenue' => $mom['previous'],
                'month_delta'            => $mom['delta'],
                'month_delta_percent'    => $mom['percent'],
                'receivables'            => $this->receivablesSummary(),
                'alertes_count'          => $this->alertesCount(),
            ],
            'top_clients'     => $this->topClients(5),
            'top_products'    => $this->topProducts(5),
            'recent_invoices' => $this->recentInvoices(8),
            'charts' => [
                'revenue_by_month'    => $this->revenueByMonth(12),
                'revenue_by_category' => $this->revenueByCategory(),
                'revenue_by_user'     => $this->revenueByUser(),
            ],
        ];
    }

    /** Vide tous les caches du dashboard exécutif. */
    public function flushCache(): void
    {
        foreach ([
            'today_revenue', 'today_payments',
            'current_month_revenue', 'previous_month_revenue',
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
