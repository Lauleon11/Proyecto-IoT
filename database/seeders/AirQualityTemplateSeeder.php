<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;

class AirQualityTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Crear o recuperar la plantilla
            $template = Template::firstOrCreate([
                'name' => 'sensor de calidad del aire',
            ]);

            // Crear o actualizar la telemetría PM2.5
            $telemetry = Telemetry::where('template_id', $template->id)
                ->where('name', 'pm25')
                ->first();

            if (!$telemetry) {
                $telemetry = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'pm25',
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 1,
                    'max' => 50,
                    'unit' => 'ug/m3',
                    'description' => 'Concentración de partículas finas en el aire.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $telemetry->update([
                    'data_type' => 'double',
                    'decimals' => 2,
                    'min' => 10,
                    'max' => 150,
                    'unit' => 'ug/m3',
                    'description' => 'Concentración de partículas finas en el aire.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            // Limpiar reglas previas para garantizar solo las 3 definitivas
            TelemetryRule::where('telemetry_id', $telemetry->id)->delete();

            // Reglas: < 12 -> verde
            TelemetryRule::create([
                'telemetry_id' => $telemetry->id,
                'operator' => '<',
                'threshold' => 12,
                'color' => 'green',
            ]);

            // Reglas: >= 12 y < 35 -> amarillo (dos reglas)
            TelemetryRule::create([
                'telemetry_id' => $telemetry->id,
                'operator' => '>=',
                'threshold' => 12,
                'color' => 'yellow',
            ]);

            TelemetryRule::create([
                'telemetry_id' => $telemetry->id,
                'operator' => '<',
                'threshold' => 35,
                'color' => 'yellow',
            ]);

            // Reglas: >= 35 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $telemetry->id,
                'operator' => '>=',
                'threshold' => 35,
                'color' => 'red',
            ]);

            // -------------------- VOC --------------------
            $voc = Telemetry::where('template_id', $template->id)
                ->where('name', 'voc')
                ->first();

            if (!$voc) {
                $voc = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'voc',
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 6,
                    'unit' => 'ppm',
                    'description' => 'Nivel de compuestos volátiles (calidad de aire).',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $voc->update([
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 6,
                    'unit' => 'ppm',
                    'description' => 'Nivel de compuestos volátiles (calidad de aire).',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $voc->id)->delete();

            // < 0.3 -> verde
            TelemetryRule::create([
                'telemetry_id' => $voc->id,
                'operator' => '<',
                'threshold' => 0.3,
                'color' => 'green',
            ]);

            // >= 0.3 y < 0.5 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $voc->id,
                'operator' => '>=',
                'threshold' => 0.3,
                'color' => 'yellow',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $voc->id,
                'operator' => '<',
                'threshold' => 0.5,
                'color' => 'yellow',
            ]);

            // >= 0.5 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $voc->id,
                'operator' => '>=',
                'threshold' => 0.5,
                'color' => 'red',
            ]);

            // -------------------- CO --------------------
            $co = Telemetry::where('template_id', $template->id)
                ->where('name', 'co')
                ->first();

            if (!$co) {
                $co = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'co',
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 2500,
                    'unit' => 'ppm',
                    'description' => 'Monóxido de carbono en ppm.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $co->update([
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 2500,
                    'unit' => 'ppm',
                    'description' => 'Monóxido de carbono en ppm.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $co->id)->delete();

            // < 800 -> verde
            TelemetryRule::create([
                'telemetry_id' => $co->id,
                'operator' => '<',
                'threshold' => 800,
                'color' => 'green',
            ]);
            // >= 800 y < 1000 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $co->id,
                'operator' => '>=',
                'threshold' => 800,
                'color' => 'yellow',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $co->id,
                'operator' => '<',
                'threshold' => 1000,
                'color' => 'yellow',
            ]);
            // > 1000 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $co->id,
                'operator' => '>',
                'threshold' => 1000,
                'color' => 'red',
            ]);

            // -------------------- Temperatura --------------------
            $temp = Telemetry::where('template_id', $template->id)
                ->where('name', 'temperatura')
                ->first();

            if (!$temp) {
                $temp = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'temperatura',
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 10,
                    'max' => 35,
                    'unit' => '°C',
                    'description' => 'Temperatura ambiente en el salón.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $temp->update([
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 10,
                    'max' => 35,
                    'unit' => '°C',
                    'description' => 'Temperatura ambiente en el salón.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $temp->id)->delete();

            // < 18 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $temp->id,
                'operator' => '<',
                'threshold' => 18,
                'color' => 'yellow',
            ]);
            // >= 18 y < 30 -> verde
            TelemetryRule::create([
                'telemetry_id' => $temp->id,
                'operator' => '>=',
                'threshold' => 18,
                'color' => 'green',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $temp->id,
                'operator' => '<',
                'threshold' => 30,
                'color' => 'green',
            ]);
            // > 30 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $temp->id,
                'operator' => '>',
                'threshold' => 30,
                'color' => 'red',
            ]);

            // -------------------- Humedad --------------------
            $hum = Telemetry::where('template_id', $template->id)
                ->where('name', 'humedad')
                ->first();

            if (!$hum) {
                $hum = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => 'humedad',
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'description' => 'Humedad relativa del aire.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            } else {
                $hum->update([
                    'data_type' => 'double',
                    'decimals' => 1,
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'description' => 'Humedad relativa del aire.',
                    'show_last_value' => true,
                    'show_line_chart' => true,
                ]);
            }

            TelemetryRule::where('telemetry_id', $hum->id)->delete();

            // < 40 -> amarillo
            TelemetryRule::create([
                'telemetry_id' => $hum->id,
                'operator' => '<',
                'threshold' => 40,
                'color' => 'yellow',
            ]);
            // >= 40 y < 60 -> verde
            TelemetryRule::create([
                'telemetry_id' => $hum->id,
                'operator' => '>=',
                'threshold' => 40,
                'color' => 'green',
            ]);
            TelemetryRule::create([
                'telemetry_id' => $hum->id,
                'operator' => '<',
                'threshold' => 60,
                'color' => 'green',
            ]);
            // > 60 -> rojo
            TelemetryRule::create([
                'telemetry_id' => $hum->id,
                'operator' => '>',
                'threshold' => 60,
                'color' => 'red',
            ]);
        });
    }
}