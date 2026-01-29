<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load('client', 'items.product');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->getCompanyInfo(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'facture_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }

    public function view(Invoice $invoice)
    {
        $invoice->load('client', 'items.product');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->getCompanyInfo(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream();
    }

    private function getCompanyInfo(): array
    {
        return [
            'name' => config('app.company_name', 'MUST NÉGOCE'),
            'address' => config('app.company_address', '123 Boulevard Mohammed V'),
            'city' => config('app.company_city', 'Casablanca, Maroc'),
            'phone' => config('app.company_phone', '+212 5XX-XXXXXX'),
            'email' => config('app.company_email', 'contact@mustnegoce.ma'),
            'ice' => config('app.company_ice', '00000000000000'),
            'rc' => config('app.company_rc', 'XXXXX'),
            'if' => config('app.company_if', 'XXXXXXXX'),
            'patente' => config('app.company_patente', 'XXXXXXXX'),
            'cnss' => config('app.company_cnss', 'XXXXXXXX'),
            'bank_name' => config('app.company_bank_name', 'Banque Populaire'),
            'bank_rib' => config('app.company_bank_rib', 'XXXX XXXX XXXX XXXX XXXX XX'),
        ];
    }
}
