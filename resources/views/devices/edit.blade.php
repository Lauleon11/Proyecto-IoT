@extends('layout')

@section('content')
<div class="topbar">
    <h2>Editar Dispositivo</h2>
    <a class="btn" href="{{ route('devices.show', $device) }}">Volver</a>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="card">
  <form method="post" action="{{ route('devices.update', $device) }}">
    @csrf
    @method('PUT')
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre (opcional)</label>
        <input class="input" name="name" value="{{ old('name', $device->name) }}" />
      </div>
      <div>
        <label class="label">Plantilla</label>
        <select class="input" name="template_id" required>
          @foreach($templates as $t)
            <option value="{{ $t->id }}" @selected($t->id === $device->template_id)>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="grid grid-2" style="margin-top:8px;">
      <div>
        <label class="label">Tipo de dispositivo</label>
        <select class="input" name="device_type" required>
          <option value="real" @selected($device->device_type==='real')>real</option>
          <option value="digital_twin" @selected($device->device_type==='digital_twin')>digital twin</option>
          <option value="python" @selected($device->device_type==='python')>python</option>
        </select>
      </div>
      <div>
        <label class="label">Estado</label>
        <select class="input" name="is_on">
          <option value="0" @selected(!$device->is_on)>Apagado</option>
          <option value="1" @selected($device->is_on)>Encendido</option>
        </select>
      </div>
    </div>
    <div style="margin-top:12px; display:flex; gap:8px;">
      <button class="btn btn-primary" type="submit">Guardar Cambios</button>
    </div>
  </form>
  <form method="post" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm('¿Eliminar este dispositivo?');" style="margin-top:8px;">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline" type="submit">Eliminar</button>
  </form>
</div>
@endsection