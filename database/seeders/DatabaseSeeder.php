<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $cities = [
            ['city' => 'Kuwait City', 'country' => 'Kuwait', 'lat' => 29.3759, 'lon' => 47.9774],
            ['city' => 'Hawally', 'country' => 'Kuwait', 'lat' => 29.3326, 'lon' => 48.0289],
            ['city' => 'Salmiya', 'country' => 'Kuwait', 'lat' => 29.3344, 'lon' => 48.0537],
            ['city' => 'Farwaniya', 'country' => 'Kuwait', 'lat' => 29.2775, 'lon' => 47.9589],
            ['city' => 'Ahmadi', 'country' => 'Kuwait', 'lat' => 29.0769, 'lon' => 48.0839],
        ];

        // Create 100 sample people
        for ($i = 0; $i < 100; $i++) {
            $location = $cities[array_rand($cities)];
            
            // Generate random picture URLs (using placeholder service)
            $pictures = [];
            $pictureCount = rand(2, 5);
            for ($j = 0; $j < $pictureCount; $j++) {
                $pictures[] = "https://i.pravatar.cc/400?img=" . rand(1, 70);
            }

            Person::create([
                'name' => $faker->name(),
                'age' => rand(18, 45),
                'pictures' => $pictures,
                'latitude' => $location['lat'] + (rand(-1000, 1000) / 10000),
                'longitude' => $location['lon'] + (rand(-1000, 1000) / 10000),
                'city' => $location['city'],
                'country' => $location['country'],
                'like_count' => rand(0, 30), // Random initial like count
            ]);
        }

        $this->command->info('100 people created successfully!');
    }
}
