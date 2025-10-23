<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Compte;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'montant' => $this->faker->randomFloat(2, 1000, 1000000),
            'type' => $this->faker->randomElement(['depot', 'retrait', 'virement', 'frais']),
            'devise' => 'FCFA',
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['en_attente', 'validee', 'annulee']),
            'compte_id' => Compte::factory(),

        ];
    }
}
