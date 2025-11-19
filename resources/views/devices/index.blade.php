@extends('layout')

@section('content')
<div class="topbar">
    <h2>Dispositivos</h2>
    <a class="btn btn-primary" href="{{ route('devices.create') }}">Nuevo Dispositivo</a>
</div>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Device ID</th>
                <th>Plantilla</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($devices as $d)
            <tr>
                <td>{{ $d->name ?? '—' }}</td>
                <td class="muted">{{ $d->device_id }}</td>
                <td>{{ $d->template->name }}</td>
                <td>{{ $d->device_type }}</td>
                <td>
                    @if($d->is_on)
                        <span class="status green">Encendido</span>
                    @else
                        <span class="status red">Apagado</span>
                    @endif
                </td>
                <td>
                    <form method="post" action="{{ route('devices.toggle', $d) }}" style="display:inline">
                        @csrf
                        <button class="btn" type="submit">{{ $d->is_on ? 'Apagar' : 'Encender' }}</button>
                    </form>
                    <a class="btn btn-outline" href="{{ route('devices.show', $d) }}">Ver</a>
                    <a class="btn btn-outline" href="{{ route('devices.edit', $d) }}">Editar</a>
                    <form method="post" action="{{ route('devices.destroy', $d) }}" style="display:inline" onsubmit="return confirm('¿Eliminar dispositivo?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No hay dispositivos aún</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection