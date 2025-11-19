@extends('layout')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const palette = [
    '#2563eb', '#16a34a', '#dc2626', '#f59e0b', '#7c3aed', '#0ea5e9', '#f43f5e', '#34d399', '#fde047', '#22c55e'
  ];

  function formatDate(dt) {
    const pad = n => (n < 10 ? ('0' + n) : n);
    return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()) + ' ' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
  }

  function parseInterval(val) {
    switch (val) {
      case '5m':
        return 5 * 60 * 1000;
      case '10m':
        return 10 * 60 * 1000;
      case '15m':
        return 15 * 60 * 1000;
      case '30m':
        return 30 * 60 * 1000;
      case '1h':
        return 60 * 60 * 1000;
      case '1d':
        return 24 * 60 * 60 * 1000;
      default:
        return 15 * 60 * 1000;
    }
  }

  function generateSeries(telemetries, start, end, intervalMs) {
    const labels = [];
    const timestamps = [];
    for (let t = start.getTime(); t <= end.getTime(); t += intervalMs) {
      labels.push(new Date(t));
      timestamps.push(t);
    }
    const datasets = telemetries.map((t, i) => {
      const data = labels.map(() => {
        if (t.data_type === 'boolean') return Math.random() < 0.5 ? 0 : 1;
        const min = t.min ?? 0;
        const max = t.max ?? 100;
        let v = min + Math.random() * (max - min);
        if (t.data_type === 'float') v = parseFloat(v.toFixed(t.decimals ?? 2));
        else v = Math.round(v);
        return v;
      });
      return {
        label: t.name,
        data,
        borderColor: palette[i % palette.length],
        tension: 0.25
      };
    });
    return {
      labels: labels.map(formatDate),
      datasets
    };
  }

  function updateDependentSelects() {
    const tmplSel = document.getElementById('template_id');
    const devSel = document.getElementById('device_ids');
    const telSel = document.getElementById('telemetry_ids');
    const tmplId = parseInt(tmplSel.value);
    const meta = window.templatesMeta.find(t => t.id === tmplId);
    devSel.querySelectorAll('option').forEach(o => o.remove());
    window.devicesByTemplate[tmplId]?.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.name || ('Dispositivo #' + d.id);
      devSel.appendChild(opt);
    });
    telSel.querySelectorAll('option').forEach(o => o.remove());
    meta?.telemetries.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = `${t.name} (${t.data_type})`;
      telSel.appendChild(opt);
    });
  }

  function selectAll(id) {
    const sel = document.getElementById(id);
    Array.from(sel.options).forEach(o => o.selected = true);
  }

  function safeParseLocalDateTime(str) {
    if (!str) return null;
    const d = new Date(str);
    return isNaN(d.getTime()) ? null : d;
  }
  function computeRange(range, startStr, endStr) {
    const now = new Date();
    switch (range) {
      case '1d':
        return { start: new Date(now.getTime() - 24*60*60*1000), end: now };
      case '3d':
        return { start: new Date(now.getTime() - 3*24*60*60*1000), end: now };
      case '7d':
        return { start: new Date(now.getTime() - 7*24*60*60*1000), end: now };
      case 'custom':
        return null;
      default:
        return { start: new Date(now.getTime() - 24*60*60*1000), end: now };
    }
  }
  function onSubmit(e) {
    e.preventDefault();
    const tmplId = parseInt(document.getElementById('template_id').value);
    const telSel = document.getElementById('telemetry_ids');
    const selectedTelemetryIds = Array.from(telSel.selectedOptions).map(o => parseInt(o.value));
    const meta = window.templatesMeta.find(t => t.id === tmplId);
    const telemetries = meta.telemetries.filter(t => selectedTelemetryIds.includes(t.id));
    const startStr = document.getElementById('start').value;
    const endStr = document.getElementById('end').value;
    const intervalStr = document.getElementById('interval').value;
    const rangeVal = document.getElementById('range').value;
    const range = computeRange(rangeVal, startStr, endStr);
    let start, end;
    if (range) {
      start = range.start;
      end = range.end;
    } else {
      start = safeParseLocalDateTime(startStr) || new Date(Date.now() - 24*60*60*1000);
      end = safeParseLocalDateTime(endStr) || new Date();
    }
    const data = generateSeries(telemetries, start, end, parseInterval(intervalStr));
    const ctx = document.getElementById('explorerChart').getContext('2d');
    if (window.explorerChart && typeof window.explorerChart.destroy === 'function') {
      window.explorerChart.destroy();
    }
    window.explorerChart = new Chart(ctx, {
      type: 'line',
      data: data,
      options: {
        responsive: true,
        animation: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          x: {
            ticks: {
              maxRotation: 0,
              autoSkip: true
            }
          }
        }
      }
    });
  }
  @php
    $templatesMeta = $templates->map(function($t) {
      return [
        'id' => $t->id,
        'name' => $t->name,
        'telemetries' => $t->telemetries->map(function($m) {
          return [
            'id' => $m->id,
            'name' => $m->name,
            'data_type' => $m->data_type,
            'decimals' => $m->decimals,
            'min' => $m->min,
            'max' => $m->max,
          ];
        })->values()->all(),
      ];
    })->values()->all();
    $devicesByTemplate = $devices->groupBy('template_id')->map(function($grp) {
      return $grp->map(function($d) {
        return [
          'id' => $d->id,
          'name' => $d->name
        ];
      })->values()->all();
    })->all();
  @endphp
  window.templatesMeta = @json($templatesMeta);

  window.devicesByTemplate = @json($devicesByTemplate);
  document.addEventListener('DOMContentLoaded', function() {
    updateDependentSelects();
    document.getElementById('template_id').addEventListener('change', updateDependentSelects);
    document.getElementById('configForm').addEventListener('submit', onSubmit);
    document.getElementById('configForm').requestSubmit();
  });
