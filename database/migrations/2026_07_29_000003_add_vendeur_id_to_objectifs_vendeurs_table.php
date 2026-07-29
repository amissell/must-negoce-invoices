<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('objectifs_vendeurs') || ! Schema::hasTable('vendeurs')) {
            return;
        }

        if (! Schema::hasColumn('objectifs_vendeurs', 'vendeur_id')) {
            Schema::table('objectifs_vendeurs', function (Blueprint $table) {
                $table->foreignId('vendeur_id')->nullable()->after('vendeur_nom')
                    ->constrained('vendeurs')->nullOnDelete();
                $table->index(['vendeur_id', 'mois', 'annee']);
            });
        }

        DB::transaction(function (): void {
            $vendeursByName = DB::table('vendeurs')
                ->select(['id', 'nom_complet'])
                ->get()
                ->mapWithKeys(fn (object $vendeur): array => [
                    $this->normalizeName($vendeur->nom_complet) => $vendeur->id,
                ]);

            DB::table('objectifs_vendeurs')
                ->select(['id', 'vendeur_nom'])
                ->whereNull('vendeur_id')
                ->orderBy('id')
                ->each(function (object $objectif) use ($vendeursByName): void {
                    $vendeurId = $vendeursByName->get($this->normalizeName($objectif->vendeur_nom));

                    if ($vendeurId !== null) {
                        DB::table('objectifs_vendeurs')
                            ->where('id', $objectif->id)
                            ->whereNull('vendeur_id')
                            ->update(['vendeur_id' => $vendeurId]);
                    }
                });
        });

        $total = DB::table('objectifs_vendeurs')->count();
        $matched = DB::table('objectifs_vendeurs')->whereNotNull('vendeur_id')->count();
        $unmatchedNames = DB::table('objectifs_vendeurs')
            ->whereNull('vendeur_id')
            ->distinct()
            ->orderBy('vendeur_nom')
            ->pluck('vendeur_nom')
            ->values()
            ->all();
        $unmatched = count($unmatchedNames) === 0
            ? 0
            : DB::table('objectifs_vendeurs')->whereNull('vendeur_id')->count();

        $report = sprintf(
            'Objectifs vendeurs backfill: total=%d, matched=%d, null=%d, unmatched_names=[%s]',
            $total,
            $matched,
            $unmatched,
            implode(', ', $unmatchedNames),
        );

        Log::warning($report);
        fwrite(STDOUT, "{$report}\n");
    }

    public function down(): void
    {
        if (! Schema::hasTable('objectifs_vendeurs') || ! Schema::hasColumn('objectifs_vendeurs', 'vendeur_id')) {
            return;
        }

        Schema::table('objectifs_vendeurs', function (Blueprint $table) {
            $table->dropForeign(['vendeur_id']);
            $table->dropIndex(['vendeur_id', 'mois', 'annee']);
            $table->dropColumn('vendeur_id');
        });
    }

    private function normalizeName(?string $name): string
    {
        return Str::of((string) $name)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/u', ' ')
            ->toString();
    }
};
