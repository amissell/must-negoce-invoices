<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'FAC-' . fake()->unique()->numerify('######'),
            'invoice_date' => fake()->date(),
            'client_id' => Client::factory(),
            'client_name' => fake()->company(),
            'client_address' => fake()->address(),
            'client_phone' => fake()->phoneNumber(),
            'client_email' => fake()->safeEmail(),
            'client_ice' => fake()->numerify('ICE##########'),
            'montant_ht' => 1000,
            'tva' => 200,
            'tva_rate' => 20,
            'total_ttc' => 1200,
            'remise_percentage' => 0,
            'remise_amount' => 0,
            'payment_mode' => 'VIR',
            'type' => 'manual',
            'status' => 'Verrouillée',
            'is_locked' => false,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
