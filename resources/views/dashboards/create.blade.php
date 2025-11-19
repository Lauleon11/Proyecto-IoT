@extends('layout')

@section('head')
<script>
function updateTelemetryOptions(selectEl) {
  const block = selectEl.closest('.item-block');
  const deviceId = selectEl.value;
  const tmplId = window.devicesByTemplateId[deviceId];
  const telSelect = block.querySelector('select[name$="[telemetry_id]"]');
  telSelect.innerHTML = '';
  const telemetries = (window.telemetriesByTemplate[tmplId] || []);
  telemetries.forEach(t => {
    const opt = document.createElement('option');
    opt.value = t.id;
    opt.textContent = t.name;
    telSelect.appendChild(opt);
  });
}
function addItem() {
  const container = document.getElementById('items');
  const idx = container.children.length;
  const div = document.createElement('div');
  div.className = 'card item-block';
  div.style.marginBottom = '12px';
  div.innerHTML = `
    <div class="grid grid-3">
      <div>
        <label class="label">Dispositivo</label>
        <select class="input" name="items[${idx}][device_id]" onchange="updateTelemetryOptions(this)" required>
          @foreach($devices as $d)
            <option value="{{ $d->id }}">{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Telemetría</label>
        <select class="input" name="items[${idx}][telemetry_id]" required></select>
      </div>
      <div>
        <label class="label">Visualización</label>
        <select class="input" name="items[${idx}][viz_type]" required>
          <option value="last">Último valor</option>
          <option value="line">Gráfico de líneas</option>
        </select>
      </div>
    </div>
  `;
  container.appendChild(div);
  const deviceSelect = div.querySelector('select[name$="[device_id]"]');
  updateTelemetryOptions(deviceSelect);
}
window.addEventListener('DOMContentLoaded', () => {
  // Build lookup maps
  window.telemetriesByTemplate = {};
  @foreach($templates as $t)
    window.telemetriesByTemplate[{{ $t->id }}] = @json($t->telemetries->map(fn($m)=>['id'=>$m->id,'name'=>$m->name])->values());
  @endforeach
  window.devicesByTemplateId = {};
  @foreach($devices as $d)
    window.devicesByTemplateId[{{ $d->id }}] = {{ $d->template->id }};
  @endforeach
  addItem();
});
</script>
@endsection

@section('content')
<div class="topbar">
  <h2>Crear panel</h2>
  <a class="btn" href="{{ route('dashboards.index') }}">Volver</a>
</div>
<form method="post" action="{{ route('dashboards.store') }}">
  @csrf
  <div class="card">
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre del panel</label>
        <input class="input" name="name" placeholder="Ej. General Colegio" required />
      </div>
      <div style="display:flex; align-items:flex-end;">
        <button class="btn" type="button" onclick="addItem()">+ Añadir elemento</button>
      </div>
    </div>
  </div>
  <div id="items"></div>
  <button class="btn btn-primary" type="submit">Crear panel</button>
</form>
@endsection