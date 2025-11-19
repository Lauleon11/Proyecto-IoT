<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;

class WaterQualityTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Crear o recuperar la plantilla
            $template = Template::firstOrCreate([
                'name' => 'sensor de calidad del agua',
            ]);

            // -------------------- Cloro --------------------
            $cloro = Telemetry::where('template_id', $template->id)
                ->where('name', 'cloro')
                ->first();

            if (!$cloro) {
                $cloro = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'cloro',
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'ppm',
                    'description' => 'Nivel de cloro en el agua.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $cloro->update([
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'ppm',
                    'description' => 'Nivel de cloro en el agua.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $cloro->id)->delete();

            // < 1 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $cloro->id,
                'operator' => '<',
                'threshold' => 1.0,
                'color' => 'red',
            ]);
            // >= 1 y < 3 -> verde
            TelemetryRule::create([
                'telemetry_id' => $cloro->id,
                'operator' => '>=',
                'threshold' => 1.0,
                'color' => 'green',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $cloro->id,
                'operator' => '<',
                'threshold' => 3.0,
                'color' => 'green',
            ]);
            // >= 3 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $cloro->id,
                'operator' => '>=',
                'threshold' => 3.0,
                'color' => 'yellow',
            ]);

            // -------------------- pH --------------------
            $ph = Telemetry::where('template_id', $template->id)
                ->where('name', 'ph')
                ->first();

            if (!$ph) {
                $ph = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'ph',
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 0,
                    'max' => 14,
                    'unit' => 'pH',
                    'description' => 'Acidez o alcalinidad del agua.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $ph->update([
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 0,
                    'max' => 14,
                    'unit' => 'pH',
                    'description' => 'Acidez o alcalinidad del agua.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $ph->id)->delete();

            // < 7.2 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $ph->id,
                'operator' => '<',
                'threshold' => 7.2,
                'color' => 'yellow',
            ]);
            // >= 7.2 y < 7.6 -> verde
            TelemetryRule::create([
                'telemetry_id' => $ph->id,
                'operator' => '>=',
                'threshold' => 7.2,
                'color' => 'green',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $ph->id,
                'operator' => '<',
                'threshold' => 7.6,
                'color' => 'green',
            ]);
            // >= 7.6 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $ph->id,
                'operator' => '>=',
                'threshold' => 7.6,
                'color' => 'yellow',
            ]);
        });
    }
}