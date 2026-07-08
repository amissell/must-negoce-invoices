<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Responsible for invoice total calculations and line item amount normalization.
 */
class InvoiceCalculationService
{
    /**
     * Calculate invoice totals for manual invoice items and discount percentage.
     *
     * @param array<int, array<string,mixed>> $items
     * @param float $remisePercentage
     *
     * @return array<string, mixed>
     */
    public function calculateInvoiceTotals(array $items, float $remisePercentage = 0): array
    {
        $totalTtcBrut = 0;
        $itemsCalculated = [];

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unitPriceTtc = round((float) $item['unit_price'], 2);
            $lineTotalTtc = round($quantity * $unitPriceTtc, 2);

            $totalTtcBrut += $lineTotalTtc;

            $itemsCalculated[] = [
                'product_id'      => $item['product_id'],
                'quantity'        => $quantity,
                'unit_price_ttc'  => $unitPriceTtc,
                'total_ttc'       => $lineTotalTtc,
            ];
        }

        $totalTtcBrut = round($totalTtcBrut, 2);
        $montantHtBrut = round($totalTtcBrut / 1.20, 2);
        $remisePercentage = round($remisePercentage, 2);
        $remiseAmount = round($montantHtBrut * $remisePercentage / 100, 2);
        $montantHtNet = round($montantHtBrut - $remiseAmount, 2);
        $tva = round($montantHtNet * 0.20, 2);
        $totalTtc = round($montantHtNet + $tva, 2);

        return [
            'items'              => $itemsCalculated,
            'montant_ht'         => $montantHtNet,
            'remise_percentage'  => $remisePercentage,
            'remise_amount'      => $remiseAmount,
            'tva'                => $tva,
            'total_ttc'          => $totalTtc,
        ];
    }
}
