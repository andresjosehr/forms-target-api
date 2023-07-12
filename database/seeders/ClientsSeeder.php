<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // Truncate the table
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
         DB::table('clients')->truncate();
         DB::statement('SET FOREIGN_KEY_CHECKS=1;');

         // Faker
         $faker = \Faker\Factory::create();

         // Create 100 houses
         for ($i = 0; $i < 100; $i++) {
            DB::table('clients')->insert([
                'dni' => $faker->unique()->randomNumber(8),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'neighborhood' => $faker->streetName,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'quota' => $faker->randomFloat(2, 0, 1000),
                'available_points' => $faker->randomNumber(4),
                'client_code' => $faker->unique()->randomNumber(6),
                'phone' => $faker->phoneNumber,
                'campaign' => $faker->word,
                'zip_code' => $faker->postcode,
                'balance' => $faker->randomFloat(2, -1000, 1000),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
         }
    }
}
