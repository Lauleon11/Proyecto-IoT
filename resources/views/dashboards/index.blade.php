@extends('layout')

@section('content')
<div class="topbar">
  <h2>Paneles</h2>
  <a class="btn btn-primary" href="{{ route('dashboards.create') }}">+ Crear panel</a>
</div>
@if(session('status'))
  <div class="status green">{{ session('status') }}</div>
@endif
<div class="grid grid-3">
  @forelse($panels as $panel)
  <div class="card">
    <h3>{{ $panel->name }}</h3>
    <p class="muted">{{ $panel->items_count }} elementos</p>
    <a class="btn" href="{{ route('dashboards.show', $panel) }}">Abrir</a>
<a class="btn" href="{{ route('dashboards.edit', $panel) }}">Editar</a>
  </div>
  @empty
  <div class="card">
    <p class="muted">No hay paneles todavía.</p>
  </div>
  @endforelse
</div>
@endsection