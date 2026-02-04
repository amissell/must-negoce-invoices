<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\NumberToFrenchWords;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('items')->latest('invoice_date');

        // Filter by type
        if ($request->has('type')) {
            if ($request->type === 'manual') {
                $query->manual();
            } elseif ($request->type === 'automatic') {
                $query->automatic();
            }
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15);

        $totalInvoices = Invoice::count();
        $manualCount = Invoice::manual()->count();
        $automaticCount = Invoice::automatic()->count();
        $products = Product::all();

        return view('invoices.index', compact(
            'invoices',
            'totalInvoices',
            'manualCount',
            'automaticCount',
            'products'
        ));
    }

    public function store(Request $request)
    {
        if ($request->has('montant_global_ttc')) {
            $totalAmount = (float) $request->montant_global_ttc;

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
        $maxInvoiceAmount = 4999;
        $minInvoiceAmount = 3000;

        $numberOfInvoices = ceil($totalTTC / $maxInvoiceAmount);
        $remainingAmount = $totalTTC;
        $createdInvoices = 0;

        for ($i = 0; $i < $numberOfInvoices; $i++) {
            if ($remainingAmount <= 0) break;

            $invoiceAmount = ($i === $numberOfInvoices - 1)
                ? min($remainingAmount, $maxInvoiceAmount)
                : rand($minInvoiceAmount, min($maxInvoiceAmount, $remainingAmount));

            if ($invoiceAmount < 100) break;

            $montantHT = round($invoiceAmount / 1.2, 2);
            $tva = round($invoiceAmount - $montantHT, 2);

            $startDate = \Carbon\Carbon::create(2026, 1, 1);
            $endDate = \Carbon\Carbon::now();
            $randomDate = \Carbon\Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => $randomDate,
                'montant_ht' => $montantHT,
                'tva' => $tva,
                'total_ttc' => $invoiceAmount,
                'is_locked' => true,
                'type' => 'automatic',
                'client_name' => 'divers',
            ]);

            $numberOfProducts = rand(300, 400);
            $products = Product::inRandomOrder()->limit($numberOfProducts)->get();

            if ($products->isEmpty()) {
                $invoice->delete();
                continue;
            }

            $itemsRemaining = $invoiceAmount;
            foreach ($products as $product) {
                if ($itemsRemaining <= 0) break;

                $quantity = rand(6, 24);
                $itemTotal = round($quantity * $product->prix_ttc, 2);

                while ($itemTotal > $itemsRemaining && $quantity > 1) {
                    $quantity--;
                    $itemTotal = round($quantity * $product->prix_ttc, 2);
                }

                if ($itemTotal <= 0) continue;

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
            'invoice_date' => now(),
            'montant_ht' => $montantHt,
            'tva' => $tva,
            'total_ttc' => $totalTtc,
            'is_locked' => true,
            'type' => 'automatic',
            'client_name' => 'divers',
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }
    }

    private function splitIntoMultipleInvoices($customerName, $items)
    {
        $max = 4999;
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

    /**
     * Download PDF - automatically selects the correct template based on invoice type
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $safeNumber = str_replace('/', '-', $invoice->invoice_number);
        $amountInWords = NumberToFrenchWords::convert($invoice->total_ttc);

        // Select the appropriate template based on invoice type
        $template = $invoice->type === 'manual' 
            ? 'invoices.pdf-manual'    // For manual invoices with full client info
            : 'invoices.pdf';           // For automatic invoices (simple version)

        $pdf = Pdf::loadView($template, compact('invoice', 'amountInWords'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("FACTURE_$safeNumber.pdf");
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
            'Montant TTC'
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

        return view('invoices.statistics', compact(
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
}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    