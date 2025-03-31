<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Testing\Fakes\Fake;

class ClientsTableSeeder extends Seeder
{
    public function run()
    {


        for ($i = 1; $i <= 10; $i++) {

            \App\Models\Client::create([
                'name' => fake()->name(),
                'vat_number' => fake()->randomNumber(9),
                'birth_date' => fake()->date('Y-m-d', '2000-01-01', '2010-12-31'),
                'gender' => fake()->randomElement(['', 'Homem', 'Mulher', 'Outro']),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->email(),
                'identification_number' => fake()->randomNumber(9),
                'validate_identification_number' => fake()->date('Y-m-d', '2026-01-01', '2029-12-31'),
                'address' => fake()->address(),
                'postal_code' => fake()->postcode(),
                'city' => fake()->city(),
                'client_type' => fake()->randomElement(['Particular', 'Empresa']),
                'origin' => fake()->randomElement(['', 'Olx', 'StandVirtual', 'Facebook', 'Instagram', 'Amigo', 'Outro']),
                'observation' => fake()->sentence(),
            ]);
        }
    }
}
