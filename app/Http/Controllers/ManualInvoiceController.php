<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ManualInvoiceController extends Controller
{
    /**
     * Show the form for creating a manual invoice.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        
        $nextInvoiceNumber = $this->generateNextInvoiceNumber();
        
        return view('invoices.create-manual', compact('products', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created manual invoice.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string|max:500',
            'client_phone' => 'nullable|string|max:50',
            'client_ice' => 'required|digits:15',
            'invoice_date' => 'required|date',
            'payment_mode' => 'required|in:CH,ESP,LCN',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Vous devez ajouter au moins un produit',
            'items.*.product_id.required' => 'Produit requis',
            'items.*.quantity.required' => 'Quantité requise',
            'items.*.unit_price.required' => 'Prix unitaire requis',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $totalTTC = 0;
            foreach ($validated['items'] as $item) {
                $totalTTC += $item['quantity'] * $item['unit_price'];
            }

            $tvaRate = 20; // 20% TVA
            $montantHT = $totalTTC / (1 + ($tvaRate / 100));
            $tva = $totalTTC - $montantHT;

            // Generate invoice number
            $invoiceNumber = $this->generateNextInvoiceNumber();

            // Create the invoice - FIX: client_ice was using validation rule instead of value
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => 'manual',
                'client_name' => $validated['client_name'],
                'client_address' => $validated['client_address'],
                'client_phone' => $validated['client_phone'],
                'client_ice' => $validated['client_ice'], // FIXED: was 'required|digits:15'
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'],
                'total_ttc' => $totalTTC,
                'tva_rate' => $tvaRate,
                'tva' => $tva,
                'montant_ht' => $montantHT,
                'amount_in_words' => $this->numberToWords($totalTTC),
            ]);

            // Create invoice items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('invoices.index', $invoice)
                ->with('success', 'Facture manuelle créée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la facture: ' . $e->getMessage());
        }
    }

    /**
     * Generate the next invoice number.
     */
    private function generateNextInvoiceNumber(): string
    {
        $year = date('y'); // Last 2 digits of year
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $year . '/%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(explode('/', $lastInvoice->invoice_number)[1]);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . '/' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Convert number to French words.
     */
    private function numberToWords(float $number): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $intPart = floor($number);
            $decPart = round(($number - $intPart) * 100);
            
            $words = strtoupper($formatter->format($intPart));
            $result = $words . ' DHS';
            
            if ($decPart > 0) {
                $centsWords = strtoupper($formatter->format($decPart));
                $result .= ' ET ' . $centsWords . ' CENTIMES';
            }
            
            return $result;
        }
        
        return number_format($number, 2) . ' DHS';
    }

    /**
     * Get product details via AJAX.
     */
    public function getProduct(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'unit_price' => $product->prix_ttc,
        ]);
    }
    
    public function show($id)
    {
        return redirect()->route('invoices.index');
    }

    /**
     * Download PDF for manual invoice
     */
    public function downloadPDF($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        
        // Convert amount to words in French
        $amountInWords = $this->numberToWords($invoice->total_ttc);
        
        // Use pdf-manual.blade.php template for manual invoices
        $pdf = Pdf::loadView('invoices.pdf-manual', compact('invoice', 'amountInWords'));
        
        return $pdf->download("facture_{$invoice->invoice_number}.pdf");
    }
}