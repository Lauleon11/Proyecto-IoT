@extends('layout')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function pickRuleColor(rules, value) {
  if (!rules || !rules.length) return '';
  for (const r of rules) {
    if (r.operator === '>' && value > r.threshold) return r.color;
    if (r.operator === '<' && value < r.threshold) return r.color;
    if (r.operator === '=' && value == r.threshold) return r.color;
  }
  return '';
}
function startSimulation(config) {
  const state = {};
  config.telemetries.forEach((t) => {
    state[t.id] = { values: [], chart: null };
    const valueEl = document.getElementById('value-' + t.id);
    const lvCardEl = document.getElementById('lvcard-' + t.id);

    if (t.show_line_chart) {
      const ctx = document.getElementById('chart-' + t.id).getContext('2d');
      state[t.id].chart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: t.name, data: [], borderColor: '#2563eb', tension: 0.25 }] },
        options: { responsive: true, animation: false, scales: { x: { display: false } } }
      });
    }
  });

  function randomValue(t) {
    if (t.data_type === 'boolean') {
      return Math.random() < 0.5 ? 0 : 1;
    }
    const min = t.min ?? 0;
    const max = t.max ?? 100;
    const v = min + Math.random() * (max - min);
    if (t.data_type === 'double' || t.data_type === 'float') {
      const d = (typeof t.decimals === 'number') ? t.decimals : 2;
      return parseFloat(v.toFixed(d));
    }
    return Math.round(v);
  }

  function tick() {
    config.telemetries.forEach((t) => {
      const val = randomValue(t);
      const valueEl = document.getElementById('value-' + t.id);
      const lvCardEl = document.getElementById('lvcard-' + t.id);

      if (valueEl) {
        if (t.data_type === 'boolean') {
          valueEl.textContent = val ? (t.true_label || 'Verdadero') : (t.false_label || 'Falso');
        } else {
          const display = (t.data_type === 'double' && typeof t.decimals === 'number')
            ? Number(val).toFixed(t.decimals)
            : val;
          valueEl.textContent = display + (t.unit ? ' ' + t.unit : '');
        }
      }

      if (lvCardEl) {
        const color = pickRuleColor(t.rules, val);
        lvCardEl.classList.remove('red','green','yellow');
        if (color) lvCardEl.classList.add(color);
      }

      const s = state[t.id];
      if (t.show_line_chart && s.chart) {
        const ds = s.chart.data.datasets[0];
        s.chart.data.labels.push('');
        ds.data.push(val);
        if (ds.data.length > 20) {
          ds.data.shift();
          s.chart.data.labels.shift();
        }
        s.chart.update();
      }
    });
  }

  tick();
  return setInterval(tick, 5000);
}
</script>
@endsection

@section('content')
<div class="topbar">
    <h2>Dispositivo: {{ $device->name ?? 'Sin nombre' }}</h2>
    <div style="display:flex; gap:8px;">
      <a class="btn" href="{{ route('devices.index') }}">Volver</a>
      <a class="btn btn-outline" href="{{ route('devices.edit', $device) }}">Editar</a>
      <form method="post" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm('¿Eliminar dispositivo?');">
        @csrf
        @method('DELETE')
      </form>
    </div>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="grid grid-3">
  <div class="card"><strong>Device ID:</strong> <span class="muted">{{ $device->device_id }}</span></div>
  <div class="card"><strong>Scope ID:</strong> <span class="muted">{{ $device->scope_id }}</span></div>
  <div class="card"><strong>Key:</strong> <span class="muted">{{ $device->key }}</span></div>
</div>
<div class="card" style="margin-top:12px;">
  <form method="post" action="{{ route('devices.toggle', $device) }}" style="display:flex; gap:8px; align-items:center;">
    @csrf
    <span>Estado: @if($device->is_on) <span class="status green">Encendido</span> @else <span class="status red">Apagado</span> @endif</span>
    <button class="btn btn-primary" type="submit">{{ $device->is_on ? 'Apagar' : 'Encender' }}</button>
  </form>
</div>

<div style="margin-top:12px;" class="grid grid-2">
  @foreach($device->template->telemetries as $t)
    @if($t->show_last_value)
    <div id="lvcard-{{ $t->id }}" class="card">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong>{{ $t->name }}</strong>
        <span class="tag">{{ $t->data_type }}</span>
      </div>
      <div style="margin-top:8px;">Último valor: <strong id="value-{{ $t->id }}" class="muted">—</strong></div>
      @if($t->rules->count())
        <div style="margin-top:8px;" class="muted">Reglas: 
          @foreach($t->rules as $r)
            <span class="status {{ $r->color }}">{{ $r->operator }} {{ $r->threshold }} → {{ $r->color }}</span>
          @endforeach
        </div>
      @endif
    </div>
    @endif

    @if($t->show_line_chart)
    <div class="card">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong>{{ $t->name }} — Gráfica</strong>
        <span class="tag">{{ $t->data_type }}</span>
      </div>
      <canvas id="chart-{{ $t->id }}" height="120"></canvas>
    </div>
    @endif
  @endforeach
</div>

@if($device->is_on)
@php
  $telemetriesPayload = $device->template->telemetries
    ->map(function($t){
      return [
        'id' => $t->id,
        'name' => $t->name,
        'data_type' => $t->data_type,
        'decimals' => $t->decimals,
        'min' => $t->min,
        'max' => $t->max,
        'unit' => $t->unit,
        'true_label' => $t->true_label,
        'false_label' => $t->false_label,
        'show_last_value' => $t->show_last_value,
        'show_line_chart' => $t->show_line_chart,
        'rules' => $t->rules
          ->map(function($r){
            return [
              'operator' => $r->operator,
              'threshold' => $r->threshold,
              'color' => $r->color,
            ];
          })
          ->values()
          ->toArray(),
      ];
    })
    ->values()
    ->toArray();
@endphp
<script>
  const deviceConfig = {
    telemetries: @json($telemetriesPayload)
  };
  startSimulation(deviceConfig);
</script>
@endif
@endsection