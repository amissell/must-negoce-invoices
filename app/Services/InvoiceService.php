<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    private InvoiceCalculationService $invoiceCalculationService;
    private InvoiceNumberGenerator $invoiceNumberGenerator;

    /**
     * InvoiceService constructor.
     */
    public function __construct(
        InvoiceCalculationService $invoiceCalculationService,
        InvoiceNumberGenerator $invoiceNumberGenerator
    ) {
        $this->invoiceCalculationService = $invoiceCalculationService;
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    public function createManualInvoice(array $validated, int $userId): Invoice
    {
        $validated = $this->prepareClientData($validated);
        $calculation = $this->invoiceCalculationService->calculateInvoiceTotals(
            $validated['items'],
            (float) ($validated['remise_percentage'] ?? 0)
        );

        DB::beginTransaction();

        try {
            $invoice = Invoice::create([
                'invoice_number'    => $this->generateNextInvoiceNumber(),
                'type'              => 'manual',
                'client_id'         => $validated['client_id'] ?? null,
                'client_name'       => $validated['client_name'] ?? null,
                'client_address'    => $validated['client_address'] ?? null,
                'client_phone'      => $validated['client_phone'] ?? null,
                'client_ice'        => $validated['client_ice'] ?? null,
                'invoice_date'      => $validated['invoice_date'],
                'payment_mode'      => $validated['payment_mode'],
                'montant_ht'        => $calculation['montant_ht'],
                'remise_percentage' => $calculation['remise_percentage'],
                'remise_amount'     => $calculation['remise_amount'],
                'tva_rate'          => 20,
                'tva'               => $calculation['tva'],
                'total_ttc'         => $calculation['total_ttc'],
                'amount_in_words'   => $this->numberToWords($calculation['total_ttc']),
                'created_by'        => $userId,
            ]);

            foreach ($calculation['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'description' => $product->name,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price_ttc'],
                    'total'       => $item['total_ttc'],
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function updateManualInvoice(Invoice $invoice, array $validated, int $userId): Invoice
    {
        $validated = $this->prepareClientData($validated);
        $calculation = $this->invoiceCalculationService->calculateInvoiceTotals(
            $validated['items'],
            (float) ($validated['remise_percentage'] ?? 0)
        );

        DB::beginTransaction();

        try {
            $invoice->update([
                'invoice_number'    => $validated['invoice_number'],
                'invoice_date'      => $validated['invoice_date'],
                'payment_mode'      => $validated['payment_mode'],
                'client_id'         => $validated['client_id'] ?? null,
                'client_name'       => $validated['client_name'] ?? null,
                'client_address'    => $validated['client_address'] ?? null,
                'client_phone'      => $validated['client_phone'] ?? null,
                'client_ice'        => $validated['client_ice'] ?? null,
                'montant_ht'        => $calculation['montant_ht'],
                'remise_percentage' => $calculation['remise_percentage'],
                'remise_amount'     => $calculation['remise_amount'],
                'tva_rate'          => 20,
                'tva'               => $calculation['tva'],
                'total_ttc'         => $calculation['total_ttc'],
                'amount_in_words'   => $this->numberToWords($calculation['total_ttc']),
                'updated_by'        => $userId,
            ]);

            $invoice->items()->delete();

            foreach ($calculation['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'description' => $product->name,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price_ttc'],
                    'total'       => $item['total_ttc'],
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
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
            if ($remainingAmount <= $maxInvoiceAmount) {
                $targetInvoiceAmount = $remainingAmount;
            } else {
                $targetInvoiceAmount = rand($minInvoiceAmount, $maxInvoiceAmount);
            }

            if ($targetInvoiceAmount < 100) {
                break;
            }

            $montantHT = round($targetInvoiceAmount / 1.2, 2);
            $tva = round($targetInvoiceAmount - $montantHT, 2);

            $startDate = \Carbon\Carbon::create(2026, 1, 1);
            $endDate = \Carbon\Carbon::now();
            $randomDate = \Carbon\Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => $randomDate,
                'montant_ht' => $montantHT,
                'tva' => $tva,
                'total_ttc' => $targetInvoiceAmount,
                'is_locked' => true,
                'type' => 'automatic',
                'client_name' => 'divers',
                'created_by' => $userId,
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

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->prix_ttc,
                    'total' => $itemTotal,
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
                'updated_by' => $userId,
            ]);

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

    public function generateNextInvoiceNumber(): string
    {
        return $this->invoiceNumberGenerator->generateNextInvoiceNumber();
    }

    public function generateInvoiceNumber(): string
    {
        return $this->invoiceNumberGenerator->generateInvoiceNumber();
    }

    public function numberToWords(float $number): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $intPart = floor($number);
            $decPart = round(($number - $intPart) * 100);

            $result = strtoupper($formatter->format($intPart)) . ' DHS';

            if ($decPart > 0) {
                $result .= ' ET ' . strtoupper($formatter->format($decPart)) . ' CENTIMES';
            }

            return $result;
        }

        return number_format($number, 2) . ' DHS';
    }

    private function prepareClientData(array $validated): array
    {
        $clientId = $validated['client_id'] ?? null;

        if ($clientId) {
            $client = Client::findOrFail($clientId);

            $validated['client_name']    = $client->nom;
            $validated['client_ice']     = $client->ice;
            $validated['client_phone']   = $client->telephone;
            $validated['client_address'] = $client->adresse;
        }

        $saveAsClient = isset($validated['save_as_client'])
            ? filter_var($validated['save_as_client'], FILTER_VALIDATE_BOOLEAN)
            : false;

        if (!$clientId && $saveAsClient && !empty($validated['client_name'])) {
            $newClient = Client::create([
                'nom'       => $validated['client_name'],
                'ice'       => $validated['client_ice'] ?? null,
                'telephone' => $validated['client_phone'] ?? null,
                'adresse'   => $validated['client_address'] ?? null,
            ]);

            $validated['client_id'] = $newClient->id;
        }

        return $validated;
    }

    private function createAutomaticSingleInvoice(string $customerName, array $items, float $totalTtc, int $userId): void
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
            'created_by' => $userId,
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

        if (!empty($currentItems)) {
            $this->createAutomaticSingleInvoice($customerName, $currentItems, $currentTotal, $userId);
        }
    }
}
