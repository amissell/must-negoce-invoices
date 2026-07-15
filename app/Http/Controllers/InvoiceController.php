<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GenerateInvoiceRequest;
use App\Http\Requests\StoreInvoiceProductsRequest;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\PdfService;
use App\Helpers\NumberToFrenchWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;
    private PdfService $pdfService;

    public function __construct(InvoiceService $invoiceService, PdfService $pdfService)
    {
        $this->invoiceService = $invoiceService;
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with('creator')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name',  'like', "%{$search}%")
                  ->orWhere('client_ice',   'like', "%{$search}%");
            });
        }

        $invoices       = $query->paginate(15)->withQueryString();
        $totalInvoices  = Invoice::count();
        $automaticCount = Invoice::where('type', 'automatic')->count();
        $manualCount    = Invoice::where('type', 'manual')->count();
        $clients        = \App\Models\Client::orderBy('nom')->get(['id', 'nom']);

        return view('pages.invoices.index', compact(
            'invoices',
            'totalInvoices',
            'automaticCount',
            'manualCount',
            'clients'
        ));
    }

    public function store(StoreInvoiceProductsRequest $request)
    {
        if ($request->has('montant_global_ttc')) {
            $this->invoiceService->createInvoicesFromAmount((float) $request->montant_global_ttc, 'divers', Auth::id());

            return redirect()->route('invoices.index')
                ->with('success', 'Facture(s) créée(s) avec succès');
        }

        $validated = $request->validated();

        try {
            $this->invoiceService->createInvoicesFromProducts($validated['products'], 'divers', Auth::id());

            return redirect()->route('invoices.index')
                ->with('success', 'Facture(s) créée(s) avec succès');
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function generate(GenerateInvoiceRequest $request)
    {
        $result = $this->invoiceService->generateAutomaticInvoices((float) $request->montant_global_ttc, Auth::id());

        return redirect()->route('invoices.index')
            ->with('success', $result['createdInvoices'] . ' Facture(s) créée(s) avec succès! Total généré: ' . number_format($result['totalGenerated'], 2) . ' MAD / ' . number_format($result['requestedAmount'], 2) . ' MAD demandé');
    }

    public function downloadPdf($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $safeNumber = str_replace('/', '-', $invoice->invoice_number);
        $amountInWords = NumberToFrenchWords::convert($invoice->total_ttc);

        $template = $invoice->type === 'manual'
            ? 'pages.invoices.pdf-manual'
            : 'pages.invoices.pdf';

        return $this->pdfService->download($template, compact('invoice', 'amountInWords'), "FACTURE_$safeNumber.pdf");
    }

    public function export(Request $request)
    {
        $query = Invoice::with('items');

        if ($request->has('type')) {
            if ($request->type === 'manual') $query->manual();
            elseif ($request->type === 'automatic') $query->automatic();
        }

        $invoices = $query->get();

        $filename = 'invoices_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'N° Facture',
            'Type',
            'Client',
            'Téléphone',
            'Email',
            'Date',
            'Mode Paiement',
            'Montant HT',
            'TVA',
            'Montant TTC',
            'Créé par',
        ]);

        foreach ($invoices as $invoice) {
            fputcsv($output, [
                $invoice->invoice_number,
                $invoice->type,
                $invoice->client_name,
                $invoice->client_phone ?? '',
                $invoice->client_email ?? '',
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->payment_mode ?? '',
                number_format($invoice->montant_ht, 2),
                number_format($invoice->tva, 2),
                number_format($invoice->total_ttc, 2),
                $invoice->creator_name,
            ]);
        }

        fclose($output);
        exit;
    }

    public function statistics()
    {
        $totalInvoices = Invoice::count();
        $manualInvoices = Invoice::manual()->count();
        $automaticInvoices = Invoice::automatic()->count();

        $totalRevenue = Invoice::sum('total_ttc');
        $manualRevenue = Invoice::manual()->sum('total_ttc');
        $automaticRevenue = Invoice::automatic()->sum('total_ttc');

        $monthlyRevenue = Invoice::whereMonth('invoice_date', date('m'))
            ->whereYear('invoice_date', date('Y'))
            ->sum('total_ttc');

        return view('pages.invoices.statistics', compact(
            'totalInvoices',
            'manualInvoices',
            'automaticInvoices',
            'totalRevenue',
            'manualRevenue',
            'automaticRevenue',
            'monthlyRevenue'
        ));
    }

    public function show($id)
    {
        return redirect()->route('invoices.index');
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Facture supprimée avec succès!');
    }
}
