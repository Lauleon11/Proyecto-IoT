<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Panel;
use App\Models\PanelItem;
use App\Models\Telemetry;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelController extends Controller
{
    public function index()
    {
        $panels = Panel::withCount('items')->orderBy('id', 'desc')->get();
        return view('dashboards.index', compact('panels'));
    }

    public function create()
    {
        $templates = Template::with('telemetries')->get();
        $devices = Device::orderBy('name')->get();
        return view('dashboards.create', compact('templates', 'devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.device_id' => ['required', 'integer', 'exists:devices,id'],
            'items.*.telemetry_id' => ['required', 'integer', 'exists:telemetries,id'],
            'items.*.viz_type' => ['required', 'in:last,line'],
        ]);

        DB::transaction(function () use ($validated) {
            $panel = Panel::create(['name' => $validated['name']]);

            foreach ($validated['items'] as $index => $item) {
                PanelItem::create([
                    'panel_id' => $panel->id,
                    'device_id' => $item['device_id'],
                    'telemetry_id' => $item['telemetry_id'],
                    'viz_type' => $item['viz_type'],
                    'position' => $index,
                ]);
            }
        });

        return redirect()->route('dashboards.index')->with('status', 'Panel creado correctamente');
    }

    public function show(Panel $panel)
    {
        $panel->load(['items' => function ($q) {
            $q->orderBy('position');
        }, 'items.device.template', 'items.telemetry.rules']);

        $templates = Template::with('telemetries')->get();
        $devices = Device::orderBy('name')->get();

        return view('dashboards.show', compact('panel', 'templates', 'devices'));
    }

    public function edit(Panel $panel)
    {
        $panel->load(['items' => function ($q) {
            $q->orderBy('position');
        }, 'items.device.template', 'items.telemetry.rules']);

        $templates = Template::with('telemetries')->get();
        $devices = Device::orderBy('name')->get();

        return view('dashboards.edit', compact('panel', 'templates', 'devices'));
    }

    public function update(Request $request, Panel $panel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:panel_items,id'],
            'items.*.device_id' => ['required', 'integer', 'exists:devices,id'],
            'items.*.telemetry_id' => ['required', 'integer', 'exists:telemetries,id'],
            'items.*.viz_type' => ['required', 'in:last,line'],
        ]);

        DB::transaction(function () use ($validated, $panel) {
            $panel->update(['name' => $validated['name']]);

            $existingItems = $panel->items()->get();
            $submittedIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($existingItems as $existing) {
                if (!in_array($existing->id, $submittedIds, true)) {
                    $existing->delete();
                }
            }

            foreach ($validated['items'] as $index => $item) {
                if (!empty($item['id'])) {
                    $pi = PanelItem::where('panel_id', $panel->id)->where('id', $item['id'])->first();
                    if ($pi) {
                        $pi->update([
                            'device_id' => $item['device_id'],
                            'telemetry_id' => $item['telemetry_id'],
                            'viz_type' => $item['viz_type'],
                            'position' => $index,
                        ]);
                    }
                } else {
                    PanelItem::create([
                        'panel_id' => $panel->id,
                        'device_id' => $item['device_id'],
                        'telemetry_id' => $item['telemetry_id'],
                        'viz_type' => $item['viz_type'],
                        'position' => $index,
                    ]);
                }
            }
        });

        return redirect()->route('dashboards.show', $panel)->with('status', 'Panel actualizado correctamente');
    }

    protected function evaluateColor(Telemetry $telemetry, $value): string
    {
        $rulesByColor = ['red' => [], 'yellow' => [], 'green' => []];
        foreach ($telemetry->rules as $r) {
            $rulesByColor[$r->color][] = $r;
        }
        $apply = function(array $rules) use ($value) {
            if (empty($rules)) return false;
            foreach ($rules as $r) {
                switch ($r->operator) {
                    case '>': if (!($value > $r->threshold)) return false; break;
                    case '>=': if (!($value >= $r->threshold)) return false; break;
                    case '<': if (!($value < $r->threshold)) return false; break;
                    case '<=': if (!($value <= $r->threshold)) return false; break;
                    case '=': if (!($value == $r->threshold)) return false; break;
                    default: return false;
                }
            }
            return true;
        };
        if ($apply($rulesByColor['red'])) return 'red';
        if ($apply($rulesByColor['yellow'])) return 'yellow';
        if ($apply($rulesByColor['green'])) return 'green';
        return 'gray';
    }

    public function data(Panel $panel)
    {
        $panel->load(['items.telemetry.rules', 'items.device']);
        $payload = [];
        foreach ($panel->items as $it) {
            $tele = $it->telemetry;
            // Soporte para boolean: usar etiquetas personalizadas o por defecto
            if (($tele->data_type ?? '') === 'boolean') {
                $boolVal = (bool) random_int(0, 1);
                $nameLower = mb_strtolower($tele->name);
                $isButton = str_contains($nameLower, 'boton') || str_contains($nameLower, 'botón') || str_contains($nameLower, 'panic') || str_contains($nameLower, 'pánico') || str_contains($nameLower, 'panico');
                $trueLabel = $tele->true_label ?? ($isButton ? 'presionado' : 'Verdadero');
                $falseLabel = $tele->false_label ?? ($isButton ? 'no presionado' : 'Falso');
                $color = $this->evaluateColor($tele, $boolVal ? 1 : 0);
                $payload[] = [
                    'item_id' => $it->id,
                    'device' => $it->device->name,
                    'telemetry' => $tele->name,
                    'value' => $boolVal ? 1 : 0,
                    'text' => $boolVal ? $trueLabel : $falseLabel,
                    'decimals' => null,
                    'unit' => null,
                    'color' => $color,
                    'data_type' => 'boolean',
                ];
                continue;
            }
            // Número/double: simulación y formato
            $min = is_numeric($tele->min) ? (float)$tele->min : 0;
            $max = is_numeric($tele->max) ? (float)$tele->max : 100;
            if ($max <= $min) { $max = $min + 100; }
            $value = round($min + ((mt_rand()/mt_getrandmax()) * ($max - $min)), $tele->decimals ?? 2);
            $color = $this->evaluateColor($tele, $value);
            $payload[] = [
                'item_id' => $it->id,
                'device' => $it->device->name,
                'telemetry' => $tele->name,
                'value' => $value,
                'decimals' => $tele->decimals ?? 2,
                'unit' => $tele->unit,
                'color' => $color,
                'data_type' => $tele->data_type ?? 'number',
            ];
        }
        return response()->json(['items' => $payload]);
    }
}