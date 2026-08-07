<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->company(),
            'ice' => fake()->unique()->numerify('ICE##########'),
            'if_number' => fake()->numerify('IF#####'),
            'rc' => fake()->numerify('RC#####'),
            'cnss' => fake()->numerify('CNSS#####'),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'adresse' => fake()->address(),
            'ville' => fake()->city(),
            'credit_limit' => 0,
            'payment_terms_days' => 30,
        ];
    }
}
