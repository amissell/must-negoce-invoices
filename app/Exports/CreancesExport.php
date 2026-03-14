// app/Exports/CreancesExport.php
<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreancesExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data) { $this->data = $data; }

    public function headings(): array
    {
        return ['Client', 'Nb Factures', '0-30j', '31-60j', '60j+', 'Total Impayé', 'Risque'];
    }

    public function collection()
    {
        return collect($this->data)->map(fn($d) => [
            $d['client']->name,
            $d['nb_factures'],
            number_format($d['j30'], 2),
            number_format($d['j60'], 2),
            number_format($d['j90_plus'], 2),
            number_format($d['total_impaye'], 2),
            $d['risque'],
        ]);
    }
}

// In CreanceController, add:
public function exportExcel() {
    // ... same logic as index to build $clients
    return \Excel::download(new CreancesExport($clients), 'creances_' . now()->format('Y-m-d') . '.xlsx');
}

public function exportPdf() {
    // ... same logic as index
    $pdf = \PDF::loadView('creances.pdf', compact('clients', 'totalCreances', 'total30', 'total60', 'total90'));
    return $pdf->download('creances_' . now()->format('Y-m-d') . '.pdf');
}
