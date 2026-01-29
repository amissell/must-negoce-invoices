<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSequence;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceGeneratorService
{
    private const MIN_INVOICE_TOTAL = 2000;
    private const MAX_INVOICE_TOTAL = 4500;
    private const MAX_INVOICE_LIMIT = 5000;
    private const VAT_RATE = 20.00;
    private const MIN_DATE = '2026-01-01';

    private Client $client;
    private Collection $products;

    public function __construct()
    {
        $this->loadDefaultClient();
        $this->loadProducts();
    }

    /**
     * Generate invoices from a global total amount
     */
    public function generateFromTotal(float $globalTotal): array
    {
        if ($globalTotal <= 0) {
            throw new \InvalidArgumentException('Le montant total doit être supérieur à zéro.');
        }

        if ($this->products->isEmpty()) {
            throw new \RuntimeException('Aucun produit actif disponible pour la génération de factures.');
        }

        return DB::transaction(function () use ($globalTotal) {
            $invoices = [];
            $remainingTotal = $globalTotal;
            $batchId = time(); // Unique batch identifier
            $usedDates = [];

            while ($remainingTotal > 0) {
                // Calculate target for this invoice
                $targetTotal = $this->calculateInvoiceTarget($remainingTotal);
                
                // Generate random date
                $invoiceDate = $this->generateRandomDate($usedDates);
                $usedDates[] = $invoiceDate->format('Y-m-d');

                // Generate invoice number
                $invoiceNumber = InvoiceSequence::getNextNumber($invoiceDate->year);

                // Generate items that approximately match the target
                $items = $this->generateItems($targetTotal);
                $actualTotal = collect($items)->sum('line_total_ttc');

                // Create the invoice
                $invoice = $this->createInvoice(
                    $invoiceNumber,
                    $invoiceDate,
                    $items,
                    $actualTotal,
                    $batchId
                );

                $invoices[] = $invoice;
                $remainingTotal -= $actualTotal;

                // If remaining is too small to make a valid invoice, add it to the last one
                if ($remainingTotal > 0 && $remainingTotal < self::MIN_INVOICE_TOTAL) {
                    $this->addToLastInvoice($invoice, $remainingTotal);
                    $remainingTotal = 0;
                }
            }

            return $invoices;
        });
    }

    /**
     * Calculate target total for an invoice
     */
    private function calculateInvoiceTarget(float $remaining): float
    {
        if ($remaining <= self::MAX_INVOICE_LIMIT) {
            return $remaining;
        }

        // Random amount between MIN and MAX, but not round numbers
        $base = mt_rand(self::MIN_INVOICE_TOTAL * 100, self::MAX_INVOICE_TOTAL * 100) / 100;
        
        // Add random centimes to avoid round numbers
        $centimes = mt_rand(1, 99) / 100;
        $target = $base + $centimes;

        // Ensure we don't exceed the limit
        return min($target, self::MAX_INVOICE_LIMIT - 0.01);
    }

    /**
     * Generate a random unique date
     */
    private function generateRandomDate(array $usedDates): Carbon
    {
        $minDate = Carbon::parse(self::MIN_DATE);
        $maxDate = Carbon::today();
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $randomDays = mt_rand(0, $minDate->diffInDays($maxDate));
            $date = $minDate->copy()->addDays($randomDays);
            $attempts++;
        } while (in_array($date->format('Y-m-d'), $usedDates) && $attempts < $maxAttempts);

        return $date;
    }

    /**
     * Generate invoice items
     */
    private function generateItems(float $targetTotal): array
    {
        $items = [];
        $currentTotal = 0;
        $usedProducts = [];
        $attempts = 0;
        $maxAttempts = 50;

        while ($currentTotal < $targetTotal * 0.85 && $attempts < $maxAttempts) {
            // Select a random product not yet used in this invoice
            $availableProducts = $this->products->whereNotIn('id', $usedProducts);
            
            if ($availableProducts->isEmpty()) {
                break;
            }

            $product = $availableProducts->random();
            $usedProducts[] = $product->id;

            // Calculate max quantity based on remaining amount
            $remaining = $targetTotal - $currentTotal;
            $maxQty = max(1, (int) floor($remaining / $product->price_ttc));
            $quantity = mt_rand(1, min($maxQty, 20)); // Cap at 20 units per line

            $lineTotalTtc = round($quantity * $product->price_ttc, 2);
            $lineTotalHt = round($lineTotalTtc / 1.2, 2);
            $lineVat = round($lineTotalTtc - $lineTotalHt, 2);

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_ttc' => $product->price_ttc,
                'quantity' => $quantity,
                'line_total_ttc' => $lineTotalTtc,
                'line_total_ht' => $lineTotalHt,
                'line_vat' => $lineVat,
            ];

            $currentTotal += $lineTotalTtc;
            $attempts++;
        }

        // Adjust last item to get closer to target if needed
        if (!empty($items) && abs($currentTotal - $targetTotal) > 50) {
            $items = $this->adjustLastItem($items, $targetTotal, $currentTotal);
        }

        return $items;
    }

    /**
     * Adjust the last item to match target more closely
     */
    private function adjustLastItem(array $items, float $target, float $current): array
    {
        $difference = $target - $current;
        
        if ($difference > 0 && !empty($items)) {
            // Try to add more quantity to the last item
            $lastIndex = count($items) - 1;
            $lastItem = &$items[$lastIndex];
            
            $additionalQty = (int) floor($difference / $lastItem['unit_price_ttc']);
            if ($additionalQty > 0) {
                $lastItem['quantity'] += $additionalQty;
                $lastItem['line_total_ttc'] = round($lastItem['quantity'] * $lastItem['unit_price_ttc'], 2);
                $lastItem['line_total_ht'] = round($lastItem['line_total_ttc'] / 1.2, 2);
                $lastItem['line_vat'] = round($lastItem['line_total_ttc'] - $lastItem['line_total_ht'], 2);
            }
        }

        return $items;
    }

    /**
     * Create an invoice with its items
     */
    private function createInvoice(
        string $invoiceNumber,
        Carbon $invoiceDate,
        array $items,
        float $totalTtc,
        int $batchId
    ): Invoice {
        $totalHt = round($totalTtc / 1.2, 2);
        $totalVat = round($totalTtc - $totalHt, 2);

        $invoice = Invoice::create([
            'client_id' => $this->client->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'total_ht' => $totalHt,
            'total_vat' => $totalVat,
            'total_ttc' => $totalTtc,
            'vat_rate' => self::VAT_RATE,
            'is_locked' => true,
            'generation_batch' => $batchId,
        ]);

        foreach ($items as $item) {
            $invoice->items()->create($item);
        }

        return $invoice->load('items', 'client');
    }

    /**
     * Add remaining amount to the last invoice
     */
    private function addToLastInvoice(Invoice $invoice, float $amount): void
    {
        // Add a product line to cover the remaining amount
        $product = $this->products->random();
        $quantity = max(1, (int) ceil($amount / $product->price_ttc));
        
        $lineTotalTtc = round($quantity * $product->price_ttc, 2);
        $lineTotalHt = round($lineTotalTtc / 1.2, 2);
        $lineVat = round($lineTotalTtc - $lineTotalHt, 2);

        $invoice->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price_ttc' => $product->price_ttc,
            'quantity' => $quantity,
            'line_total_ttc' => $lineTotalTtc,
            'line_total_ht' => $lineTotalHt,
            'line_vat' => $lineVat,
        ]);

        // Recalculate invoice totals
        $newTotalTtc = $invoice->items()->sum('line_total_ttc');
        $invoice->update([
            'total_ttc' => $newTotalTtc,
            'total_ht' => round($newTotalTtc / 1.2, 2),
            'total_vat' => round($newTotalTtc - ($newTotalTtc / 1.2), 2),
        ]);

        $invoice->refresh();
    }

    /**
     * Load or create the default client
     */
    private function loadDefaultClient(): void
    {
        $this->client = Client::firstOrCreate(
            ['name' => 'CLIENT DIVERS'],
            [
                'ice' => null,
                'address' => null,
                'city' => null,
                'phone' => null,
            ]
        );
    }

    /**
     * Load active products
     */
    private function loadProducts(): void
    {
        $this->products = Product::all();
        
        if ($this->products->isEmpty()) {
            throw new \RuntimeException('Aucun produit disponible pour la génération de factures.');
        }
}
}
