@extends('layout')

@section('content')
<div class="topbar">
    <h2>Editar Plantilla</h2>
    <a class="btn" href="{{ route('templates.show', $template) }}">Volver</a>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="card">
  <form id="template-form" method="post" action="{{ route('templates.update', $template) }}">
    @csrf
    @method('PUT')
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre de la plantilla</label>
        <input class="input" name="name" value="{{ old('name', $template->name) }}" required />
      </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
      <h3>Telemetrías</h3>
      <button type="button" class="btn" onclick="addTelemetry()">+ Telemetría</button>
    </div>
    <div id="telemetries">
      @foreach($template->telemetries as $t)
      <div class="card telemetry-block" style="margin-bottom:12px;" data-tidx="{{ $loop->index }}">
        <div class="grid grid-3">
          <input type="hidden" name="telemetries[{{ $loop->index }}][id]" value="{{ $t->id }}" />
          <div>
            <label class="label">Nombre</label>
            <input class="input" name="telemetries[{{ $loop->index }}][name]" value="{{ $t->name }}" required />
          </div>
          <div>
            <label class="label">Tipo de dato</label>
            <select class="input" name="telemetries[{{ $loop->index }}][data_type]" onchange="onTypeChange(this)">
              <option value="number" {{ $t->data_type==='number' ? 'selected' : '' }}>number</option>
              <option value="double" {{ $t->data_type==='double' ? 'selected' : '' }}>double</option>
              <option value="boolean" {{ $t->data_type==='boolean' ? 'selected' : '' }}>boolean</option>
            </select>
          </div>
          <div>
            <label class="label">Unidad</label>
            <input class="input" name="telemetries[{{ $loop->index }}][unit]" value="{{ $t->unit }}" />
          </div>
        </div>
        <div class="grid grid-3 range-block" style="margin-top:8px;">
          <div>
            <label class="label">Mínimo</label>
            <input type="number" step="any" class="input" name="telemetries[{{ $loop->index }}][min]" value="{{ $t->min }}" />
          </div>
          <div>
            <label class="label">Máximo</label>
            <input type="number" step="any" class="input" name="telemetries[{{ $loop->index }}][max]" value="{{ $t->max }}" />
          </div>
          <div class="decimals-block">
            <label class="label">Decimales (solo double)</label>
            <input type="number" min="0" max="10" class="input" name="telemetries[{{ $loop->index }}][decimals]" value="{{ $t->decimals }}" />
          </div>
        </div>
        <div class="grid grid-2 bool-block" style="margin-top:8px;">
          <div>
            <label class="label">Etiqueta Falso</label>
            <input class="input" name="telemetries[{{ $loop->index }}][false_label]" value="{{ $t->false_label }}" />
          </div>
          <div>
            <label class="label">Etiqueta Verdadero</label>
            <input class="input" name="telemetries[{{ $loop->index }}][true_label]" value="{{ $t->true_label }}" />
          </div>
        </div>
        <div style="margin-top:8px;">
          <label class="label">Descripción</label>
          <textarea class="input" name="telemetries[{{ $loop->index }}][description]">{{ $t->description }}</textarea>
        </div>
        <div class="grid grid-2" style="margin-top:6px;">
          <label>
            <input type="checkbox" name="telemetries[{{ $loop->index }}][show_last_value]" value="1" {{ $t->show_last_value ? 'checked' : '' }}> Mostrar último valor
          </label>
          <label>
            <input type="checkbox" name="telemetries[{{ $loop->index }}][show_line_chart]" value="1" {{ $t->show_line_chart ? 'checked' : '' }}> Mostrar gráfico de líneas
          </label>
        </div>
        <div style="margin-top:8px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <strong>Reglas</strong>
            <button type="button" class="btn" onclick="addRule(this.closest('.telemetry-block'))">+ Regla</button>
          </div>
          <div class="rules-container">
            @foreach($t->rules as $r)
            <div class="grid grid-3 rule-item" data-ridx="{{ $loop->index }}" style="margin-top:6px;">
              <input type="hidden" name="telemetries[{{ $loop->parent->index }}][rules][{{ $loop->index }}][id]" value="{{ $r->id }}">
              <div>
                <label class="label">Operador</label>
                <select class="input" name="telemetries[{{ $loop->parent->index }}][rules][{{ $loop->index }}][operator]">
                  <option value=">" {{ $r->operator==='>' ? 'selected' : '' }}>Mayor (>)</option>
                  <option value=">=" {{ $r->operator==='>=' ? 'selected' : '' }}>Mayor o igual (>=)</option>
                  <option value="<" {{ $r->operator==='<' ? 'selected' : '' }}>Menor (<)</option>
                  <option value="<=" {{ $r->operator==='<' ? 'selected' : '' }}>Menor o igual (<=)</option>
                  <option value="=" {{ $r->operator==='=' ? 'selected' : '' }}>Igual (=)</option>
                </select>
              </div>
              <div>
                <label class="label">Valor</label>
                <input type="number" step="any" class="input" name="telemetries[{{ $loop->parent->index }}][rules][{{ $loop->index }}][threshold]" value="{{ $r->threshold }}" required />
              </div>
              <div>
                <label class="label">Color</label>
                <select class="input" name="telemetries[{{ $loop->parent->index }}][rules][{{ $loop->index }}][color]">
                  <option value="green" {{ $r->color==='green' ? 'selected' : '' }}>Verde</option>
                  <option value="yellow" {{ $r->color==='yellow' ? 'selected' : '' }}>Amarillo</option>
                  <option value="red" {{ $r->color==='red' ? 'selected' : '' }}>Rojo</option>
                </select>
              </div>
              <div style="grid-column:1/-1; display:flex; gap:8px; align-items:center;">
                <label><input type="checkbox" class="and-toggle"> Agregar AND</label>
                <select class="input op2" style="display:none;">
                  <option value=">">Mayor (>)</option>
                  <option value=">=">Mayor o igual (>=)</option>
                  <option value="<">Menor (<)</option>
                  <option value="<=">Menor o igual (<=)</option>
                  <option value="=">Igual (=)</option>
                </select>
                <input type="number" step="any" class="input th2" style="display:none;" placeholder="Valor AND" />
                <div style="margin-left:auto;">
                  <button type="button" class="btn btn-outline" onclick="removeRule(this)">Quitar regla</button>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        <div style="margin-top:8px; display:flex; justify-content:flex-end;">
          <button type="button" class="btn btn-outline" onclick="removeTelemetry(this)">Quitar telemetría</button>
        </div>
      </div>
      @endforeach
    </div>

    <div style="margin-top:12px; display:flex; gap:8px;">
      <button class="btn btn-primary" type="submit">Guardar Cambios</button>
      <button class="btn btn-outline" type="button" onclick="document.getElementById('delete-template-form').submit()">Eliminar</button>
    </div>
  </form>
  <form id="delete-template-form" method="post" action="{{ route('templates.destroy', $template) }}" onsubmit="return confirm('¿Eliminar esta plantilla?');" style="display:none">
    @csrf
    @method('DELETE')
  </form>
