<?php
declare(strict_types=1);
namespace App\Http\Controllers;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Http\Requests\StoreInvoiceProductsRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PdfService;
use App\Helpers\NumberToFrenchWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\Vendeur;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesComptaExport;

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
    // Check authorization
    $this->authorize('viewAny', Invoice::class);

    /** @var \App\Models\User $user */
    $user = Auth::user();

    $query = Invoice::with(['creator', 'client', 'vendeur'])->withSum('paiements as montant_regle_sum', 'paiement_invoice.montant_affecte')->latest();

    // Apply row-level security: regular users see only their own invoices
    if (! $user->isAdmin() && ! $user->isSuperviseur()) {
        $query->where('created_by', $user->id);
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('client_id')) {
        $query->where('client_id', $request->client_id);
    }
    if ($request->filled('vendeur_id')) $query->where('vendeur_id', $request->vendeur_id);
    if ($request->filled('delivery_status')) {
        $request->delivery_status === 'envoyee'
            ? $query->where('delivery_status', '!=', 'non_envoyee')
            : $query->where('delivery_status', $request->delivery_status);
    }
    if ($request->filled('compta')) $request->compta === 'remis' ? $query->whereNotNull('remis_compta_at') : $query->whereNull('remis_compta_at');
    if ($request->filled('month')) $query->whereMonth('invoice_date', $request->month);
    if ($request->filled('year')) $query->whereYear('invoice_date', $request->year);
    if ($request->filled('payment_status')) {
        $paid = '(select coalesce(sum(montant_affecte), 0) from paiement_invoice where paiement_invoice.invoice_id = invoices.id)';
        if ($request->payment_status === 'non_payee') $query->whereRaw("{$paid} = 0");
        if ($request->payment_status === 'payee') $query->whereRaw("{$paid} >= invoices.total_ttc");
        if ($request->payment_status === 'partiellement_payee') $query->whereRaw("{$paid} > 0 and {$paid} < invoices.total_ttc");
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhere('client_name', 'like', "%{$search}%")
              ->orWhere('client_ice', 'like', "%{$search}%");
        });
    }

    $invoices = $query->paginate(15)->withQueryString();

    $totalInvoices = Invoice::count();
    $automaticCount = Invoice::where('type', 'automatic')->count();
    $manualCount = Invoice::where('type', 'manual')->count();

    $clients = \App\Models\Client::orderBy('nom')->get(['id', 'nom']);
    $vendeurs = Vendeur::orderBy('nom_complet')->get(['id', 'nom_complet']);

    return view('pages.invoices.index', compact(
        'invoices',
        'totalInvoices',
        'automaticCount',
        'manualCount',
        'clients', 'vendeurs'
    ));
}

    public function bulkUpdate(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);
        $data = $request->validate(['invoice_ids' => 'required|array|min:1', 'invoice_ids.*' => 'integer|exists:invoices,id', 'vendeur_id' => 'nullable|exists:vendeurs,id', 'delivery_status' => 'nullable|in:non_envoyee,email,main_propre', 'remis_compta' => 'nullable|boolean']);
        $attributes = [];
        if ($request->filled('vendeur_id')) $attributes['vendeur_id'] = $data['vendeur_id'];
        if ($request->filled('delivery_status')) { $attributes['delivery_status'] = $data['delivery_status']; $attributes['delivered_at'] = $data['delivery_status'] === 'non_envoyee' ? null : now(); }
        if (array_key_exists('remis_compta', $data)) $attributes['remis_compta_at'] = $data['remis_compta'] ? now()->toDateString() : null;
        if ($attributes) Invoice::whereIn('id', $data['invoice_ids'])->update($attributes);
        return back()->with('success', count($data['invoice_ids']).' facture(s) mise(s) à jour.');
    }

    public function updateTracking(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $data = $request->validate([
            'vendeur_id' => 'nullable|exists:vendeurs,id',
            'delivery_status' => 'required|in:non_envoyee,email,main_propre',
        ]);
        $invoice->update([
            'vendeur_id' => $data['vendeur_id'],
            'delivery_status' => $data['delivery_status'],
            'delivered_at' => $data['delivery_status'] === 'non_envoyee' ? null : now(),
        ]);
        return back()->with('success', 'Suivi de la facture mis à jour.');
    }

    public function exportSelection(Request $request)
    {
        $data = $request->validate(['invoice_ids' => 'required|array|min:1', 'invoice_ids.*' => 'integer|exists:invoices,id']);
        $invoices = Invoice::with(['client', 'vendeur'])->whereIn('id', $data['invoice_ids'])->get();
        return Excel::download(new InvoicesComptaExport($invoices), 'remise-compta-'.now()->format('Y-m-d').'.xlsx');
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

        // Check authorization
        $this->authorize('view', $invoice);
        $safeNumber = str_replace('/', '-', $invoice->invoice_number);
        $amountInWords = NumberToFrenchWords::convert($invoice->total_ttc);

        $company  = CompanySetting::current();
        $template = in_array($company->default_pdf_template, ['classic', 'modern'], true)
            ? $company->default_pdf_template
            : 'classic';

        $variant = $invoice->type === 'manual' ? 'manual' : 'auto';
        $view    = "pages.invoices.templates.$template.$variant";

        // Backward compatibility: fall back to legacy blade if template is missing.
        if (! View::exists($view)) {
            $view = $invoice->type === 'manual'
                ? 'pages.invoices.pdf-manual'
                : 'pages.invoices.pdf';
        }

        return $this->pdfService->download($view, compact('invoice', 'amountInWords'), "FACTURE_$safeNumber.pdf");
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
            'N° Facture', 'Type', 'Client', 'Téléphone', 'Email', 'Date',
            'Mode Paiement', 'Montant HT', 'TVA', 'Montant TTC', 'Créé par',
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
            'totalInvoices', 'manualInvoices', 'automaticInvoices',
            'totalRevenue', 'manualRevenue', 'automaticRevenue', 'monthlyRevenue'
        ));
    }
    public function show($id)
    {
        return redirect()->route('invoices.index');
    }
    public function destroy(Request $request, $id)
{
    $invoice = Invoice::findOrFail($id);

    $this->authorize('delete', $invoice);

    $invoice->items()->delete();
    $invoice->delete();

    if ($request->expectsJson()) {
        return response()->json(['message' => 'Facture supprimée avec succès!']);
    }

    return redirect()->route('invoices.index')
        ->with('success', 'Facture supprimée avec succès!');
}
}
