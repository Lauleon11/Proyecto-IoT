<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Device;
use App\Models\Template;

class DevicesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $items = [
                ['name' => 'Sensor agua - Piscina', 'template' => 'sensor de calidad del agua', 'type' => 'real'],
                ['name' => 'Sensor nivel agua - Piscina', 'template' => 'sensor de nivel del agua', 'type' => 'digital_twin'],
                ['name' => 'Sensor sonido/ruido - Piscina', 'template' => 'sensor de sonido/ruido', 'type' => 'python'],
                ['name' => 'Sensor aire - Salon 1', 'template' => 'sensor de calidad del aire', 'type' => 'real'],
                ['name' => 'Sensor aire - Salon 2', 'template' => 'sensor de calidad del aire', 'type' => 'digital_twin'],
                ['name' => 'Sensor aire - Salon 3', 'template' => 'sensor de calidad del aire', 'type' => 'python'],
                ['name' => 'Sensor aire - Salon 4', 'template' => 'sensor de calidad del aire', 'type' => 'real'],
                ['name' => 'Botón de Pánico - Piscina', 'template' => 'boton de panico', 'type' => 'digital_twin'],
                ['name' => 'Botón de Pánico - Salon 1', 'template' => 'boton de panico', 'type' => 'python'],
                ['name' => 'Botón de Pánico - Salon 2', 'template' => 'boton de panico', 'type' => 'real'],
                ['name' => 'Botón de Pánico - Salon 3', 'template' => 'boton de panico', 'type' => 'digital_twin'],
                ['name' => 'Botón de Pánico - Salon 4', 'template' => 'boton de panico', 'type' => 'python'],
            ];

            foreach ($items as $it) {
                $template = Template::firstOrCreate(['name' => $it['template']]);

                Device::updateOrCreate(
                    ['name' => $it['name'], 'template_id' => $template->id],
                    [
                        'device_type' => $it['type'],
                        'is_on' => true,
                        'device_id' => Str::uuid()->toString(),
                        'scope_id' => Str::uuid()->toString(),
                        'key' => Str::random(32),
                    ]
                );
            }
        });
    }
}