</div>

<script>
function onTypeChange(select) {
  const block = select.closest('.telemetry-block');
  const type = select.value;
  const range = block.querySelector('.range-block');
  const decimals = block.querySelector('.decimals-block');
  const bool = block.querySelector('.bool-block');
  if (type === 'boolean') {
    range.style.display = 'none';
    decimals.style.display = 'none';
    bool.style.display = 'grid';
  } else {
    range.style.display = 'grid';
    decimals.style.display = (type === 'double') ? 'block' : 'none';
    bool.style.display = 'none';
  }
}

function addTelemetry() {
  const container = document.getElementById('telemetries');
  const idx = container.children.length;
  const html = `
  <div class="card telemetry-block" style="margin-bottom:12px;" data-tidx="${idx}">
    <div class="grid grid-3">
      <div>
        <label class="label">Nombre</label>
        <input class="input" name="telemetries[${idx}][name]" required />
      </div>
      <div>
        <label class="label">Tipo de dato</label>
        <select class="input" name="telemetries[${idx}][data_type]" onchange="onTypeChange(this)">
          <option value="number">number</option>
          <option value="double">double</option>
          <option value="boolean">boolean</option>
        </select>
      </div>
      <div>
        <label class="label">Unidad</label>
        <input class="input" name="telemetries[${idx}][unit]" />
      </div>
    </div>
    <div class="grid grid-3 range-block" style="margin-top:8px;">
      <div>
        <label class="label">Mínimo</label>
        <input type="number" step="any" class="input" name="telemetries[${idx}][min]" />
      </div>
      <div>
        <label class="label">Máximo</label>
        <input type="number" step="any" class="input" name="telemetries[${idx}][max]" />
      </div>
      <div class="decimals-block">
        <label class="label">Decimales (solo double)</label>
        <input type="number" min="0" max="10" class="input" name="telemetries[${idx}][decimals]" />
      </div>
    </div>
    <div class="grid grid-2 bool-block" style="margin-top:8px; display:none;">
      <div>
        <label class="label">Etiqueta Falso</label>
        <input class="input" name="telemetries[${idx}][false_label]" />
      </div>
      <div>
        <label class="label">Etiqueta Verdadero</label>
        <input class="input" name="telemetries[${idx}][true_label]" />
      </div>
    </div>
    <div style="margin-top:8px;">
      <label class="label">Descripción</label>
      <textarea class="input" name="telemetries[${idx}][description]"></textarea>
    </div>
    <div class="grid grid-2" style="margin-top:6px;">
      <label>
        <input type="checkbox" name="telemetries[${idx}][show_last_value]" value="1" checked> Mostrar último valor
      </label>
      <label>
        <input type="checkbox" name="telemetries[${idx}][show_line_chart]" value="1"> Mostrar gráfico de líneas
      </label>
    </div>
    <div style="margin-top:8px;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong>Reglas</strong>
        <button type="button" class="btn" onclick="addRule(this.closest('.telemetry-block'))">+ Regla</button>
      </div>
      <div class="rules-container"></div>
    </div>
    <div style="margin-top:8px; display:flex; justify-content:flex-end;">
      <button type="button" class="btn btn-outline" onclick="removeTelemetry(this)">Quitar telemetría</button>
    </div>
  </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

function addRule(block) {
  const rules = block.querySelector('.rules-container');
  const ridx = rules.children.length;
  const tidx = Array.from(document.getElementById('telemetries').children).indexOf(block);
  const html = `
  <div class="grid grid-3 rule-item" data-ridx="${ridx}" style="margin-top:6px;">
    <div>
      <label class="label">Operador</label>
      <select class="input" name="telemetries[${tidx}][rules][${ridx}][operator]">
        <option value=">">Mayor (>)</option>
        <option value=">=">Mayor o igual (>=)</option>
        <option value="<">Menor (<)</option>
        <option value="<=">Menor o igual (<=)</option>
        <option value="=">Igual (=)</option>
      </select>
    </div>
    <div>
      <label class="label">Valor</label>
      <input type="number" step="any" class="input" name="telemetries[${tidx}][rules][${ridx}][threshold]" required />
    </div>
    <div>
      <label class="label">Color</label>
      <select class="input" name="telemetries[${tidx}][rules][${ridx}][color]">
        <option value="green">Verde</option>
        <option value="yellow">Amarillo</option>
        <option value="red">Rojo</option>
      </select>
    </div>
    <div style="grid-column:1/-1; display:flex; gap:8px; align-items:center;">
      <label><input type="checkbox" class="and-toggle"> Agregar AND</label>
      <select class="input op2" style="display:none;">
        <option value=">">Mayor (>)</option>
        <option value=">=">Mayor o igual (>=)</option>
        <option value="<">Menor (<)</option>
        <option value="<=">Menor o igual (<=)</option>
        <option value="=">Igual (=)</option>
      </select>
      <input type="number" step="any" class="input th2" style="display:none;" placeholder="Valor AND" />
      <div style="margin-left:auto;">
        <button type="button" class="btn btn-outline" onclick="removeRule(this)">Quitar regla</button>
      </div>
    </div>
  </div>`;
  rules.insertAdjacentHTML('beforeend', html);
  wireAndToggles(block);
}


function wireAndToggles(root) {
  (root.querySelectorAll ? root : document).querySelectorAll('.rule-item').forEach(item => {
    const toggle = item.querySelector('.and-toggle');
    if (toggle && !toggle.__wired) {
      toggle.__wired = true;
      toggle.addEventListener('change', () => {
        const show = toggle.checked;
        const op2 = item.querySelector('.op2');
        const th2 = item.querySelector('.th2');
        if (op2) op2.style.display = show ? '' : 'none';
        if (th2) th2.style.display = show ? '' : 'none';
      });
    }
  });
}

function expandAndRules() {
  document.querySelectorAll('.telemetry-block').forEach(block => {
    const rules = block.querySelector('.rules-container');
    if (!rules) return;
    Array.from(rules.children).forEach(item => {
      const toggle = item.querySelector('.and-toggle');
      if (toggle && toggle.checked) {
        const op2 = item.querySelector('.op2');
        const th2 = item.querySelector('.th2');
        const colorSel = item.querySelector('select[name*="[color]"]');
        const op2v = op2 ? op2.value : '';
        const th2v = th2 ? parseFloat(th2.value) : NaN;
        const color = colorSel ? colorSel.value : 'yellow';
        if (!op2v || !isFinite(th2v)) return;
        const tidx = Array.from(document.getElementById('telemetries').children).indexOf(block);
        const ridx = rules.children.length;
        const html = `
  <div class="grid grid-3 rule-item" data-ridx="${ridx}" style="margin-top:6px; display:none;">
    <div>
      <label class="label">Operador</label>
      <select class="input" name="telemetries[${tidx}][rules][${ridx}][operator]">
        <option value="${op2v}" selected>${op2v}</option>
      </select>
    </div>
    <div>
      <label class="label">Valor</label>
      <input type="number" step="any" class="input" name="telemetries[${tidx}][rules][${ridx}][threshold]" value="${th2v}" required />
    </div>
    <div>
      <label class="label">Color</label>
      <select class="input" name="telemetries[${tidx}][rules][${ridx}][color]">
        <option value="${color}" selected>${color === 'green' ? 'Verde' : color === 'yellow' ? 'Amarillo' : 'Rojo'}</option>
      </select>
    </div>
  </div>`;
        rules.insertAdjacentHTML('beforeend', html);
      }
    });
  });
}

function removeTelemetry(btn) {
  const block = btn.closest('.telemetry-block');
  block.remove();
}

function removeRule(btn) {
  const item = btn.closest('.rule-item');
  item.remove();
}

function normalizeIndexes() {
  const container = document.getElementById('telemetries');
  Array.from(container.children).forEach((block, tIdx) => {
    block.setAttribute('data-tidx', tIdx);
    // Telemetry field names
    const map = {
      'input[name$="[id]"]': `telemetries[${tIdx}][id]`,
      'input[name$="[name]"]': `telemetries[${tIdx}][name]`,
      'select[name$="[data_type]"]': `telemetries[${tIdx}][data_type]`,
      'input[name$="[unit]"]': `telemetries[${tIdx}][unit]`,
      'input[name$="[min]"]': `telemetries[${tIdx}][min]`,
      'input[name$="[max]"]': `telemetries[${tIdx}][max]`,
      'input[name$="[decimals]"]': `telemetries[${tIdx}][decimals]`,
      'input[name$="[false_label]"]': `telemetries[${tIdx}][false_label]`,
      'input[name$="[true_label]"]': `telemetries[${tIdx}][true_label]`,
      'textarea[name$="[description]"]': `telemetries[${tIdx}][description]`,
      'input[name$="[show_last_value]"]': `telemetries[${tIdx}][show_last_value]`,
      'input[name$="[show_line_chart]"]': `telemetries[${tIdx}][show_line_chart]`,
    };
    Object.entries(map).forEach(([sel, name]) => {
      const el = block.querySelector(sel);
      if (el) el.setAttribute('name', name);
    });

    // Rules reindex
    const rules = block.querySelector('.rules-container');
    if (rules) {
      Array.from(rules.children).forEach((item, rIdx) => {
        item.setAttribute('data-ridx', rIdx);
        const rmap = {
          'input[name*="[rules]"][name$="[id]"]': `telemetries[${tIdx}][rules][${rIdx}][id]`,
          'select[name*="[rules]"][name$="[operator]"]': `telemetries[${tIdx}][rules][${rIdx}][operator]`,
          'input[name*="[rules]"][name$="[threshold]"]': `telemetries[${tIdx}][rules][${rIdx}][threshold]`,
          'select[name*="[rules]"][name$="[color]"]': `telemetries[${tIdx}][rules][${rIdx}][color]`,
        };
        Object.entries(rmap).forEach(([sel, name]) => {
          const el = item.querySelector(sel);
          if (el) el.setAttribute('name', name);
        });
      });
    }
  });
}

window.addEventListener('DOMContentLoaded', () => {
  // Initialize type-dependent visibility
  document.querySelectorAll('select[name*="[data_type]"]').forEach(onTypeChange);
  wireAndToggles(document);
  const form = document.getElementById('template-form');
  if (form) {
    form.addEventListener('submit', (e) => {
      expandAndRules();
      normalizeIndexes();
    });
  }
});
</script>
@endsection