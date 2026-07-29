<?php

namespace Tests\Feature;

use App\Models\ObjectifVendeur;
use App\Models\Vendeur;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StepZeroObjectifVendeurBackfillTest extends TestCase
{
    public function test_backfill_normalizes_names_leaves_orphans_null_and_is_reversible(): void
    {
        $migration = require database_path('migrations/2026_07_29_000003_add_vendeur_id_to_objectifs_vendeurs_table.php');
        $migration->down();

        $vendeur = Vendeur::create(['nom_complet' => 'Nour  Eddine']);
        $matched = ObjectifVendeur::create(['vendeur_nom' => '  nour eddine  ', 'mois' => 7, 'annee' => 2026]);
        $unmatched = ObjectifVendeur::create(['vendeur_nom' => 'Nom introuvable', 'mois' => 8, 'annee' => 2026]);

        $migration->up();

        $this->assertSame($vendeur->id, $matched->fresh()->vendeur_id);
        $this->assertNull($unmatched->fresh()->vendeur_id);
        $this->assertTrue($matched->fresh()->vendeur->is($vendeur));
        $this->assertTrue($vendeur->fresh()->objectifs->contains($matched->fresh()));

        $migration->down();
        $this->assertFalse(Schema::hasColumn('objectifs_vendeurs', 'vendeur_id'));

        $migration->up();
        $this->assertTrue(Schema::hasColumn('objectifs_vendeurs', 'vendeur_id'));
    }
}
