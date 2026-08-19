<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;

class InvoiceService
{
    private InvoiceCalculationService $invoiceCalculationService;

    private InvoicePersistence $invoicePersistence;

    public function __construct(
        InvoiceCalculationService $invoiceCalculationService,
        InvoicePersistence $invoicePersistence
    ) {
        $this->invoiceCalculationService = $invoiceCalculationService;
        $this->invoicePersistence = $invoicePersistence;
    }

    public function createManualInvoice(array $validated, int $userId): Invoice
    {
        $validated = $this->prepareClientData($validated);
        $calculation = $this->invoiceCalculationService->calculateInvoiceTotals(
            $validated['items'],
            (float) ($validated['remise_percentage'] ?? 0)
        );

        // NOTE: invoice_number is generated atomically inside InvoicePersistence.
        return $this->invoicePersistence->createManualInvoice([
            'type' => 'manual',
            'client_id' => $validated['client_id'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'client_address' => $validated['client_address'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'client_ice' => $validated['client_ice'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'payment_mode' => $validated['payment_mode'],
            'montant_ht' => $calculation['montant_ht'],
            'remise_percentage' => $calculation['remise_percentage'],
            'remise_amount' => $calculation['remise_amount'],
            'tva_rate' => 20,
            'tva' => $calculation['tva'],
            'total_ttc' => $calculation['total_ttc'],
            'amount_in_words' => $this->numberToWords($calculation['total_ttc']),
            'created_by' => $userId,
        ], $this->buildManualInvoiceItems($calculation['items'], (float) $calculation['remise_percentage']));
    }

    public function updateManualInvoice(Invoice $invoice, array $validated, int $userId): Invoice
    {
        // PATCH requests may change only a subset of invoice fields. Merge the
        // submitted data with the current invoice before recalculating totals.
        $existingItems = $invoice->items()->with('product')->get()->map(static function ($item): array {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        })->all();

        $validated = array_merge([
            'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
            'payment_mode' => $invoice->payment_mode,
            'client_id' => $invoice->client_id,
            'client_name' => $invoice->client_name,
            'client_address' => $invoice->client_address,
            'client_phone' => $invoice->client_phone,
            'client_ice' => $invoice->client_ice,
            'remise_percentage' => $invoice->remise_percentage ?? 0,
            'items' => $existingItems,
        ], $validated);

        $validated = $this->prepareClientData($validated);
        $calculation = $this->invoiceCalculationService->calculateInvoiceTotals(
            $validated['items'],
            (float) ($validated['remise_percentage'] ?? 0)
        );

        return $this->invoicePersistence->updateManualInvoice(
            $invoice,
            [
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'],
                'client_id' => $validated['client_id'] ?? null,
                'client_name' => $validated['client_name'] ?? null,
                'client_address' => $validated['client_address'] ?? null,
                'client_phone' => $validated['client_phone'] ?? null,
                'client_ice' => $validated['client_ice'] ?? null,
                'montant_ht' => $calculation['montant_ht'],
                'remise_percentage' => $calculation['remise_percentage'],
                'remise_amount' => $calculation['remise_amount'],
                'tva_rate' => 20,
                'tva' => $calculation['tva'],
                'total_ttc' => $calculation['total_ttc'],
                'amount_in_words' => $this->numberToWords($calculation['total_ttc']),
                'updated_by' => $userId,
            ],
            $this->buildManualInvoiceItems($calculation['items'], (float) $calculation['remise_percentage'])
        );
    }

    public function createInvoicesFromAmount(float $amount, string $customerName, int $userId): void
    {
        $items = [
            [
                'product' => (object) ['id' => 0],
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount,
            ],
        ];

        $this->splitIntoMultipleInvoices($customerName, $items, $userId);
    }

    public function createInvoicesFromProducts(array $productsData, string $customerName, int $userId): void
    {
        $totalAmount = 0;
        $allItems = [];

        foreach ($productsData as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = (int) $productData['quantity'];
            $unitPrice = $product->prix_ttc;
            if ($unitPrice === null) {
                throw new \RuntimeException("Le produit '{$product->name}' n'a pas de prix défini.");
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
            $this->createAutomaticSingleInvoice($customerName, $allItems, $totalAmount, $userId);
        } else {
            $this->splitIntoMultipleInvoices($customerName, $allItems, $userId);
        }
    }

    public function generateAutomaticInvoices(float $totalTTC, int $userId): array
    {
        $maxInvoiceAmount = 4999;
        $minInvoiceAmount = 3000;
        $remainingAmount = $totalTTC;
        $createdInvoices = 0;
        $totalGenerated = 0;
        while ($remainingAmount > 50) {
            $targetInvoiceAmount = $remainingAmount <= $maxInvoiceAmount
                ? $remainingAmount
                : rand($minInvoiceAmount, $maxInvoiceAmount);
            if ($targetInvoiceAmount < 100) {
                break;
            }
            $montantHT = round($targetInvoiceAmount / 1.2, 2);
            $tva = round($targetInvoiceAmount - $montantHT, 2);
            $startDate = \Carbon\Carbon::create(2026, 1, 1);
            $endDate = \Carbon\Carbon::now();
            $randomDate = \Carbon\Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
            $numberOfProducts = rand(300, 400);
            $products = Product::inRandomOrder()->limit($numberOfProducts)->get();
            if ($products->isEmpty()) {
                break;
            }
            $items = [];
            $actualInvoiceTotal = 0;
            $itemsRemaining = $targetInvoiceAmount;
            foreach ($products as $product) {
                if ($itemsRemaining <= 1) {
                    break;
                }
                $quantity = rand(6, 24);
                $itemTotal = round($quantity * $product->prix_ttc, 2);
                while ($itemTotal > $itemsRemaining && $quantity > 1) {
                    $quantity--;
                    $itemTotal = round($quantity * $product->prix_ttc, 2);
                }
                if ($itemTotal <= 0 || $itemTotal > $itemsRemaining) {
                    continue;
                }
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->prix_ttc,
                    'total' => $itemTotal,
                ];
                $actualInvoiceTotal += $itemTotal;
                $itemsRemaining -= $itemTotal;
            }
            if (empty($items) || $actualInvoiceTotal < 100) {
                break;
            }
            $actualMontantHT = round($actualInvoiceTotal / 1.2, 2);
            $actualTva = round($actualInvoiceTotal - $actualMontantHT, 2);
            $this->invoicePersistence->createAutomaticInvoice([
                'invoice_date' => $randomDate,
                'montant_ht' => $actualMontantHT,
                'tva' => $actualTva,
                'total_ttc' => $actualInvoiceTotal,
                'is_locked' => true,
                'type' => 'automatic',
                'client_name' => 'divers',
                'created_by' => $userId,
            ], $items);
            $remainingAmount -= $actualInvoiceTotal;
            $totalGenerated += $actualInvoiceTotal;
            $createdInvoices++;
            if ($createdInvoices >= 100) {
                break;
            }
        }

        return [
            'createdInvoices' => $createdInvoices,
            'totalGenerated' => $totalGenerated,
            'requestedAmount' => $totalTTC,
        ];
    }

    public function numberToWords(float $number): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $intPart = floor($number);
            $decPart = round(($number - $intPart) * 100);
            $result = strtoupper($formatter->format($intPart)).' DHS';
            if ($decPart > 0) {
                $result .= ' ET '.strtoupper($formatter->format($decPart)).' CENTIMES';
            }

            return $result;
        }

        return number_format($number, 2).' DHS';
    }

    private function prepareClientData(array $validated): array
    {
        $clientId = $validated['client_id'] ?? null;
        if ($clientId) {
            $client = Client::findOrFail($clientId);
            $validated['client_name'] = $client->nom;
            $validated['client_ice'] = $client->ice;
            $validated['client_phone'] = $client->telephone;
            $validated['client_address'] = $client->adresse;
        }
        $saveAsClient = isset($validated['save_as_client'])
            ? filter_var($validated['save_as_client'], FILTER_VALIDATE_BOOLEAN)
            : false;
        if (! $clientId && $saveAsClient && ! empty($validated['client_name'])) {
            $newClient = Client::create([
                'nom' => $validated['client_name'],
                'ice' => $validated['client_ice'] ?? null,
                'telephone' => $validated['client_phone'] ?? null,
                'adresse' => $validated['client_address'] ?? null,
            ]);
            $validated['client_id'] = $newClient->id;
        }

        return $validated;
    }

    private function createAutomaticSingleInvoice(string $customerName, array $items, float $totalTtc, int $userId): void
    {
        $montantHt = round($totalTtc / 1.2, 2);
        $tva = round($totalTtc - $montantHt, 2);
        $this->invoicePersistence->createAutomaticInvoice([
            'invoice_date' => now(),
            'montant_ht' => $montantHt,
            'tva' => $tva,
            'total_ttc' => $totalTtc,
            'is_locked' => true,
            'type' => 'automatic',
            'client_name' => $customerName,
            'created_by' => $userId,
        ], $items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function buildManualInvoiceItems(array $items, float $remisePercentage = 0): array
    {
        return array_map(function (array $item) use ($remisePercentage) {
            return [
                'product' => Product::findOrFail($item['product_id']),
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price_ttc' => $item['unit_price_ttc'],
                'total_ttc' => $item['total_ttc'],
                'discount_amount' => round((float) $item['total_ttc'] * $remisePercentage / 100, 2),
            ];
        }, $items);
    }

    private function splitIntoMultipleInvoices(string $customerName, array $items, int $userId): void
    {
        $max = 4999;
        $currentItems = [];
        $currentTotal = 0;
        foreach ($items as $item) {
            if ($currentTotal + $item['total'] > $max) {
                $this->createAutomaticSingleInvoice($customerName, $currentItems, $currentTotal, $userId);
                $currentItems = [];
                $currentTotal = 0;
            }
            $currentItems[] = $item;
            $currentTotal += $item['total'];
        }
        if (! empty($currentItems)) {
            $this->createAutomaticSingleInvoice($customerName, $currentItems, $currentTotal, $userId);
        }
    }

    public function generateNextInvoiceNumber(): string
    {
        $year = date('y');

        $lastInvoice = \App\Models\Invoice::where('invoice_number', 'like', $year.'/%')
            ->orderByDesc('invoice_number')
            ->first();

        if (! $lastInvoice) {
            return $year.'/001';
        }
        $parts = explode('/', $lastInvoice->invoice_number);

        $next = ((int) $parts[1]) + 1;

        return $year.'/'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next invoice number, filling gaps if present.
     */
    public function generateInvoiceNumber(): string
    {
        $year = date('y');

        $numbers = \App\Models\Invoice::where('invoice_number', 'like', $year.'/%')
            ->pluck('invoice_number')
            ->map(fn ($n) => (int) substr($n, 3))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $i = 1;
        foreach ($numbers as $num) {
            if ($num !== $i) {
                return $year.'/'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            }
            $i++;
        }

        return $year.'/'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
    }
}
