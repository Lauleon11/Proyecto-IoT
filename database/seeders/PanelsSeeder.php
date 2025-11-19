<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Panel;
use App\Models\PanelItem;
use App\Models\Device;
use App\Models\Template;
use App\Models\Telemetry;

class PanelsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Helper: resolve device by name
            $device = fn($name) => Device::where('name', $name)->first();
            // Helper: resolve telemetry by template name + telemetry name
            $telemetry = function(string $templateName, string $telemetryName) {
                $template = Template::where('name', $templateName)->first();
                if (!$template) return null;
                return Telemetry::where('template_id', $template->id)->where('name', $telemetryName)->first();
            };

            // Panel 1: General Colegio
            $general = Panel::firstOrCreate(['name' => 'General Colegio']);
            $pos = 0;
            $airs = [
                'Sensor aire - Salon 1',
                'Sensor aire - Salon 2',
                'Sensor aire - Salon 3',
                'Sensor aire - Salon 4',
            ];
            $airTelems = ['co','humedad','temperatura','pm25','voc'];
            foreach ($airs as $dn) {
                $d = $device($dn);
                foreach ($airTelems as $tn) {
                    $t = $telemetry('sensor de calidad del aire', $tn);
                    if ($d && $t) {
                        PanelItem::updateOrCreate(
                            ['panel_id' => $general->id, 'device_id' => $d->id, 'telemetry_id' => $t->id],
                            ['viz_type' => 'last', 'position' => $pos++]
                        );
                    }
                }
                // Add CO line chart per salon
                $tco = $telemetry('sensor de calidad del aire', 'co');
                if ($d && $tco) {
                    PanelItem::updateOrCreate(
                        ['panel_id' => $general->id, 'device_id' => $d->id, 'telemetry_id' => $tco->id],
                        ['viz_type' => 'line', 'position' => $pos++]
                    );
                }
            }

            // Botón de Pánico: último valor por cada salón (presionado / no presionado)
            $panicRooms = [
                'Botón de Pánico - Salon 1',
                'Botón de Pánico - Salon 2',
                'Botón de Pánico - Salon 3',
                'Botón de Pánico - Salon 4',
            ];
            $btnTelemetry = $telemetry('boton de panico', 'boton_presionado');
            foreach ($panicRooms as $dn) {
                $d = $device($dn);
                if ($d && $btnTelemetry) {
                    PanelItem::updateOrCreate(
                        ['panel_id' => $general->id, 'device_id' => $d->id, 'telemetry_id' => $btnTelemetry->id],
                        ['viz_type' => 'last', 'position' => $pos++]
                    );
                }
            }

            // Panel 2: Piscina Detallado
            $pool = Panel::firstOrCreate(['name' => 'Piscina Detallado']);
            $pos = 0;
            $dPoolQuality = $device('Sensor agua - Piscina');
            $dPoolLevel = $device('Sensor nivel agua - Piscina');
            $dPoolNoise = $device('Sensor sonido/ruido - Piscina');
            $dPoolPanic = $device('Botón de Pánico - Piscina');

            $tCloro = $telemetry('sensor de calidad del agua', 'cloro');
            $tPh    = $telemetry('sensor de calidad del agua', 'ph');
            $tNivel = $telemetry('sensor de nivel del agua', 'nivel_agua');
            $tNoise = $telemetry('sensor de sonido/ruido', 'nivel_ruido');
            $tBtn   = $telemetry('boton de panico', 'boton_presionado');

            // Last values
            if ($dPoolQuality && $tCloro) PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolQuality->id,'telemetry_id'=>$tCloro->id,'viz_type'=>'last'], ['position'=>$pos++]);
            if ($dPoolQuality && $tPh)    PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolQuality->id,'telemetry_id'=>$tPh->id,'viz_type'=>'last'],     ['position'=>$pos++]);
            if ($dPoolLevel && $tNivel)   PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolLevel->id,'telemetry_id'=>$tNivel->id,'viz_type'=>'last'],   ['position'=>$pos++]);
            if ($dPoolNoise && $tNoise)   PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolNoise->id,'telemetry_id'=>$tNoise->id,'viz_type'=>'last'],   ['position'=>$pos++]);
            if ($dPoolPanic && $tBtn)     PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolPanic->id,'telemetry_id'=>$tBtn->id,'viz_type'=>'last'],     ['position'=>$pos++]);

            // Line charts
            if ($dPoolQuality && $tCloro) PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolQuality->id,'telemetry_id'=>$tCloro->id,'viz_type'=>'line'], ['position'=>$pos++]);
            if ($dPoolQuality && $tPh)    PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolQuality->id,'telemetry_id'=>$tPh->id,'viz_type'=>'line'],     ['position'=>$pos++]);
            if ($dPoolLevel && $tNivel)   PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolLevel->id,'telemetry_id'=>$tNivel->id,'viz_type'=>'line'],   ['position'=>$pos++]);
            if ($dPoolNoise && $tNoise)   PanelItem::updateOrCreate(['panel_id'=>$pool->id,'device_id'=>$dPoolNoise->id,'telemetry_id'=>$tNoise->id,'viz_type'=>'line'],   ['position'=>$pos++]);
        });
    }
}