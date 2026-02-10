<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string|max:500',
            'client_phone' => 'nullable|string|max:50',
            'client_ice' => 'required|digits:15',
            'invoice_date' => 'required|date',
            'payment_mode' => 'required|in:CH,ESP,LCN',
            'remise_percentage' => 'nullable|numeric|min:0|max:100',
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
            $montantHT = 0;
            foreach ($validated['items'] as $item) {
                $montantHT += $item['quantity'] * $item['unit_price'];
            }
            
            $remisePercentage = $validated['remise_percentage'] ?? 0;
            $remiseAmount = $montantHT * ($remisePercentage / 100);
            $montantHTAfterRemise = $montantHT - $remiseAmount;
            $tvaRate = 20;
            $tva = $montantHTAfterRemise * ($tvaRate / 100);
            $totalTTC = $montantHTAfterRemise + $tva;
            $invoiceNumber = $this->generateNextInvoiceNumber();
            
            // ✅ Create invoice with user tracking
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => 'manual',
                'client_name' => $validated['client_name'],
                'client_address' => $validated['client_address'],
                'client_phone' => $validated['client_phone'],
                'client_ice' => $validated['client_ice'],
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'],
                'total_ttc' => $totalTTC,
                'tva_rate' => $tvaRate,
                'tva' => $tva,
                'montant_ht' => $montantHT,
                'remise_percentage' => $remisePercentage,
                'remise_amount' => $remiseAmount,
                'amount_in_words' => $this->numberToWords($totalTTC),
                'created_by' => Auth::id(), // ✅ Track who created it
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
                ->with('success', 'Facture manuelle créée avec succès par ' . Auth::user()->name . '!');

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
        $year = date('y');
        
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
        
        $amountInWords = $this->numberToWords($invoice->total_ttc);
        
        $pdf = Pdf::loadView('invoices.pdf-manual', compact('invoice', 'amountInWords'));
        
        return $pdf->download("facture_{$invoice->invoice_number}.pdf");
    }

    /**
     * Show the form for editing an invoice.
     */
    public function edit($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $products = Product::orderBy('name')->get();
        
        return view('invoices.edit', compact('invoice', 'products'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'payment_mode' => 'required|in:CH,ESP,LCN',
            'remise_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Vous devez avoir au moins un produit',
            'items.*.product_id.required' => 'Produit requis',
            'items.*.quantity.required' => 'Quantité requise',
            'items.*.unit_price.required' => 'Prix unitaire requis',
        ]);
        
        DB::beginTransaction();
        
        try {
            $montantHT = 0;
            foreach ($validated['items'] as $item) {
                $montantHT += $item['quantity'] * $item['unit_price'];
            }
            
            $remisePercentage = $validated['remise_percentage'] ?? 0;
            $remiseAmount = $montantHT * ($remisePercentage / 100);
            $montantHTAfterRemise = $montantHT - $remiseAmount;
            $tvaRate = 20;
            $tva = $montantHTAfterRemise * ($tvaRate / 100);
            $totalTTC = $montantHTAfterRemise + $tva;
            
            // ✅ Update invoice with user tracking
            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'],
                'total_ttc' => $totalTTC,
                'tva_rate' => $tvaRate,
                'tva' => $tva,
                'montant_ht' => $montantHT,
                'remise_percentage' => $remisePercentage,
                'remise_amount' => $remiseAmount,
                'amount_in_words' => $this->numberToWords($totalTTC),
                'updated_by' => Auth::id(), // ✅ Track who updated it
            ]);
            
            // Delete old items
            $invoice->items()->delete();
            
            // Create new items
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
                ->route('invoices.index')
                ->with('success', 'Facture modifiée avec succès par ' . Auth::user()->name . '!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }
}