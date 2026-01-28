<?php
// app/Services/InvoicePdfService.php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load('items', 'client');
        
        $data = [
            'invoice' => $invoice,
            'company' => [
                'name' => env('COMPANY_NAME', 'Sté. MUST-NEGOCE'),
                'slogan' => 'CONSULTING - FORMATION - NEGOCE',
                'address' => env('COMPANY_ADDRESS', 'Mourabitoun commune Saada Marrakech'),
                'phone' => env('COMPANY_PHONE', '06 62482531'),
                'rc' => env('COMPANY_RC', '86613'),
                'if' => env('COMPANY_IF', '25055643'),
                'ice' => env('COMPANY_ICE', '001996252000022'),
                'tp' => env('COMPANY_TP', '47960328'),
            ],
        ];
        
        return Pdf::loadView('invoices.pdf', $data)
            ->setPaper('a4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
    }

    public function download(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($invoice);
        $filename = "facture_{$invoice->invoice_number}.pdf";
        $filename = str_replace('/', '-', $filename);
        
        return $pdf->download($filename);
    }

    public function stream(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($invoice);
        $filename = "facture_{$invoice->invoice_number}.pdf";
        $filename = str_replace('/', '-', $filename);
        
        return $pdf->stream($filename);
    }

    public function save(Invoice $invoice, string $disk = 'local'): string
    {
        $pdf = $this->generate($invoice);
        $filename = "invoices/facture_{$invoice->invoice_number}.pdf";
        $filename = str_replace('/', '-', $filename);
        
        Storage::disk($disk)->put($filename, $pdf->output());
        
        return $filename;
    }
}