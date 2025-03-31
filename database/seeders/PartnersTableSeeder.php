<?php

// database/seeders/PartnersTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;
use Faker\Factory as Faker;

class PartnersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Gerar 10 parceiros com dados aleatórios
        foreach (range(1, 10) as $index) {
            Partner::create([
                'name' => $faker->company,  // Nome do parceiro (empresa fictícia)
                'phone' => $faker->phoneNumber,  // Telemóvel
                'address' => $faker->address,  // Morada
                'postal_code' => $faker->postcode,  // Código Postal
                'city' => $faker->city,  // Cidade
                'country' => $faker->country,  // País
                'email' => $faker->companyEmail,  // Email
                'vat' => $faker->unique()->numerify('PT#########'),  // NIF
                'contact_name' => $faker->name,  // Nome de contato
            ]);
        }
    }
}
