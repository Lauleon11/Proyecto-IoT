@extends('layout')

@section('content')
<div class="topbar">
    <h2>Nuevo Dispositivo</h2>
    <a class="btn" href="{{ route('devices.index') }}">Volver</a>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="card">
  <form method="post" action="{{ route('devices.store') }}">
    @csrf
    <div class="grid grid-2">
      <div>
        <label class="label">Nombre (opcional)</label>
        <input class="input" name="name" />
      </div>
      <div>
        <label class="label">Plantilla</label>
        <select class="input" name="template_id" required>
          <option value="">Selecciona una plantilla…</option>
          @foreach($templates as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="grid grid-2" style="margin-top:8px;">
      <div>
        <label class="label">Tipo de dispositivo</label>
        <select class="input" name="device_type" required>
          <option value="real">real</option>
          <option value="digital_twin">digital twin</option>
          <option value="python">python</option>
        </select>
      </div>
    </div>
    <div style="margin-top:12px;">
      <button class="btn btn-primary" type="submit">Crear Dispositivo</button>
    </div>
  </form>
</div>
@endsection