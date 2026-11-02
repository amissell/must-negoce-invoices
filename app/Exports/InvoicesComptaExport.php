<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesComptaExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $invoices) {}
    public function headings(): array { return ['N° facture', 'Client', 'Date', 'Montant TTC', 'Vendeur', 'Envoi client', 'Remise à la compta']; }
    public function collection(): Collection
    {
        return $this->invoices->map(fn ($i) => [$i->invoice_number, $i->client?->nom ?? $i->client_name, optional($i->invoice_date)->format('d/m/Y'), (float) $i->total_ttc, $i->vendeur?->nom_complet ?? 'Non assigné', $i->delivery_status, optional($i->remis_compta_at)->format('d/m/Y')]);
    }
}
