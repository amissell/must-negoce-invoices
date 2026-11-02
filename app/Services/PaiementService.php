<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaiementService
{
    public function save(array $data, ?Paiement $paiement = null): Paiement
    {
        $allocations = collect($data['allocations'] ?? [])->filter(fn ($allocation) => (float) ($allocation['montant_affecte'] ?? 0) > 0)->values();
        $total = round((float) $data['montant_total'], 2);
        $allocated = round((float) $allocations->sum('montant_affecte'), 2);
        if ($allocated > $total) {
            throw ValidationException::withMessages(['allocations' => "Le montant affecté ({$allocated}) dépasse le paiement ({$total})."]);
        }

        return DB::transaction(function () use ($data, $paiement, $allocations, $total) {
            $invoiceIds = $allocations->pluck('invoice_id')->all();
            $invoices = Invoice::whereIn('id', $invoiceIds)->get()->keyBy('id');
            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation['invoice_id']);
                if (! $invoice || (int) $invoice->client_id !== (int) $data['client_id']) {
                    throw ValidationException::withMessages(['allocations' => 'Les factures affectées doivent appartenir au client sélectionné.']);
                }
                $alreadyPaid = (float) $invoice->paiements()->sum('paiement_invoice.montant_affecte');
                if ($paiement) $alreadyPaid -= (float) $paiement->invoices()->whereKey($invoice->id)->sum('paiement_invoice.montant_affecte');
                if (round($alreadyPaid + (float) $allocation['montant_affecte'], 2) > (float) $invoice->total_ttc) {
                    throw ValidationException::withMessages(['allocations' => "L'affectation dépasse le reste dû pour {$invoice->invoice_number}."]);
                }
            }
            $attributes = collect($data)->except('allocations')->merge(['montant' => $total])->all();
            $paiement = $paiement ? tap($paiement)->update($attributes) : Paiement::create($attributes);
            $paiement->invoices()->sync($allocations->mapWithKeys(fn ($a) => [$a['invoice_id'] => ['montant_affecte' => $a['montant_affecte']]])->all());
            return $paiement;
        });
    }
}
