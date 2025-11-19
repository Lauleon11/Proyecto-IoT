@extends('layout')

@section('content')
<div class="topbar">
    <h2>Plantillas</h2>
    <a class="btn btn-primary" href="{{ route('templates.create') }}">Nueva Plantilla</a>
</div>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Telemetrías</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($templates as $template)
            <tr>
                <td>{{ $template->name }}</td>
                <td><span class="tag">{{ $template->telemetries_count }}</span></td>
                <td>
                    <a class="btn btn-outline" href="{{ route('templates.show', $template) }}">Ver</a>
                    <a class="btn btn-outline" href="{{ route('templates.edit', $template) }}">Editar</a>
                    <form method="post" action="{{ route('templates.destroy', $template) }}" style="display:inline" onsubmit="return confirm('¿Eliminar plantilla?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No hay plantillas aún</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection