<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Schema;

class DataGapService
{
    /** @return array<int, array<string, mixed>> */
    public function report(): array
    {
        return [
            $this->gap('Profitability by customer/product', Schema::hasColumn('invoice_items', 'unit_cost_at_sale'), 'Profit is only an estimate for historic sales because cost is stored on the current product record.', ['invoice_items.unit_cost_at_sale', 'invoice_items.discount_amount']),
            $this->gap('Inventory turnover, stockouts and dead stock', Schema::hasTable('inventory_movements'), 'No stock movement or current-stock records exist.', ['inventory_movements.product_id', 'inventory_movements.quantity', 'inventory_movements.movement_type', 'inventory_movements.occurred_at']),
            $this->gap('Supplier and purchase-price analysis', Schema::hasTable('suppliers'), 'No supplier or purchase history records exist.', ['suppliers', 'purchase_orders', 'purchase_order_items.unit_cost']),
            $this->gap('Product category profitability', Schema::hasColumn('products', 'category_id'), 'Products are not connected to categories.', ['products.category_id']),
            $this->gap('Salesperson performance from invoices', Schema::hasColumn('invoices', 'vendeur_id'), 'Invoice creator is not a reliable salesperson assignment.', ['invoices.vendeur_id']),
            $this->gap('Regional analysis', false, 'Client city exists, but there is no standardized region mapping.', ['clients.region_id or clients.region', 'regions']),
            $this->gap('Operational lead-time analysis', false, 'There are no order, delivery, or fulfilment timestamps.', ['orders.created_at', 'deliveries.delivered_at', 'payments.date_paiement']),
        ];
    }

    /** @param array<int, string> $requiredFields */
    private function gap(string $question, bool $available, string $explanation, array $requiredFields): array
    {
        return [
            'question' => $question,
            'status' => $available ? 'available' : 'data_unavailable',
            'explanation' => $available ? 'The required data structure exists; completeness is checked separately.' : $explanation,
            'required_fields' => $requiredFields,
        ];
    }
}
