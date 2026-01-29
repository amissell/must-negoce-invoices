<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('items.product')
            ->orderBy('invoice_date', 'desc')
            ->get();
        
        $products = Product::all();
        
        return view('invoices.index', compact('invoices', 'products'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'montant_global_ttc' => 'required|numeric|min:0'
        ]);

        $totalTTC = $request->montant_global_ttc;
        
        // Calculate HT and TVA (20%)
        $montantHT = $totalTTC / 1.2;
        $tva = $totalTTC - $montantHT;

        // Generate random date between Jan 1st 2026 and today
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now();
        $randomDate = Carbon::createFromTimestamp(
            rand($startDate->timestamp, $endDate->timestamp)
        );

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => $randomDate,
            'montant_ht' => $montantHT,
            'tva' => $tva,
            'total_ttc' => $totalTTC,
            'is_locked' => true,
            'status' => 'Verrouillée'
        ]);

        // Get random products and quantities
        $products = Product::inRandomOrder()->limit(rand(2, 5))->get();
        
        $remainingAmount = $totalTTC;
        $itemsCount = $products->count();
        
        foreach ($products as $index => $product) {
            if ($index == $itemsCount - 1) {
                // Last item gets remaining amount
                $itemTotal = $remainingAmount;
            } else {
                // Random portion of remaining amount
                $portion = rand(10, 40) / 100;
                $itemTotal = $remainingAmount * $portion;
            }
            
            // Calculate quantity based on unit price
            $quantity = max(1, round($itemTotal / $product->prix_ttc));
            $itemTotal = $quantity * $product->prix_ttc;
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->prix_ttc,
                'total' => $itemTotal
            ]);
            
            $remainingAmount -= $itemTotal;
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Facture générée avec succès!');
    }
    public function downloadPdf($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $safeNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('facture_' . $safeNumber . '.pdf');
        }


    public function show($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }
}