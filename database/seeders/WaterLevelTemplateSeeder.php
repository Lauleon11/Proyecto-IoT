<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;

class WaterLevelTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Crear o recuperar la plantilla
            $template = Template::firstOrCreate([
                'name' => 'sensor de nivel del agua',
            ]);

            // Telemetría: nivel_agua
            $nivel = Telemetry::where('template_id', $template->id)
                ->where('name', 'nivel_agua')
                ->first();

            if (!$nivel) {
                $nivel = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'nivel_agua',
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'description' => 'Porcentaje de llenado de la piscina.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $nivel->update([
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'description' => 'Porcentaje de llenado de la piscina.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            // Limpiar reglas previas
            TelemetryRule::where('telemetry_id', $nivel->id)->delete();

            // < 50 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $nivel->id,
                'operator' => '<',
                'threshold' => 50,
                'color' => 'yellow',
            ]);
            // >= 50 y < 90 -> verde
            TelemetryRule::create([
                'telemetry_id' => $nivel->id,
                'operator' => '>=',
                'threshold' => 50,
                'color' => 'green',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $nivel->id,
                'operator' => '<',
                'threshold' => 90,
                'color' => 'green',
            ]);
            // >= 90 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $nivel->id,
                'operator' => '>=',
                'threshold' => 90,
                'color' => 'red',
            ]);
        });
    }
}