<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if already seeded
        if (Person::count() > 0) {
            $this->command->info('People already exist. Skipping seeding.');
            return;
        }

        $firstNames = [
            'Mohammed', 'Ahmed', 'Ali', 'Omar', 'Khalid', 'Yousef', 'Abdullah', 'Salem',
            'Fatima', 'Aisha', 'Mariam', 'Noor', 'Sara', 'Huda', 'Layla', 'Zahra',
            'Hassan', 'Hussein', 'Rashid', 'Faisal', 'Tariq', 'Zayed', 'Mansour', 'Jaber',
            'Amira', 'Nada', 'Reem', 'Jana', 'Lina', 'Maha', 'Noura', 'Salma',
            'Hamad', 'Nasser', 'Sultan', 'Saif', 'Talal', 'Waleed', 'Bashar', 'Karim',
            'Yasmin', 'Dina', 'Hana', 'Rana', 'Leila', 'Maya', 'Nadia', 'Sana'
        ];

        $lastNames = [
            'Al-Sabah', 'Al-Ahmad', 'Al-Jaber', 'Al-Salem', 'Al-Mutairi', 'Al-Azmi',
            'Al-Rashidi', 'Al-Ajmi', 'Al-Enezi', 'Al-Shammari', 'Al-Dosari', 'Al-Otaibi',
            'Al-Harbi', 'Al-Anzi', 'Al-Roumi', 'Al-Kandari', 'Al-Fahad', 'Al-Khaled',
            'Al-Nasser', 'Al-Ibrahim', 'Al-Mansour', 'Al-Abdulla', 'Al-Mohammed', 'Al-Ali'
        ];

        $cities = [
            ['city' => 'Kuwait City', 'country' => 'Kuwait', 'lat' => 29.3759, 'lon' => 47.9774],
            ['city' => 'Hawally', 'country' => 'Kuwait', 'lat' => 29.3326, 'lon' => 48.0289],
            ['city' => 'Salmiya', 'country' => 'Kuwait', 'lat' => 29.3344, 'lon' => 48.0537],
            ['city' => 'Farwaniya', 'country' => 'Kuwait', 'lat' => 29.2775, 'lon' => 47.9589],
            ['city' => 'Ahmadi', 'country' => 'Kuwait', 'lat' => 29.0769, 'lon' => 48.0839],
            ['city' => 'Jahra', 'country' => 'Kuwait', 'lat' => 29.3375, 'lon' => 47.6581],
            ['city' => 'Sabah Al-Salem', 'country' => 'Kuwait', 'lat' => 29.2508, 'lon' => 48.0806],
        ];

        $people = [];
        
        for ($i = 0; $i < 100; $i++) {
            $location = $cities[array_rand($cities)];
            
            // Generate random picture URLs
            $pictureCount = rand(2, 5);
            $pictures = [];
            for ($j = 0; $j < $pictureCount; $j++) {
                $pictures[] = "https://i.pravatar.cc/400?img=" . rand(1, 70);
            }

            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $people[] = [
                'name' => $firstName . ' ' . $lastName,
                'age' => rand(18, 45),
                'pictures' => json_encode($pictures),
                'latitude' => $location['lat'] + (rand(-1000, 1000) / 10000),
                'longitude' => $location['lon'] + (rand(-1000, 1000) / 10000),
                'city' => $location['city'],
                'country' => $location['country'],
                'like_count' => rand(0, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks for better performance
        foreach (array_chunk($people, 50) as $chunk) {
            DB::table('people')->insert($chunk);
        }

        $this->command->info('Successfully created 100 people!');
    }
}
