<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Telemetry;
use App\Models\TelemetryRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::withCount('telemetries')->orderByDesc('id')->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telemetries' => 'required|array|min:1',
            'telemetries.*.name' => 'required|string|max:255',
            'telemetries.*.data_type' => 'required|string|in:number,double,boolean',
            'telemetries.*.decimals' => 'nullable|integer|min:0|max:10',
            'telemetries.*.min' => 'nullable|numeric',
            'telemetries.*.max' => 'nullable|numeric',
            'telemetries.*.unit' => 'nullable|string|max:50',
            'telemetries.*.description' => 'nullable|string',
            'telemetries.*.false_label' => 'nullable|string|max:50',
            'telemetries.*.true_label' => 'nullable|string|max:50',
            'telemetries.*.show_last_value' => 'nullable|boolean',
            'telemetries.*.show_line_chart' => 'nullable|boolean',
            'telemetries.*.rules' => 'nullable|array',
            'telemetries.*.rules.*.operator' => 'required_with:telemetries.*.rules|string|in:>,<,=,<=,>=',
            'telemetries.*.rules.*.threshold' => 'required_with:telemetries.*.rules|numeric',
            'telemetries.*.rules.*.color' => 'required_with:telemetries.*.rules|string|in:red,green,yellow',
        ]);

        return DB::transaction(function () use ($data) {
            $template = Template::create(['name' => $data['name']]);

            foreach ($data['telemetries'] as $t) {
                $telemetry = Telemetry::create([
                    'template_id' => $template->id,
                    'name' => $t['name'],
                    'data_type' => $t['data_type'],
                    'decimals' => $t['data_type'] === 'double' ? ($t['decimals'] ?? 2) : null,
                    'min' => $t['min'] ?? null,
                    'max' => $t['max'] ?? null,
                    'unit' => $t['unit'] ?? null,
                    'description' => $t['description'] ?? null,
                    'false_label' => $t['data_type'] === 'boolean' ? ($t['false_label'] ?? 'Falso') : null,
                    'true_label' => $t['data_type'] === 'boolean' ? ($t['true_label'] ?? 'Verdadero') : null,
                    'show_last_value' => (bool)($t['show_last_value'] ?? true),
                    'show_line_chart' => (bool)($t['show_line_chart'] ?? false),
                ]);

                if (!empty($t['rules']) && is_array($t['rules'])) {
                    foreach ($t['rules'] as $r) {
                        TelemetryRule::create([
                            'telemetry_id' => $telemetry->id,
                            'operator' => $r['operator'],
                            'threshold' => $r['threshold'],
                            'color' => $r['color'],
                        ]);
                    }
                }
            }

            return redirect()->route('templates.show', $template)->with('status', 'Plantilla creada correctamente');
        });
    }

    public function show(Template $template)
    {
        $template->load(['telemetries.rules']);
        return view('templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telemetries' => 'nullable|array',
            'telemetries.*.id' => 'nullable|integer|exists:telemetries,id',
            'telemetries.*.name' => 'required|string|max:255',
            'telemetries.*.data_type' => 'required|string|in:number,double,boolean',
            'telemetries.*.decimals' => 'nullable|integer|min:0|max:10',
            'telemetries.*.min' => 'nullable|numeric',
            'telemetries.*.max' => 'nullable|numeric',
            'telemetries.*.unit' => 'nullable|string|max:50',
            'telemetries.*.description' => 'nullable|string',
            'telemetries.*.false_label' => 'nullable|string|max:50',
            'telemetries.*.true_label' => 'nullable|string|max:50',
            'telemetries.*.show_last_value' => 'nullable|boolean',
            'telemetries.*.show_line_chart' => 'nullable|boolean',
            'telemetries.*.rules' => 'nullable|array',
            'telemetries.*.rules.*.id' => 'nullable|integer|exists:telemetry_rules,id',
            'telemetries.*.rules.*.operator' => 'required_with:telemetries.*.rules|string|in:>,<,=,<=,>=',
            'telemetries.*.rules.*.threshold' => 'required_with:telemetries.*.rules|numeric',
            'telemetries.*.rules.*.color' => 'required_with:telemetries.*.rules|string|in:red,green,yellow',
        ]);

        return DB::transaction(function () use ($template, $data) {
            $template->update(['name' => $data['name']]);

            // Solo procesar telemetrías si la clave existe en los datos validados
            $hasTelemetries = array_key_exists('telemetries', $data);
            if ($hasTelemetries) {
                $existingTelemetries = $template->telemetries()->with('rules')->get();
                $submittedTelemetryIds = collect($data['telemetries'] ?? [])
                    ->pluck('id')
                    ->filter()
                    ->map(fn($id) => (int)$id)
                    ->all();

                // Eliminar telemetrías que no están en el envío
                foreach ($existingTelemetries as $existing) {
                    if (!in_array($existing->id, $submittedTelemetryIds, true)) {
                        $existing->rules()->delete();
                        $existing->delete();
                    }
                }

                // Upsert de telemetrías y sus reglas
                foreach ($data['telemetries'] ?? [] as $t) {
                    $telemetry = null;
                    if (!empty($t['id'])) {
                        $telemetry = $existingTelemetries->firstWhere('id', (int)$t['id']);
                        if ($telemetry) {
                            $telemetry->update([
                                'name' => $t['name'],
                                'data_type' => $t['data_type'],
                                'decimals' => $t['data_type'] === 'double' ? ($t['decimals'] ?? $telemetry->decimals ?? 2) : null,
                                'min' => $t['min'] ?? null,
                                'max' => $t['max'] ?? null,
                                'unit' => $t['unit'] ?? null,
                                'description' => $t['description'] ?? null,
                                'false_label' => $t['data_type'] === 'boolean' ? ($t['false_label'] ?? $telemetry->false_label ?? 'Falso') : null,
                                'true_label' => $t['data_type'] === 'boolean' ? ($t['true_label'] ?? $telemetry->true_label ?? 'Verdadero') : null,
                                'show_last_value' => (bool)($t['show_last_value'] ?? false),
                                'show_line_chart' => (bool)($t['show_line_chart'] ?? false),
                            ]);
                        }
                    }
                    if (!$telemetry) {
                        $telemetry = Telemetry::create([
                            'template_id' => $template->id,
                            'name' => $t['name'],
                            'data_type' => $t['data_type'],
                            'decimals' => $t['data_type'] === 'double' ? ($t['decimals'] ?? 2) : null,
                            'min' => $t['min'] ?? null,
                            'max' => $t['max'] ?? null,
                            'unit' => $t['unit'] ?? null,
                            'description' => $t['description'] ?? null,
                            'false_label' => $t['data_type'] === 'boolean' ? ($t['false_label'] ?? 'Falso') : null,
                            'true_label' => $t['data_type'] === 'boolean' ? ($t['true_label'] ?? 'Verdadero') : null,
                            'show_last_value' => (bool)($t['show_last_value'] ?? true),
                            'show_line_chart' => (bool)($t['show_line_chart'] ?? false),
                        ]);
                    }

                    // Procesamiento de reglas
                    $existingRuleIds = $telemetry->rules()->pluck('id')->all();
                    $submittedRules = collect($t['rules'] ?? []);
                    $submittedRuleIds = $submittedRules->pluck('id')->filter()->map(fn($id) => (int)$id)->all();

                    // Eliminar reglas faltantes
                    foreach ($existingRuleIds as $rid) {
                        if (!in_array($rid, $submittedRuleIds, true)) {
                            TelemetryRule::where('id', $rid)->delete();
                        }
                    }

                    // Upsert de reglas
                    foreach ($submittedRules as $r) {
                        if (!empty($r['id'])) {
                            $rule = TelemetryRule::where('id', (int)$r['id'])->where('telemetry_id', $telemetry->id)->first();
                            if ($rule) {
                                $rule->update([
                                    'operator' => $r['operator'],
                                    'threshold' => $r['threshold'],
                                    'color' => $r['color'],
                                ]);
                                continue;
                            }
                        }
                        TelemetryRule::create([
                            'telemetry_id' => $telemetry->id,
                            'operator' => $r['operator'],
                            'threshold' => $r['threshold'],
                            'color' => $r['color'],
                        ]);
                    }
                }
            }

            return redirect()->route('templates.show', $template)->with('status', 'Plantilla actualizada');
        });
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('status', 'Plantilla eliminada');
    }
}