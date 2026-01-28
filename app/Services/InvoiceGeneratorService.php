<?php
// app/Services/InvoiceGeneratorService.php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSequence;
use App\Models\Product;
use App\Helpers\NumberToFrenchWords;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceGeneratorService
{
    private const MAX_INVOICE_AMOUNT = 5000;
    private const TARGET_AMOUNTS = [2000, 2500, 3000, 3500, 4000, 4500];
    private const MIN_PRODUCTS_PER_INVOICE = 8;
    private const MAX_PRODUCTS_PER_INVOICE = 25;
    
    private Collection $products;
    private Client $client;
    private Carbon $startDate;
    private Carbon $endDate;

    public function __construct()
    {
        $this->products = Product::where('is_active', true)->get();
        $this->client = Client::where('is_default', true)->first() ?? Client::first();
        $this->startDate = Carbon::create(2026, 1, 1);
        $this->endDate = Carbon::now();
    }

    /**
     * Generate invoices for a global total amount
     */
    public function generateInvoices(float $globalTotal): array
    {
        if ($globalTotal <= 0) {
            throw new \InvalidArgumentException('Global total must be positive');
        }

        return DB::transaction(function () use ($globalTotal) {
            $batchId = time();
            $invoices = [];

            $invoiceAmounts = $this->splitIntoInvoiceAmounts($globalTotal);
            $dates = $this->generateRandomDates(count($invoiceAmounts));
            sort($dates);
            
            $invoicesByYear = [];
            foreach ($dates as $index => $date) {
                $year = $date->year;
                if (!isset($invoicesByYear[$year])) {
                    $invoicesByYear[$year] = [];
                }
                $invoicesByYear[$year][] = [
                    'date' => $date,
                    'amount' => $invoiceAmounts[$index],
                ];
            }

            foreach ($invoicesByYear as $year => $yearInvoices) {
                $numbers = InvoiceSequence::reserveNumbers($year, count($yearInvoices));
                
                foreach ($yearInvoices as $index => $invoiceData) {
                    $invoice = $this->createInvoice(
                        $numbers[$index],
                        $invoiceData['date'],
                        $invoiceData['amount'],
                        $batchId
                    );
                    $invoices[] = $invoice;
                }
            }

            return [
                'batch_id' => $batchId,
                'invoices' => $invoices,
                'total_generated' => array_sum(array_map(fn($i) => $i->total_ttc, $invoices)),
                'count' => count($invoices),
            ];
        });
    }

    private function splitIntoInvoiceAmounts(float $globalTotal): array
    {
        $amounts = [];
        $remaining = $globalTotal;

        while ($remaining > 0) {
            if ($remaining <= self::MAX_INVOICE_AMOUNT) {
                $amounts[] = $this->addRandomCents($remaining);
                break;
            }

            $targetAmount = $this->pickRandomTargetAmount($remaining);
            $amount = $this->addRandomCents($targetAmount);
            $amount = min($amount, self::MAX_INVOICE_AMOUNT - rand(10, 500));
            
            $amounts[] = $amount;
            $remaining -= $amount;
        }

        shuffle($amounts);
        return $amounts;
    }

    private function pickRandomTargetAmount(float $remaining): float
    {
        $validTargets = array_filter(
            self::TARGET_AMOUNTS,
            fn($t) => $t < $remaining && $t <= self::MAX_INVOICE_AMOUNT
        );

        if (empty($validTargets)) {
            return min($remaining, self::MAX_INVOICE_AMOUNT - rand(100, 500));
        }

        $weights = [
            2000 => 15,
            2500 => 20,
            3000 => 25,
            3500 => 20,
            4000 => 15,
            4500 => 5,
        ];

        $totalWeight = 0;
        $weightedTargets = [];
        
        foreach ($validTargets as $target) {
            $weight = $weights[$target] ?? 10;
            $totalWeight += $weight;
            $weightedTargets[] = ['target' => $target, 'weight' => $weight];
        }

        $random = rand(1, $totalWeight);
        $cumulative = 0;
        
        foreach ($weightedTargets as $item) {
            $cumulative += $item['weight'];
            if ($random <= $cumulative) {
                return $item['target'];
            }
        }

        return $validTargets[array_rand($validTargets)];
    }

    private function addRandomCents(float $amount): float
    {
        $variation = $amount * (rand(-500, 500) / 10000);
        $amount += $variation;
        
        $cents = rand(1, 99);
        while (in_array($cents, [0, 25, 50, 75])) {
            $cents = rand(1, 99);
        }
        
        return floor($amount) + ($cents / 100);
    }

    private function generateRandomDates(int $count): array
    {
        $dates = [];
        $daysDiff = $this->startDate->diffInDays($this->endDate);
        
        for ($i = 0; $i < $count; $i++) {
            $randomDays = rand(0, $daysDiff);
            $date = $this->startDate->copy()->addDays($randomDays);
            
            while ($date->isWeekend()) {
                $date->addDay();
                if ($date->gt($this->endDate)) {
                    $date = $this->endDate->copy();
                    break;
                }
            }
            
            $dates[] = $date;
        }
        
        return $dates;
    }

    private function createInvoice(string $invoiceNumber, Carbon $date, float $targetAmount, int $batchId): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'client_id' => $this->client->id,
            'invoice_date' => $date,
            'payment_mode' => 'CH',
            'total_ht' => 0,
            'total_tva' => 0,
            'total_ttc' => 0,
            'tva_rate' => 20.00,
            'is_locked' => true,
            'batch_id' => $batchId,
        ]);

        $items = $this->generateInvoiceItems($targetAmount);
        
        $sortOrder = 0;
        foreach ($items as $itemData) {
            $itemData['sort_order'] = $sortOrder++;
            $invoice->items()->create($itemData);
        }

        $invoice->calculateTotals();
        $invoice->amount_in_words = NumberToFrenchWords::convert($invoice->total_ttc);
        $invoice->save();

        return $invoice->fresh(['items']);
    }

    private function generateInvoiceItems(float $targetAmount): array
    {
        $items = [];
        $currentTotal = 0;
        $usedProducts = [];
        $productsArray = $this->products->toArray();
        
        $numProducts = rand(self::MIN_PRODUCTS_PER_INVOICE, self::MAX_PRODUCTS_PER_INVOICE);
        $targetWithMargin = $targetAmount * 0.85;
        
        while ($currentTotal < $targetWithMargin && count($items) < $numProducts) {
            $availableProducts = array_filter(
                $productsArray,
                fn($p) => !in_array($p['id'], $usedProducts)
            );
            
            if (empty($availableProducts)) {
                break;
            }
            
            $product = $availableProducts[array_rand($availableProducts)];
            $usedProducts[] = $product['id'];
            
            $remaining = $targetAmount - $currentTotal;
            $maxQty = max(1, floor($remaining / $product['price_ttc']));
            $quantity = $this->getRealisticQuantity($maxQty, $product['price_ttc']);
            
            if ($quantity > 0) {
                $lineTotal = round($product['price_ttc'] * $quantity, 2);
                $lineTotalHt = round($lineTotal / 1.2, 2);
                
                $items[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'quantity' => $quantity,
                    'unit_price_ttc' => $product['price_ttc'],
                    'unit_price_ht' => round($product['price_ttc'] / 1.2, 2),
                    'line_total_ttc' => $lineTotal,
                    'line_total_ht' => $lineTotalHt,
                    'line_tva' => round($lineTotal - $lineTotalHt, 2),
                ];
                
                $currentTotal += $lineTotal;
            }
        }

        $items = $this->adjustItemsToTarget($items, $targetAmount, $productsArray, $usedProducts);
        return $items;
    }

    private function getRealisticQuantity(int $maxQty, float $price): int
    {
        $commonQuantities = [4, 6, 12, 15, 24, 30];
        $validQuantities = array_filter($commonQuantities, fn($q) => $q <= $maxQty);
        
        if (!empty($validQuantities) && rand(1, 100) <= 70) {
            return $validQuantities[array_rand($validQuantities)];
        }
        
        return max(1, rand(1, min($maxQty, 50)));
    }

    private function adjustItemsToTarget(array $items, float $targetAmount, array $products, array $usedProducts): array
    {
        $currentTotal = array_sum(array_column($items, 'line_total_ttc'));
        $diff = $targetAmount - $currentTotal;
        
        if (abs($diff) / $targetAmount < 0.02) {
            return $items;
        }
        
        if ($diff > 0) {
            $availableProducts = array_filter(
                $products,
                fn($p) => !in_array($p['id'], $usedProducts) && $p['price_ttc'] <= $diff
            );
            
            if (!empty($availableProducts)) {
                $product = $availableProducts[array_rand($availableProducts)];
                $quantity = max(1, floor($diff / $product['price_ttc']));
                $lineTotal = round($product['price_ttc'] * $quantity, 2);
                
                $items[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'quantity' => $quantity,
                    'unit_price_ttc' => $product['price_ttc'],
                    'unit_price_ht' => round($product['price_ttc'] / 1.2, 2),
                    'line_total_ttc' => $lineTotal,
                    'line_total_ht' => round($lineTotal / 1.2, 2),
                    'line_tva' => round($lineTotal - round($lineTotal / 1.2, 2), 2),
                ];
            }
        }
        
        return $items;
    }
}