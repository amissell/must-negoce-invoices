<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = ['client_id', 'invoice_id', 'montant', 'montant_total', 'date_paiement', 'date_encaissement', 'mode', 'reference', 'banque', 'notes'];

    protected $casts = ['montant' => 'decimal:2', 'montant_total' => 'decimal:2', 'date_paiement' => 'date', 'date_encaissement' => 'date'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function invoices(): BelongsToMany { return $this->belongsToMany(Invoice::class, 'paiement_invoice')->withPivot('montant_affecte')->withTimestamps(); }
    public function getMontantAffecteAttribute(): float { return (float) $this->invoices()->sum('paiement_invoice.montant_affecte'); }
    public function getEcartAttribute(): float { return round((float) ($this->montant_total ?? $this->montant) - $this->montant_affecte, 2); }
}
