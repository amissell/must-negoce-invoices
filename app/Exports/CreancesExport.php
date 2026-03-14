<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreancesExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return ['Client', 'Nb Factures', '0-30j', '31-60j', '60j+', 'Total Impayé', 'Risque'];
    }

    public function collection()
    {
        return collect($this->data)->map(fn($d) => [
            $d['client']->nom,
            $d['nb_factures'],
            number_format($d['j30'], 2),
            number_format($d['j60'], 2),
            number_format($d['j90_plus'], 2),
            number_format($d['total_impaye'], 2),
            $d['risque'],
        ]);
    }
}
