@extends('layout')

@section('content')
<div class="topbar">
  <h2>Editar panel</h2>
  <a class="btn" href="{{ route('dashboards.show', $panel) }}">Volver</a>
</div>
@if($errors->any())
  <div class="status red">
    <ul style="margin:0; padding-left:18px;">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<form id="panelEditForm" method="post" action="{{ route('dashboards.update', $panel) }}">
  @csrf
  @method('PUT')

  <div class="card" style="margin-bottom:12px;">
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre del panel</label>
        <input class="input" type="text" name="name" value="{{ old('name', $panel->name) }}" required />
      </div>
      <div style="display:flex; align-items:flex-end; justify-content:flex-end;">
        <button id="addItemBtn" class="btn" type="button">+ Añadir elemento</button>
      </div>
    </div>
  </div>

  <div id="items" class="grid grid-2">
    @foreach($panel->items as $idx => $it)
      <div class="card item-block" data-index="{{ $idx }}" style="margin-bottom:12px;">
        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $it->id }}" />
        <div class="grid grid-3">
          <div>
            <label class="label">Dispositivo</label>
            <select class="input" name="items[{{ $idx }}][device_id]" data-device-id data-template-id="{{ $it->device->template->id }}" required>
              @foreach($devices as $d)
                <option value="{{ $d->id }}" data-template-id="{{ $d->template->id }}" @selected($d->id === $it->device_id)>{{ $d->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="label">Telemetría</label>
            <select class="input" name="items[{{ $idx }}][telemetry_id]" data-telemetry-id data-selected="{{ $it->telemetry_id }}" required>
              {{-- Opciones se rellenan por JS según el template --}}
            </select>
          </div>
          <div>
            <label class="label">Visualización</label>
            <select class="input" name="items[{{ $idx }}][viz_type]" required>
              <option value="last" @selected($it->viz_type === 'last')>Último valor</option>
              <option value="line" @selected($it->viz_type === 'line')>Gráfico de líneas</option>
            </select>
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; margin-top:8px;">
          <button class="btn" type="button" data-remove>Eliminar</button>
        </div>
      </div>
    @endforeach
  </div>

  <button class="btn btn-primary" type="submit">Guardar cambios</button>
</form>
@endsection

@section('head')
<script>
// Mapas construidos por Blade
window.telemetriesByTemplate = {};
@foreach($templates as $t)
  window.telemetriesByTemplate[{{ $t->id }}] = @json($t->telemetries->map(fn($m)=>['id'=>$m->id,'name'=>$m->name])->values());
@endforeach
window.deviceTemplateById = {};
@foreach($devices as $d)
  window.deviceTemplateById[{{ $d->id }}] = {{ $d->template->id }};
@endforeach

function fillTelemetryOptions(selectEl, templateId, selectedId) {
  const list = window.telemetriesByTemplate[templateId] || [];
  selectEl.innerHTML = '';
  const ph = document.createElement('option');
  ph.value = '';
  ph.textContent = list.length ? 'Seleccione una telemetría' : 'Sin telemetrías disponibles';
  ph.disabled = true;
  selectEl.appendChild(ph);

  let hasSelection = false;
  list.forEach(t => {
    const opt = document.createElement('option');
    opt.value = t.id; opt.textContent = t.name;
    if (selectedId && Number(selectedId) === Number(t.id)) { opt.selected = true; hasSelection = true; }
    selectEl.appendChild(opt);
  });
  if (!hasSelection && list.length > 0) { const first = selectEl.options[1]; if (first) first.selected = true; }
  selectEl.removeAttribute('disabled');
}

function wireItem(block) {
  const deviceSel = block.querySelector('[data-device-id]');
  const telemetrySel = block.querySelector('[data-telemetry-id]');
  const removeBtn = block.querySelector('[data-remove]');
  const selectedTelemetry = telemetrySel.getAttribute('data-selected');

  const currentTemplateId = window.deviceTemplateById[deviceSel.value] ?? (deviceSel.options[deviceSel.selectedIndex] && deviceSel.options[deviceSel.selectedIndex].dataset.templateId);
  if (currentTemplateId) { fillTelemetryOptions(telemetrySel, currentTemplateId, selectedTelemetry); }

  deviceSel.addEventListener('change', () => {
    const tId = window.deviceTemplateById[deviceSel.value] ?? (deviceSel.options[deviceSel.selectedIndex] && deviceSel.options[deviceSel.selectedIndex].dataset.templateId);
    fillTelemetryOptions(telemetrySel, tId, null);
  });
  removeBtn.addEventListener('click', () => block.remove());
}

function addItem() {
  const container = document.getElementById('items');
  const idx = container.querySelectorAll('.item-block').length;
  const div = document.createElement('div');
  div.className = 'card item-block';
  div.style.marginBottom = '12px';
  div.innerHTML = `
    <input type="hidden" name="items[${idx}][id]" value="" />
    <div class="grid grid-3">
      <div>
        <label class="label">Dispositivo</label>
        <select class="input" name="items[${idx}][device_id]" data-device-id required>
          @foreach($devices as $d)
            <option value="{{ $d->id }}" data-template-id="{{ $d->template->id }}">{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Telemetría</label>
        <select class="input" name="items[${idx}][telemetry_id]" data-telemetry-id required></select>
      </div>
      <div>
        <label class="label">Visualización</label>
        <select class="input" name="items[${idx}][viz_type]" required>
          <option value="last">Último valor</option>
          <option value="line">Gráfico de líneas</option>
        </select>
      </div>
    </div>
    <div style="display:flex; justify-content:flex-end; margin-top:8px;">
      <button class="btn" type="button" data-remove>Eliminar</button>
    </div>
  `;
  container.appendChild(div);
  wireItem(div);
}

function normalizeIndexes() {
  const blocks = document.querySelectorAll('.item-block');
  blocks.forEach((b, i) => {
    const idInput = b.querySelector('input[type="hidden"]');
    const dev = b.querySelector('[data-device-id]');
    const tel = b.querySelector('[data-telemetry-id]');
    const viz = b.querySelector('select[name$="[viz_type]"]');
    if (idInput) idInput.name = `items[${i}][id]`;
    if (dev) dev.name = `items[${i}][device_id]`;
    if (tel) tel.name = `items[${i}][telemetry_id]`;
    if (viz) viz.name = `items[${i}][viz_type]`;
  });
}

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.item-block').forEach(wireItem);
  const addBtn = document.getElementById('addItemBtn');
  if (addBtn) addBtn.addEventListener('click', addItem);
  const form = document.getElementById('panelEditForm');
  if (form) form.addEventListener('submit', normalizeIndexes);
});
</script>
@endsection