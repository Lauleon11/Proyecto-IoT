@extends('layout')

@section('content')
<div class="topbar">
    <h2>Plantilla: {{ $template->name }}</h2>
    <div style="display:flex; gap:8px;">
        <a class="btn" href="{{ route('templates.index') }}">Volver</a>
        <a class="btn btn-outline" href="{{ route('templates.edit', $template) }}">Editar</a>
        <form method="post" action="{{ route('templates.destroy', $template) }}" onsubmit="return confirm('¿Eliminar plantilla?');">
            @csrf
            @method('DELETE')
            <button class="btn" type="submit">Eliminar</button>
        </form>
    </div>
</div>
<div class="grid grid-2">
    <div class="card">
        <h3>Telemetrías</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Rango</th>
                    <th>Unidad</th>
                    <th>Visualización</th>
                </tr>
            </thead>
            <tbody>
            @foreach($template->telemetries as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->data_type }}</td>
                    <td>
                        @if($t->data_type === 'boolean')
                            <span class="tag">{{ $t->false_label ?? 'Falso' }}</span> / <span class="tag">{{ $t->true_label ?? 'Verdadero' }}</span>
                        @else
                            {{ $t->min }} - {{ $t->max }} @if($t->data_type === 'float') ({{ $t->decimals }} dec) @endif
                        @endif
                    </td>
                    <td>{{ $t->unit }}</td>
                    <td>
                        @if($t->show_last_value) <span class="tag">Último valor</span> @endif
                        @if($t->show_line_chart) <span class="tag">Líneas</span> @endif
                    </td>
                </tr>
                @if($t->rules->count())
                <tr>
                    <td colspan="5">
                        <strong>Reglas:</strong>
                        @php
                            $rules = $t->rules->values();
                            $used = [];
                            $display = [];
                            for ($i = 0; $i < $rules->count(); $i++) {
                                if (!empty($used[$i])) continue;
                                $r = $rules[$i];
                                // Try to merge as BETWEEN when same color has complementary operator
                                $merged = false;
                                if (in_array($r->operator, ['>', '>='])) {
                                    for ($j = 0; $j < $rules->count(); $j++) {
                                        if ($j === $i || !empty($used[$j])) continue;
                                        $p = $rules[$j];
                                        if ($p->color === $r->color && in_array($p->operator, ['<', '<=']) && $p->threshold > $r->threshold) {
                                            $display[] = [
                                                'text' => $t->name.' '.$r->operator.' '.$r->threshold.' AND '.$p->operator.' '.$p->threshold.' → '.$r->color,
                                                'color' => $r->color,
                                            ];
                                            $used[$i] = true; $used[$j] = true;
                                            $merged = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$merged) {
                                    $display[] = [
                                        'text' => $t->name.' '.$r->operator.' '.$r->threshold.' → '.$r->color,
                                        'color' => $r->color,
                                    ];
                                    $used[$i] = true;
                                }
                            }
                        @endphp
                        @foreach($display as $line)
                            <span class="status {{ $line['color'] }}">{{ $line['text'] }}</span>
                        @endforeach
                    </td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3>Descripción</h3>
        <p class="muted">Configura visualizaciones por telemetría. La vista/panel se guarda en cada telemetría mediante las banderas de visualización.</p>
    </div>
</div>
@endsection