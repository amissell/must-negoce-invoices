<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('items.product')->latest()->get();
        $products = Product::all();

        return view('invoices.index', compact('invoices', 'products'));
    }

    public function store(Request $request)
    {
        // 1️⃣ If montant_global_ttc is submitted
        if ($request->has('montant_global_ttc')) {
            $totalAmount = (float) $request->montant_global_ttc;

            // Create a dummy product just to store the amount
            $items = [
                [
                    'product' => (object)['id' => 0],
                    'quantity' => 1,
                    'unit_price' => $totalAmount,
                    'total' => $totalAmount,
                ]
            ];

            $this->splitIntoMultipleInvoices('divers', $items);

            return redirect()->route('invoices.index')
                ->with('success', 'Facture(s) créée(s) avec succès');
        }

        // 2️⃣ If products are submitted
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $customerName = 'divers';
        $totalAmount = 0;
        $allItems = [];

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = $productData['quantity'];
            $unitPrice = $product->prix_ttc;

            if ($unitPrice === null) {
                return back()->with('error', "Le produit '{$product->name}' n'a pas de prix défini.");
            }

            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $allItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
            ];
        }

        if ($totalAmount < 5000) {
            $this->createSingleInvoice($customerName, $allItems, $totalAmount);
        } else {
            $this->splitIntoMultipleInvoices($customerName, $allItems);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Facture(s) créée(s) avec succès');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'montant_global_ttc' => 'required|numeric|min:0'
        ]);

        $totalTTC = $request->montant_global_ttc;
        
        // IMPORTANT: Each invoice MUST be LESS than 5000 MAD
        $maxInvoiceAmount = 4999; // Maximum per invoice
        $minInvoiceAmount = 3000; // Minimum per invoice
        
        // Calculate how many invoices needed
        $numberOfInvoices = ceil($totalTTC / $maxInvoiceAmount);
        $remainingAmount = $totalTTC;
        
        $createdInvoices = 0;
        
        for ($i = 0; $i < $numberOfInvoices; $i++) {
            if ($remainingAmount <= 0) {
                break;
            }
            
            // Determine amount for this invoice
            if ($i == $numberOfInvoices - 1) {
                // Last invoice gets remaining amount (but MUST be < 5000)
                $invoiceAmount = min($remainingAmount, $maxInvoiceAmount);
            } else {
                // Random amount between 3000 and 4999
                $invoiceAmount = rand($minInvoiceAmount, $maxInvoiceAmount);
                
                // Don't exceed remaining amount
                if ($invoiceAmount > $remainingAmount) {
                    $invoiceAmount = $remainingAmount;
                }
                
                // ENFORCE max limit
                $invoiceAmount = min($invoiceAmount, $maxInvoiceAmount);
            }
            
            // Skip if amount is too small
            if ($invoiceAmount < 100) {
                break;
            }
            
            // Calculate HT and TVA (20%)
            $montantHT = round($invoiceAmount / 1.2, 2);
            $tva = round($invoiceAmount - $montantHT, 2);

            // Generate random date between Jan 1st 2026 and today
            $startDate = \Carbon\Carbon::create(2026, 1, 1);
            $endDate = \Carbon\Carbon::now();
            $randomDate = \Carbon\Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => $randomDate,
                'montant_ht' => $montantHT,
                'tva' => $tva,
                'total_ttc' => $invoiceAmount,
                'is_locked' => true,
                'status' => 'Verrouillée'
            ]);

            // Get random products (2-5 products)
            $numberOfProducts = rand(2, 5);
            $products = Product::inRandomOrder()->limit($numberOfProducts)->get();
            
            if ($products->isEmpty()) {
                $invoice->delete();
                continue;
            }
            
            // Distribute invoice amount across products
            $itemsRemaining = $invoiceAmount;
            $productsCount = $products->count();
            
            foreach ($products as $index => $product) {
                if ($itemsRemaining <= 10) {
                    break;
                }
                
                if ($index == $productsCount - 1) {
                    // Last product gets remaining amount
                    $targetAmount = $itemsRemaining;
                } else {
                    // Random portion between 15% and 40%
                    $portion = rand(15, 40) / 100;
                    $targetAmount = $itemsRemaining * $portion;
                }
                
                // Calculate quantity (use floor to avoid exceeding)
                $quantity = max(1, floor($targetAmount / $product->prix_ttc));
                
                // Calculate actual item total
                $itemTotal = round($quantity * $product->prix_ttc, 2);
                
                // IMPORTANT: Don't exceed remaining amount
                if ($itemTotal > $itemsRemaining) {
                    $quantity = max(1, floor($itemsRemaining / $product->prix_ttc));
                    $itemTotal = round($quantity * $product->prix_ttc, 2);
                }
                
                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->prix_ttc,
                    'total' => $itemTotal
                ]);
                
                $itemsRemaining -= $itemTotal;
            }
            
            $remainingAmount -= $invoiceAmount;
            $createdInvoices++;
        }

        return redirect()->route('invoices.index')
            ->with('success', $createdInvoices . ' Facture(s) créée(s) avec succès! Chaque facture < 5000 MAD');
    }

    private function createSingleInvoice($customerName, $items, $totalTtc)
    {
        $montantHt = round($totalTtc / 1.2, 2);
        $tva = round($totalTtc - $montantHt, 2);

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date'   => now(),
            'montant_ht'     => $montantHt,
            'tva'            => $tva,
            'total_ttc'      => $totalTtc,
            'is_locked'      => true,
            'status'         => 'Verrouillée',
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product']->id,
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total'      => $item['total'],
            ]);
        }
    }

    private function splitIntoMultipleInvoices($customerName, $items)
    {
        $max = 4999; // MUST be less than 5000
        $currentItems = [];
        $currentTotal = 0;

        foreach ($items as $item) {
            if ($currentTotal + $item['total'] > $max) {
                $this->createSingleInvoice($customerName, $currentItems, $currentTotal);
                $currentItems = [];
                $currentTotal = 0;
            }

            $currentItems[] = $item;
            $currentTotal += $item['total'];
        }

        if (!empty($currentItems)) {
            $this->createSingleInvoice($customerName, $currentItems, $currentTotal);
        }
    }

    private function generateInvoiceNumber()
    {
        $year = date('y');

        $last = Invoice::whereYear('created_at', date('Y'))
            ->orderByDesc('id')
            ->first();

        $next = $last
            ? ((int) explode('/', $last->invoice_number)[1]) + 1
            : 1;

        return $year . '/' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function downloadPdf($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $safeNumber = str_replace('/', '-', $invoice->invoice_number);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("facture-$safeNumber.pdf");
    }
}