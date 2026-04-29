<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ManualInvoiceController extends Controller
{
    public function create()
    {
        $products          = Product::orderBy('name')->get();
        $nextInvoiceNumber = $this->generateNextInvoiceNumber();

        return view('invoices.create-manual', compact('products', 'nextInvoiceNumber'));
    }


    public function store(Request $request)
{
    $validated = $request->validate([
        'client_id'          => 'nullable|exists:clients,id',
        'client_name'        => 'nullable|string|max:255',
        'client_address'     => 'nullable|string|max:500',
        'client_phone'       => 'nullable|string|max:50',
        'client_ice'         => 'nullable|string|max:15',
        'save_as_client'     => 'nullable|in:0,1,true,false,on',

        'invoice_date'       => 'required|date',
        'payment_mode'       => 'required|in:CH,ESP,LCN',
        'remise_percentage'  => 'nullable|numeric|min:0|max:100',

        'items'              => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity'   => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $clientId   = $validated['client_id'] ?? null;
        $clientName = $validated['client_name'] ?? null;
        $clientIce  = $validated['client_ice'] ?? null;

        if ($clientId) {
            $client = Client::findOrFail($clientId);
            $clientName = $client->nom;
            $clientIce  = $client->ice;
            $validated['client_phone']   = $client->telephone;
            $validated['client_address'] = $client->adresse;
        }

        if (!$clientId && $request->boolean('save_as_client') && $clientName) {
            $newClient = Client::firstOrCreate(
                ['ice' => $clientIce ?? null],
                [
                    'nom'       => $clientName,
                    'telephone' => $validated['client_phone'] ?? null,
                    'adresse'   => $validated['client_address'] ?? null,
                ]
            );
            $clientId = $newClient->id;
        }

        // ===== CALCULS =====
        $montant_ht = 0;

        foreach ($validated['items'] as $item) {
            $montant_ht += $item['quantity'] * $item['unit_price'];
        }

        $remisePercentage = (float) ($validated['remise_percentage'] ?? 0);
        $remiseAmount     = round($montant_ht * $remisePercentage / 100, 2);
        $ht_net           = round($montant_ht - $remiseAmount, 2);
        $tva              = round($ht_net * 0.20, 2);
        $total_ttc        = round($ht_net + $tva, 2);

        $invoice = Invoice::create([
            'invoice_number'    => $this->generateNextInvoiceNumber(),
            'type'              => 'manual',
            'client_id'         => $clientId,
            'client_name'       => $clientName,
            'client_address'    => $validated['client_address'] ?? null,
            'client_phone'      => $validated['client_phone'] ?? null,
            'client_ice'        => $clientIce,
            'invoice_date'      => $validated['invoice_date'],
            'payment_mode'      => $validated['payment_mode'],

            'montant_ht'        => $montant_ht,
            'remise_percentage' => $remisePercentage,
            'remise_amount'     => $remiseAmount,
            'tva_rate'          => 20,
            'tva'               => $tva,
            'total_ttc'         => $total_ttc,

            'amount_in_words'   => $this->numberToWords($total_ttc),
            'created_by'        => Auth::id(),
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $item['product_id'],
                'description' => $product->name,
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
            ]);
        }

        DB::commit();

        return redirect()->route('invoices.index')
            ->with('success', 'Facture créée');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', $e->getMessage());
    }
}


public function update(Request $request, $id)
{
    $invoice = Invoice::findOrFail($id);

    $validated = $request->validate([
        'invoice_number'     => 'required|string|max:50',
        'invoice_date'       => 'required|date',
        'payment_mode'       => 'required|in:CH,ESP,LCN',

        'client_id'          => 'nullable|exists:clients,id',
        'client_name'        => 'nullable|string|max:255',
        'client_address'     => 'nullable|string|max:500',
        'client_phone'       => 'nullable|string|max:50',
        'client_ice'         => 'nullable|string|max:15',

        'remise_percentage'  => 'nullable|numeric|min:0|max:100',

        'items'              => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity'   => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $clientId   = $validated['client_id'] ?? null;
        $clientName = $validated['client_name'] ?? null;
        $clientIce  = $validated['client_ice'] ?? null;

        if ($clientId) {
            $client = Client::findOrFail($clientId);
            $clientName = $client->nom;
            $clientIce  = $client->ice;
            $validated['client_phone']   = $client->telephone;
            $validated['client_address'] = $client->adresse;
        }

        // ===== CALCULS =====
        $montant_ht = 0;

        foreach ($validated['items'] as $item) {
            $montant_ht += $item['quantity'] * $item['unit_price'];
        }

        $remisePercentage = (float) ($validated['remise_percentage'] ?? 0);
        $remiseAmount     = round($montant_ht * $remisePercentage / 100, 2);
        $ht_net           = round($montant_ht - $remiseAmount, 2);
        $tva              = round($ht_net * 0.20, 2);
        $total_ttc        = round($ht_net + $tva, 2);

        $invoice->update([
            'invoice_number'    => $validated['invoice_number'],
            'invoice_date'      => $validated['invoice_date'],
            'payment_mode'      => $validated['payment_mode'],
            'client_id'         => $clientId,
            'client_name'       => $clientName,
            'client_address'    => $validated['client_address'] ?? null,
            'client_phone'      => $validated['client_phone'] ?? null,
            'client_ice'        => $clientIce,

            'montant_ht'        => $montant_ht,
            'remise_percentage' => $remisePercentage,
            'remise_amount'     => $remiseAmount,
            'tva_rate'          => 20,
            'tva'               => $tva,
            'total_ttc'         => $total_ttc,

            'amount_in_words'   => $this->numberToWords($total_ttc),
            'updated_by'        => Auth::id(),
        ]);

        $invoice->items()->delete();

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $item['product_id'],
                'description' => $product->name,
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
            ]);
        }

        DB::commit();

        return redirect()->route('invoices.index')
            ->with('success', 'Facture modifiée');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', $e->getMessage());
    }
}


    // ── Méthodes utilitaires ─────────────────────────────────────────────────

    private function generateNextInvoiceNumber(): string
    {
        $year        = date('y');
        $lastInvoice = Invoice::where('invoice_number', 'like', $year . '/%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED) DESC')
            ->first();

        $newNumber = $lastInvoice
            ? intval(explode('/', $lastInvoice->invoice_number)[1]) + 1
            : 1;

        return $year . '/' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    private function numberToWords(float $number): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $intPart   = floor($number);
            $decPart   = round(($number - $intPart) * 100);
            $words     = strtoupper($formatter->format($intPart));
            $result    = $words . ' DHS';
            if ($decPart > 0) {
                $result .= ' ET ' . strtoupper($formatter->format($decPart)) . ' CENTIMES';
            }
            return $result;
        }
        return number_format($number, 2) . ' DHS';
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
        $invoice       = Invoice::with('items.product')->findOrFail($id);
        $amountInWords = $this->numberToWords($invoice->total_ttc);
        $pdf           = Pdf::loadView('invoices.pdf-manual', compact('invoice', 'amountInWords'));

        return $pdf->download("facture_{$invoice->invoice_number}.pdf");
    }

    public function edit($id)
    {
        $invoice  = Invoice::with('items.product')->findOrFail($id);
        $products = Product::orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'products'));
    }
}