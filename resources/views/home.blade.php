@extends('layout')

@section('content')
<div class="topbar">
    <h2>Inicio</h2>
</div>
<div class="grid grid-3">
    <div class="card">
        <h3>Plantillas</h3>
        <p class="muted">Crea y configura telemetrías, reglas y visualizaciones.</p>
        <a class="btn btn-primary" href="{{ route('templates.index') }}">Ir a Plantillas</a>
    </div>
    <div class="card">
        <h3>Dispositivos</h3>
        <p class="muted">Crea dispositivos y simula datos en tiempo real.</p>
        <a class="btn btn-primary" href="{{ route('devices.index') }}">Ir a Dispositivos</a>
    </div>
    <div class="card">
        <h3>Explorador de Datos</h3>
        <p class="muted">Análisis y consultas de datos.</p>
        <a class="btn btn-primary" href="{{ route('explorer') }}">Ir a Explorador de Datos</a>
    </div>
</div>
@endsection