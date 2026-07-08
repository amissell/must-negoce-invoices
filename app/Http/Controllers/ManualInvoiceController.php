<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreManualInvoiceRequest;
use App\Http\Requests\UpdateManualInvoiceRequest;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\PdfService;

class ManualInvoiceController extends Controller
{
    private InvoiceService $invoiceService;
    private PdfService $pdfService;

    public function __construct(InvoiceService $invoiceService, PdfService $pdfService)
    {
        $this->invoiceService = $invoiceService;
        $this->pdfService = $pdfService;
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $nextInvoiceNumber = $this->invoiceService->generateNextInvoiceNumber();

        return view('invoices.create-manual', compact('products', 'nextInvoiceNumber'));
    }

    public function store(StoreManualInvoiceRequest $request)
    {
        try {
            $this->invoiceService->createManualInvoice($request->validated(), auth()->id());

            return redirect()->route('invoices.index')
                ->with('success', 'Facture créée');
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function update(UpdateManualInvoiceRequest $request, int $id)
    {
        $invoice = Invoice::findOrFail($id);

        try {
            $this->invoiceService->updateManualInvoice($invoice, $request->validated(), auth()->id());

            return redirect()->route('invoices.index')
                ->with('success', 'Facture modifiée');
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function getProduct(Product $product)
    {
        return response()->json([
            'id'         => $product->id,
            'name'       => $product->name,
            'unit_price' => $product->prix_ttc,
        ]);
    }

    public function show($id)
    {
        return redirect()->route('invoices.index');
    }

    public function downloadPDF($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $amountInWords = $this->invoiceService->numberToWords($invoice->total_ttc);

        return $this->pdfService->download('invoices.pdf-manual', compact('invoice', 'amountInWords'), "facture_{$invoice->invoice_number}.pdf");
    }

    public function edit(int $id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $products = Product::orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'products'));
    }
}