</script>
@endsection

@section('content')
<div class="topbar">
  <h2>Explorador de Datos</h2>
</div>
<div class="card">
  <form id="configForm">
    <div class="grid grid-3">
      <div>
        <label class="label">Plantilla</label>
        <select class="input" id="template_id" name="template_id" required>
          @foreach($templates as $t)
          <option value="{{ $t->id }}" @selected($prefill['template_id']===$t->id)>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Dispositivos</label>
        <div style="display:flex; gap:8px;">
          <select multiple size="6" class="input" id="device_ids" name="device_ids[]" style="height:120px;"></select>
          <button type="button" class="btn" onclick="selectAll('device_ids')">Seleccionar todos</button>
        </div>
      </div>
      <div>
        <label class="label">Telemetrías</label>
        <div style="display:flex; gap:8px;">
          <select multiple size="6" class="input" id="telemetry_ids" name="telemetry_ids[]" style="height:120px;"></select>
          <button type="button" class="btn" onclick="selectAll('telemetry_ids')">Seleccionar todas</button>
        </div>
      </div>
    </div>
    <div class="grid grid-4" style="margin-top:12px;">
      <div>
        <label class="label">Inicio</label>
        <input type="datetime-local" class="input" id="start" name="start" value="{{ $prefill['start'] ?? '' }}" />
      </div>
      <div>
        <label class="label">Fin</label>
        <input type="datetime-local" class="input" id="end" name="end" value="{{ $prefill['end'] ?? '' }}" />
      </div>
      <div>
        <label class="label">Rango</label>
        <select class="input" id="range" name="range">
          <option value="1d" selected>Último día</option>
          <option value="3d">Últimos 3 días</option>
          <option value="7d">Última semana</option>
          <option value="custom">Personalizado</option>
        </select>
      </div>
      <div>
        <label class="label">Intervalo de muestreo</label>
        <select class="input" id="interval" name="interval">
          <option value="5m" @selected(($prefill['interval'] ?? '' )==='5m' )>Cada 5 minutos</option>
          <option value="10m" @selected(($prefill['interval'] ?? '' )==='10m' )>Cada 10 minutos</option>
          <option value="15m" @selected(($prefill['interval'] ?? '15m' )==='15m' )>Cada 15 minutos</option>
          <option value="30m" @selected(($prefill['interval'] ?? '' )==='30m' )>Cada 30 minutos</option>
          <option value="1h" @selected(($prefill['interval'] ?? '' )==='1h' )>Cada hora</option>
          <option value="1d" @selected(($prefill['interval'] ?? '' )==='1d' )>Diariamente</option>
        </select>
      </div>
    </div>
    <div style="margin-top:12px;">
      <button class="btn btn-primary" type="submit">Generar visualización</button>
    </div>
  </form>
</div>
<div class="card" style="margin-top:12px;">
  <canvas id="explorerChart" height="140"></canvas>
</div>
@endsection