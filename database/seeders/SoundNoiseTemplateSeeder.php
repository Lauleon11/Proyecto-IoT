<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;

class SoundNoiseTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Crear o recuperar la plantilla
            $template = Template::firstOrCreate([
                'name' => 'sensor de sonido/ruido',
            ]);

            // Telemetría: nivel_ruido (double)
            $noise = Telemetry::where('template_id', $template->id)
                ->where('name', 'nivel_ruido')
                ->first();

            $telemetryData = [
                'template_id' => $template->id,
                'name' => 'nivel_ruido',
                'data_type' => 'double',
                'decimals' => 0,
                'min' => 0,
                'max' => 120,
                'unit' => 'dB',
                'description' => 'Detecta ruido en la piscina, gritos o emergencias.',
                'show_last_value' => true,
                'show_line_chart' => true,
            ];

            if (!$noise) {
                $noise = Telemetry::create($telemetryData);
            } else {
                $noise->update($telemetryData);
            }

            // Limpiar reglas previas
            TelemetryRule::where('telemetry_id', $noise->id)->delete();

            // < 60 -> verde
            TelemetryRule::create([
                'telemetry_id' => $noise->id,
                'operator' => '<',
                'threshold' => 60,
                'color' => 'green',
            ]);

            // >= 60 y < 85 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $noise->id,
                'operator' => '>=',
                'threshold' => 60,
                'color' => 'yellow',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $noise->id,
                'operator' => '<',
                'threshold' => 85,
                'color' => 'yellow',
            ]);

            // >= 85 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $noise->id,
                'operator' => '>=',
                'threshold' => 85,
                'color' => 'red',
            ]);
        });
    }
}