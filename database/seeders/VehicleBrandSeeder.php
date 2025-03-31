<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;

class VehicleBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $brands = [
            ['name' => 'Acura', 'reference' => '9047'],
            ['name' => 'Alfa Romeo', 'reference' => '7623'],
            ['name' => 'Aston Martin', 'reference' => '3569'],
            ['name' => 'Audi', 'reference' => '1505'],
            ['name' => 'Bentley', 'reference' => '9522'],
            ['name' => 'BMW', 'reference' => '8498'],
            ['name' => 'Bugatti', 'reference' => '7908'],
            ['name' => 'Buick', 'reference' => '3853'],
            ['name' => 'Cadillac', 'reference' => '6269'],
            ['name' => 'Chevrolet', 'reference' => '5988'],
            ['name' => 'Chrysler', 'reference' => '8269'],
            ['name' => 'Citroën', 'reference' => '5209'],
            ['name' => 'Dacia', 'reference' => '4280'],
            ['name' => 'Daewoo', 'reference' => '3304'],
            ['name' => 'Daihatsu', 'reference' => '5182'],
            ['name' => 'Dodge', 'reference' => '7141'],
            ['name' => 'DS Automobiles', 'reference' => '6343'],
            ['name' => 'Ferrari', 'reference' => '2720'],
            ['name' => 'Fiat', 'reference' => '5969'],
            ['name' => 'Ford', 'reference' => '3343'],
            ['name' => 'Genesis', 'reference' => '3030'],
            ['name' => 'GMC', 'reference' => '6773'],
            ['name' => 'Honda', 'reference' => '9271'],
            ['name' => 'Hummer', 'reference' => '3339'],
            ['name' => 'Hyundai', 'reference' => '4620'],
            ['name' => 'Infiniti', 'reference' => '4518'],
            ['name' => 'Isuzu', 'reference' => '6546'],
            ['name' => 'Jaguar', 'reference' => '4902'],
            ['name' => 'Jeep', 'reference' => '1817'],
            ['name' => 'Kia', 'reference' => '8081'],
            ['name' => 'Koenigsegg', 'reference' => '4852'],
            ['name' => 'Lamborghini', 'reference' => '3658'],
            ['name' => 'Lancia', 'reference' => '3664'],
            ['name' => 'Land Rover', 'reference' => '7381'],
            ['name' => 'Lexus', 'reference' => '8735'],
            ['name' => 'Lincoln', 'reference' => '3093'],
            ['name' => 'Lotus', 'reference' => '1060'],
            ['name' => 'Maserati', 'reference' => '4444'],
            ['name' => 'Maybach', 'reference' => '9324'],
            ['name' => 'Mazda', 'reference' => '2891'],
            ['name' => 'McLaren', 'reference' => '7647'],
            ['name' => 'Mercedes-Benz', 'reference' => '2168'],
            ['name' => 'MG', 'reference' => '5483'],
            ['name' => 'Mini', 'reference' => '9340'],
            ['name' => 'Mitsubishi', 'reference' => '9221'],
            ['name' => 'Nissan', 'reference' => '5530'],
            ['name' => 'Opel', 'reference' => '1954'],
            ['name' => 'Pagani', 'reference' => '7756'],
            ['name' => 'Peugeot', 'reference' => '7523'],
            ['name' => 'Plymouth', 'reference' => '9693'],
            ['name' => 'Polestar', 'reference' => '2285'],
            ['name' => 'Pontiac', 'reference' => '7053'],
            ['name' => 'Porsche', 'reference' => '2528'],
            ['name' => 'RAM', 'reference' => '1929'],
            ['name' => 'Renault', 'reference' => '1351'],
            ['name' => 'Rolls-Royce', 'reference' => '6243'],
            ['name' => 'Saab', 'reference' => '7992'],
            ['name' => 'Saturn', 'reference' => '9188'],
            ['name' => 'Scion', 'reference' => '4325'],
            ['name' => 'Seat', 'reference' => '9737'],
            ['name' => 'Škoda', 'reference' => '1092'],
            ['name' => 'Smart', 'reference' => '9066'],
            ['name' => 'Subaru', 'reference' => '4909'],
            ['name' => 'Suzuki', 'reference' => '3181'],
            ['name' => 'Tesla', 'reference' => '9642'],
            ['name' => 'Toyota', 'reference' => '3570'],
            ['name' => 'Volkswagen', 'reference' => '1430'],
            ['name' => 'Volvo', 'reference' => '9392'],
        ];


        foreach ($brands as $brand) {
            VehicleBrand::create($brand);
        }
    }
}
