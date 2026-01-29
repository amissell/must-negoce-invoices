<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('client', 'items')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc')
            ->paginate(20);

        $stats = [
            'total_invoices' => Invoice::count(),
            'total_amount' => Invoice::sum('total'),
            'total_products' => Product::count(),
        ];

        return view('dashboard.index', compact('invoices', 'stats'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'global_total' => 'required|numeric|min:100|max:10000000',
        ], [
            'global_total.required' => 'Le montant global est requis.',
            'global_total.numeric' => 'Le montant doit être un nombre.',
            'global_total.min' => 'Le montant minimum est de 100 MAD.',
            'global_total.max' => 'Le montant maximum est de 10 000 000 MAD.',
        ]);

        try {
            $generator = new InvoiceGeneratorService();
            $invoices = $generator->generateFromTotal($request->global_total);

            return redirect()->route('dashboard')
                ->with('success', count($invoices) . ' facture(s) générée(s) avec succès.')
                ->with('generated_batch', $invoices[0]->generation_batch ?? null);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items.product');
        return view('dashboard.show', compact('invoice'));
    }
}
