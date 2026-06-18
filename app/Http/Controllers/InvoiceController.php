<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\NumberToFrenchWords;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
{
    $query = Invoice::with('creator')->latest();

    // ── Filtre par type ──────────────────────────────────────
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // ── Filtre par client ────────────────────────────────────
    if ($request->filled('client_id')) {
        $query->where('client_id', $request->client_id);
    }

    // ── Recherche par N° facture, client, ICE ───────────────
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

    return view('invoices.index', compact(
        'invoices',
        'totalInvoices',
        'automaticCount',
        'manualCount',
        'clients'
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

        $totalTTC = (float) $request->montant_global_ttc;
        $maxInvoiceAmount = 4999;
        $minInvoiceAmount = 3000;

        $remainingAmount = $totalTTC;
        $createdInvoices = 0;
        $totalGenerated = 0;

        while ($remainingAmount > 50) {
            
            if ($remainingAmount <= $maxInvoiceAmount) {
                $targetInvoiceAmount = $remainingAmount;
            } else {
                $targetInvoiceAmount = rand($minInvoiceAmount, $maxInvoiceAmount);
            }

            if ($targetInvoiceAmount < 100) break;

            $montantHT = round($targetInvoiceAmount / 1.2, 2);
            $tva = round($targetInvoiceAmount - $montantHT, 2);

            $startDate = \Carbon\Carbon::create(2026, 1, 1);
            $endDate = \Carbon\Carbon::now();
            $randomDate = \Carbon\Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));

            // ✅ Add user tracking
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => $randomDate,
                'montant_ht' => $montantHT,
                'tva' => $tva,
                'total_ttc' => $targetInvoiceAmount,
                'is_locked' => true,
                'type' => 'automatic',
                'client_name' => 'divers',
                'created_by' => Auth::id(), // ✅ Track who created it
            ]);

            $numberOfProducts = rand(300, 400);
            $products = Product::inRandomOrder()->limit($numberOfProducts)->get();

            if ($products->isEmpty()) {
                $invoice->delete();
                break;
            }

            $actualInvoiceTotal = 0;
            $itemsAdded = 0;
            $itemsRemaining = $targetInvoiceAmount;

            foreach ($products as $product) {
                if ($itemsRemaining <= 1) break;

                $quantity = rand(6, 24);
                $itemTotal = round($quantity * $product->prix_ttc, 2);

                while ($itemTotal > $itemsRemaining && $quantity > 1) {
                    $quantity--;
                    $itemTotal = round($quantity * $product->prix_ttc, 2);
                }

                if ($itemTotal <= 0 || $itemTotal > $itemsRemaining) continue;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->prix_ttc,
                    'total' => $itemTotal
                ]);

                $actualInvoiceTotal += $itemTotal;
                $itemsRemaining -= $itemTotal;
                $itemsAdded++;
            }

            if ($itemsAdded === 0 || $actualInvoiceTotal < 100) {
                $invoice->delete();
                break;
            }

            $actualMontantHT = round($actualInvoiceTotal / 1.2, 2);
            $actualTva = round($actualInvoiceTotal - $actualMontantHT, 2);
            
            $invoice->update([
                'montant_ht' => $actualMontantHT,
                'tva' => $actualTva,
                'total_ttc' => $actualInvoiceTotal,
                'updated_by' => Auth::id(), // ✅ Track who updated it
            ]);

            $remainingAmount -= $actualInvoiceTotal;
            $totalGenerated += $actualInvoiceTotal;
            $createdInvoices++;

            if ($createdInvoices >= 100) {
                break;
            }
        }

        return redirect()->route('invoices.index')
            ->with('success', $createdInvoices . ' Facture(s) créée(s) avec succès! Total généré: ' . number_format($totalGenerated, 2) . ' MAD / ' . number_format($totalTTC, 2) . ' MAD demandé');
    }

    /**
     * ✅ Créer une facture unique avec tracking utilisateur
     */
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
            'client_name' => $customerName,
            'created_by' => Auth::id(), // ✅ Track creator
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product']->id ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }
    }

    /**
     * ✅ Diviser en plusieurs factures
     */
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

    /**
     * ✅ Générer le numéro de facture
     */
    private function generateInvoiceNumber()
    {
        $year = date('y');
        $currentYear = date('Y');

        $existingNumbers = Invoice::whereYear('created_at', $currentYear)
            ->pluck('invoice_number')
            ->map(function($number) use ($year) {
                $parts = explode('/', $number);
                return isset($parts[1]) ? (int) $parts[1] : null;
            })
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingNumbers)) {
            return $year . '/001';
        }

        $nextNumber = 1;
        foreach ($existingNumbers as $num) {
            if ($num != $nextNumber) {
                break;
            }
            $nextNumber++;
        }

        return $year . '/' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Download PDF
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $safeNumber = str_replace('/', '-', $invoice->invoice_number);
        $amountInWords = NumberToFrenchWords::convert($invoice->total_ttc);

        $template = $invoice->type === 'manual' 
            ? 'invoices.pdf-manual'
            : 'invoices.pdf';

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
            'Montant TTC',
            'Créé par', // ✅ Added
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
                $invoice->creator_name, // ✅ Added
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

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->delete();
        
        return redirect()->route('invoices.index')->with('success', 'Facture supprimée avec succès!');
    }
}