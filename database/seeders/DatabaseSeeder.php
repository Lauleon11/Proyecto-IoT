<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed air quality template with PM2.5 rules
        $this->call([
            AirQualityTemplateSeeder::class,
            WaterQualityTemplateSeeder::class,
            WaterLevelTemplateSeeder::class,
            PanicButtonTemplateSeeder::class,
            SoundNoiseTemplateSeeder::class,
            DevicesSeeder::class,
            PanelsSeeder::class,
        ]);
    }
}
