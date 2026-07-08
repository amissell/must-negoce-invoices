<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

/**
 * Handles invoice persistence and invoice item persistence inside a transaction.
 */
class InvoicePersistence
{
    /**
     * Persist a manual invoice and its items in a transaction.
     *
     * @param array<string, mixed> $invoiceData
     * @param array<int, array<string, mixed>> $items
     * @return Invoice
     */
    public function createManualInvoice(array $invoiceData, array $items): Invoice
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::create($invoiceData);

            foreach ($items as $item) {
                $product = $item['product'];

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price_ttc'],
                    'total' => $item['total_ttc'],
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Persist an invoice update by replacing invoice items inside a transaction.
     *
     * @param Invoice $invoice
     * @param array<string, mixed> $invoiceData
     * @param array<int, array<string, mixed>> $items
     * @return Invoice
     */
    public function updateManualInvoice(Invoice $invoice, array $invoiceData, array $items): Invoice
    {
        DB::beginTransaction();

        try {
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
                    'total' => $item['total_ttc'],
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Persist an automatic invoice and its invoice items.
     *
     * @param array<string, mixed> $invoiceData
     * @param array<int, array<string, mixed>> $items
     * @return Invoice
     */
    public function createAutomaticInvoice(array $invoiceData, array $items): Invoice
    {
        $invoice = Invoice::create($invoiceData);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product']->id ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }

        return $invoice;
    }
}
