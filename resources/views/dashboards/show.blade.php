@extends('layout')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const palette = ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'];
function formatDate(d) {
  const pad = n => n.toString().padStart(2,'0');
  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
function generateSeries(points=60) {
  const labels = [];
  const now = new Date();
  for (let i=points-1;i>=0;i--) {
    const d = new Date(now.getTime()-i*60*1000);
    labels.push(formatDate(d));
  }
  const data = labels.map((_,i)=> (Math.sin(i/5)*10) + (Math.random()*5+20));
  return { labels, data };
}
function renderLine(canvasId, label) {
  const s = generateSeries(60);
  const ctx = document.getElementById(canvasId).getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: s.labels,
      datasets: [{ label, data: s.data, borderColor: palette[Math.floor(Math.random()*palette.length)], tension: 0.3 }]
    },
    options: { responsive: true, animation: false, plugins: { legend: { position: 'bottom' } } }
  });
}
function renderLastValue(containerId, label) {
  const cont = document.getElementById(containerId);
  cont.querySelector('.label').textContent = label;
}
function formatValue(it) {
  if (it.text) return it.text;
  const unit = it.unit ? ` ${it.unit}` : '';
  const decimals = (typeof it.decimals === 'number') ? it.decimals : 2;
  return `${Number(it.value).toFixed(decimals)}${unit}`;
}
let isPolling = false;
async function pollData(panelId) {
  if (isPolling) return; // evita solapar peticiones
  isPolling = true;
  try {
    const res = await fetch(`/dashboards/${panelId}/data`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    const items = (json.items || []);

    // Actualizar tarjetas de cada item
    items.forEach(it => {
      const el = document.getElementById(`item-${it.item_id}`);
      if (!el) return;
      const valEl = el.querySelector('.value');
      valEl.textContent = formatValue(it);
      el.classList.remove('status-green','status-yellow','status-red');
      if (it.color === 'green') el.classList.add('status-green');
      else if (it.color === 'yellow') el.classList.add('status-yellow');
      else if (it.color === 'red') el.classList.add('status-red');
    });
  } catch (e) {
    // Ignora abortos o cambios de visibilidad para reducir ruido en consola
    if (e && (e.name === 'AbortError')) { /* noop */ } else {
      console.warn('poll error', e);
    }
  } finally {
    isPolling = false;
  }
}
window.addEventListener('DOMContentLoaded', () => {
  const items = window.panelItems || [];
  items.forEach((it, idx) => {
    const label = `${it.device_name} · ${it.telemetry_name}`;
    if (it.viz_type === 'line') {
      renderLine(`chart-${idx}`, label);
    } else {
      renderLastValue(`item-${it.item_id}`, label);
    }
  });
  pollData(window.panelId);
  setInterval(() => {
    if (document.visibilityState === 'visible') {
      pollData(window.panelId);
    }
  }, 5000);
});
</script>
@endsection

@section('content')
<div class="topbar">
  <h2>{{ $panel->name }}</h2>
  <a class="btn" href="{{ route('dashboards.index') }}">Volver</a>
  <a class="btn" href="{{ route('dashboards.edit', $panel) }}">Editar</a>
</div>
@php
  $itemsPayload = $panel->items->map(function($it){
    return [
      'item_id' => $it->id,
      'viz_type' => $it->viz_type,
      'device_name' => $it->device->name,
      'telemetry_name' => $it->telemetry->name,
    ];
  })->values()->toArray();
@endphp
<script>
  window.panelItems = @json($itemsPayload);
  window.panelId = {{ $panel->id }};
</script>
<style>
  .status-green { background:#e6ffec; border-color:#22c55e; }
  .status-yellow { background:#fff9db; border-color:#f59e0b; }
  .status-red { background:#ffe7e7; border-color:#ef4444; }
</style>

<div class="grid grid-3">
  @foreach($panel->items as $it)
    @if($it->viz_type === 'line')
      <div class="card">
        <h3>{{ $it->device->name }} · {{ $it->telemetry->name }}</h3>
        <canvas id="chart-{{ $loop->index }}" height="140"></canvas>
      </div>
    @else
      <div class="card" id="item-{{ $it->id }}">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <h3 class="label">{{ $it->device->name }} · {{ $it->telemetry->name }}</h3>
          <span class="value" style="font-size:24px; font-weight:bold;">--</span>
        </div>
        <p class="muted">Último valor</p>
      </div>
    @endif
  @endforeach
</div>
@endsection