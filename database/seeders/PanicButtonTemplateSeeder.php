<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;

class PanicButtonTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Crear o recuperar la plantilla
            $template = Template::firstOrCreate([
                'name' => 'boton de panico',
            ]);

            // Telemetría: boton_presionado (boolean)
            $btn = Telemetry::where('template_id', $template->id)
                ->where('name', 'boton_presionado')
                ->first();

            $telemetryData = [
                'template_id' => $template->id,
                'name' => 'boton_presionado',
                'data_type' => 'boolean',
                'decimals' => null,
                'min' => null,
                'max' => null,
                'unit' => null,
                'description' => 'Indica si el botón de pánico está presionado.',
                'false_label' => 'no presionado',
                'true_label' => 'presionado',
                'show_last_value' => true,
                'show_line_chart' => false,
            ];

            if (!$btn) {
                $btn = Telemetry::create($telemetryData);
            } else {
                $btn->update($telemetryData);
            }

            // Limpiar reglas previas
            TelemetryRule::where('telemetry_id', $btn->id)->delete();

            // = true (1) -> rojo
            TelemetryRule::create([
                'telemetry_id' => $btn->id,
                'operator' => '=',
                'threshold' => 1,
                'color' => 'red',
            ]);

            // = false (0) -> verde
            TelemetryRule::create([
                'telemetry_id' => $btn->id,
                'operator' => '=',
                'threshold' => 0,
                'color' => 'green',
            ]);
        });
    }
}