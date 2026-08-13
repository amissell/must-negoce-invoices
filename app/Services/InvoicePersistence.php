<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoicePersistence
{
    private InvoiceNumberGenerator $invoiceNumberGenerator;

    public function __construct(InvoiceNumberGenerator $invoiceNumberGenerator)
    {
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    /**
     * Persist a manual invoice and its items in a transaction.
     * The invoice number is generated inside the transaction.
     *
     * @param  array<string, mixed>  $invoiceData
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createManualInvoice(array $invoiceData, array $items): Invoice
    {
        return DB::transaction(function () use ($invoiceData, $items) {
            $invoiceData['invoice_number'] = $this->invoiceNumberGenerator->generateNextInvoiceNumber();

            $invoice = Invoice::create($invoiceData);

            foreach ($items as $item) {
                $product = $item['product'];

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price_ttc'],
                    'unit_cost_at_sale' => $product->cost_price,
                    'total' => $item['total_ttc'],
                    'discount_amount' => $item['discount_amount'],
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Persist an invoice update by replacing invoice items inside a transaction.
     * Invoice numbers are immutable and are NOT updated here.
     *
     * @param  array<string, mixed>  $invoiceData
     * @param  array<int, array<string, mixed>>  $items
     */
    public function updateManualInvoice(Invoice $invoice, array $invoiceData, array $items): Invoice
    {
        // Invoice numbers are immutable — strip any incoming value.
        unset($invoiceData['invoice_number']);

        return DB::transaction(function () use ($invoice, $invoiceData, $items) {
            $invoice->update($invoiceData);

            $invoice->items()->delete();

            foreach ($items as $item) {
                $product = $item['product'];

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price_ttc'],
                    'unit_cost_at_sale' => $product->cost_price,
                    'total' => $item['total_ttc'],
                    'discount_amount' => $item['discount_amount'],
                ]);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Persist an automatic invoice and its invoice items in a transaction.
     * The invoice number is generated inside the transaction.
     *
     * @param  array<string, mixed>  $invoiceData
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createAutomaticInvoice(array $invoiceData, array $items): Invoice
    {
        return DB::transaction(function () use ($invoiceData, $items) {
            $invoiceData['invoice_number'] = $this->invoiceNumberGenerator->generateNextInvoiceNumber();
            $invoice = Invoice::create($invoiceData);
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product']->id ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost_at_sale' => $item['product']->cost_price ?? null,
                    'total' => $item['total'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                ]);
            }

            return $invoice;
        });
    }
}
