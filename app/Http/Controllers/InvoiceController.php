<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function generate()
    {
        // Generate invoice number
        $invoiceNumber = $this->invoiceService->generateInvoiceNumber();

        return response()->json([
            'invoice_number' => $invoiceNumber
        ]);
    }
}
