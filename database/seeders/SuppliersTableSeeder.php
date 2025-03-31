<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Supplier;


class SuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            Supplier::create([
                'type' => $faker->randomElement(['particular', 'empresa']),
                'company_name' => $faker->company,
                'contact_name' => $faker->name,
                'address' => $faker->address,
                'postal_code' => $faker->postcode,
                'city' => $faker->city,
                'country' => $faker->country,
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'vat' => $faker->unique()->numerify('PT#########'),
                'identification_number' => $faker->unique()->numerify('##########'),
                'identification_number_validity' => $faker->date,
            ]);
        }
    }
}
