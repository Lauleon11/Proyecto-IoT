@extends('layout')

@section('head')
<script>
function addTelemetry() {
  const container = document.getElementById('telemetries');
  const idx = container.children.length;
  const html = `
  <div class="card" style="margin-bottom:12px;">
    <div class="grid grid-3">
      <div>
        <label class="label">Nombre</label>
        <input class="input" name="telemetries[${idx}][name]" required />
      </div>
      <div>
        <label class="label">Tipo de dato</label>
        <select class="input" name="telemetries[${idx}][data_type]" onchange="onTypeChange(this, ${idx})">
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
    <div class="grid grid-3" id="range-${idx}">
      <div>
        <label class="label">Mínimo</label>
        <input type="number" step="any" class="input" name="telemetries[${idx}][min]" />
      </div>
      <div>
        <label class="label">Máximo</label>
        <input type="number" step="any" class="input" name="telemetries[${idx}][max]" />
      </div>
      <div id="decimals-${idx}">
        <label class="label">Decimales (solo double)</label>
        <input type="number" min="0" max="10" class="input" name="telemetries[${idx}][decimals]" />
      </div>
    </div>
    <div class="grid grid-2" id="bool-${idx}" style="display:none;">
      <div>
        <label class="label">Etiqueta Falso</label>
        <input class="input" name="telemetries[${idx}][false_label]" />
      </div>
      <div>
        <label class="label">Etiqueta Verdadero</label>
        <input class="input" name="telemetries[${idx}][true_label]" />
      </div>
    </div>
    <div>
      <label class="label">Descripción</label>
      <textarea class="input" name="telemetries[${idx}][description]"></textarea>
    </div>
    <div class="grid grid-2">
      <label>
        <input type="checkbox" name="telemetries[${idx}][show_last_value]" checked> Mostrar último valor
      </label>
      <label>
        <input type="checkbox" name="telemetries[${idx}][show_line_chart]"> Mostrar gráfico de líneas
      </label>
    </div>
    <div>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong>Reglas</strong>
        <button type="button" class="btn" onclick="addRule(${idx})">+ Regla</button>
      </div>
      <div id="rules-${idx}"></div>
    </div>
  </div>`;
  container.insertAdjacentHTML('beforeend', html);
}
function onTypeChange(select, idx) {
  const type = select.value;
  document.getElementById(`bool-${idx}`).style.display = (type === 'boolean') ? 'grid' : 'none';
  document.getElementById(`range-${idx}`).style.display = (type === 'boolean') ? 'none' : 'grid';
  document.getElementById(`decimals-${idx}`).style.display = (type === 'double') ? 'block' : 'none';
}
function addRule(tIdx) {
  const container = document.getElementById(`rules-${tIdx}`);
  const rIdx = container.children.length;
  const html = `
  <div class="grid grid-3 rule-block" data-tidx="${tIdx}" data-ridx="${rIdx}" style="margin-top:8px;">
    <div>
      <label class="label">Operador</label>
      <select class="input op1" name="telemetries[${tIdx}][rules][${rIdx}][operator]">
        <option value=">">Mayor (>)</option>
        <option value=">=">Mayor o igual (>=)</option>
        <option value="<">Menor (<)</option>
        <option value="<=">Menor o igual (<=)</option>
        <option value="=">Igual (=)</option>
      </select>
    </div>
    <div>
      <label class="label">Valor</label>
      <input type="number" step="any" class="input th1" name="telemetries[${tIdx}][rules][${rIdx}][threshold]" required />
    </div>
    <div>
      <label class="label">Color</label>
      <select class="input color" name="telemetries[${tIdx}][rules][${rIdx}][color]">
        <option value="green">Verde</option>
        <option value="yellow">Amarillo</option>
        <option value="red">Rojo</option>
      </select>
    </div>
    <div class="grid grid-3" style="margin-top:6px;">
      <div>
        <label class="label"><input type="checkbox" class="use-and"> Agregar Y</label>
      </div>
      <div class="and-fields" style="display:none; grid-template-columns: 1fr 1fr; gap:8px;">
        <div>
          <label class="label">Operador (Y)</label>
          <select class="input op2">
            <option value=">">Mayor (>)</option>
            <option value=">=">Mayor o igual (>=)</option>
            <option value="<">Menor (<)</option>
            <option value="<=">Menor o igual (<=)</option>
            <option value="=">Igual (=)</option>
          </select>
        </div>
        <div>
          <label class="label">Valor (Y)</label>
          <input type="number" step="any" class="input th2" />
        </div>
      </div>
    </div>
  </div>`;
  container.insertAdjacentHTML('beforeend', html);
  wireRuleBlock(tIdx, rIdx);
}

function wireRuleBlock(tIdx, rIdx) {
  const block = document.querySelector(`.rule-block[data-tidx="${tIdx}"][data-ridx="${rIdx}"]`);
  if (!block) return;
  const useAnd = block.querySelector('.use-and');
  const andFields = block.querySelector('.and-fields');
  useAnd.addEventListener('change', () => {
    andFields.style.display = useAnd.checked ? 'grid' : 'none';
  });
}
window.addEventListener('DOMContentLoaded', () => {
  addTelemetry();
  const form = document.querySelector('form'); // corregido: selecciona el único formulario
  if (!form) return;
  form.addEventListener('submit', (e) => {
    let hasError = false;
    const ruleBlocks = document.querySelectorAll('.rule-block');
    ruleBlocks.forEach(block => {
      const useAnd = block.querySelector('.use-and');
      const andFields = block.querySelector('.and-fields');
      const errorEl = block.querySelector('.and-error') || (() => {
        const span = document.createElement('div');
        span.className = 'and-error';
        span.style.color = 'red';
        span.style.marginTop = '4px';
        andFields.parentElement.appendChild(span);
        return span;
      })();
      errorEl.textContent = '';
      if (useAnd && useAnd.checked) {
        const op2 = block.querySelector('.op2').value;
        const th2 = block.querySelector('.th2').value;
        const color = block.querySelector('.color').value;
        if (th2 === '' || th2 === null) {
          hasError = true;
          errorEl.textContent = 'Debes ingresar el valor para la condición Y.';
          return; // no expandir la segunda regla si falta
        }
        const tIdx = block.getAttribute('data-tidx');
        const rulesContainer = document.getElementById(`rules-${tIdx}`);
        const newIdx = rulesContainer.children.length; // siguiente índice
        const hidden = document.createElement('div');
        hidden.innerHTML = `
          <input type="hidden" name="telemetries[${tIdx}][rules][${newIdx}][operator]" value="${op2}">
          <input type="hidden" name="telemetries[${tIdx}][rules][${newIdx}][threshold]" value="${th2}">
          <input type="hidden" name="telemetries[${tIdx}][rules][${newIdx}][color]" value="${color}">
        `;
        form.appendChild(hidden);
      }
    });
    if (hasError) {
      e.preventDefault();
    }
  });
});
</script>
@endsection

@section('content')
<div class="topbar">
  <h2>Nueva Plantilla</h2>
  <a class="btn" href="{{ route('templates.index') }}">Volver</a>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="card">
  <form method="post" action="{{ route('templates.store') }}">
    @csrf
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre de la plantilla</label>
        <input class="input" name="name" required />
      </div>
    </div>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
      <h3>Telemetrías</h3>
      <button type="button" class="btn" onclick="addTelemetry()">+ Telemetría</button>
    </div>
    <div id="telemetries"></div>
    <div style="margin-top:12px;">
      <button class="btn btn-primary" type="submit">Guardar Plantilla</button>
    </div>
  </form>
</div>
@endsection