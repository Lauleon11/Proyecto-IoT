<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajo IoT</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"; background:#f6f7fb; }
        .app { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:#111827; color:#fff; padding:20px; }
        .sidebar h1 { font-size:18px; margin:0 0 16px 0; }
        .nav a { display:block; color:#cbd5e1; text-decoration:none; padding:10px 12px; border-radius:8px; margin-bottom:6px; }
        .nav a.active, .nav a:hover { background:#1f2937; color:#fff; }
        .content { flex:1; padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; }
        .btn { display:inline-block; padding:8px 12px; border-radius:8px; text-decoration:none; cursor:pointer; border:1px solid #1f2937; color:#111827; background:#fff; }
        .btn-primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .btn-outline { background:#fff; color:#2563eb; border-color:#2563eb; }
        .grid { display:grid; gap:12px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .input { width:100%; padding:8px; border:1px solid #d1d5db; border-radius:8px; }
        .label { font-size:12px; color:#6b7280; margin-bottom:6px; display:block; }
        .tag { display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; }
        .status { padding:8px 12px; border-radius:8px; font-size:12px; }
        .status.red { background:#fee2e2; color:#991b1b; }
        .status.green { background:#dcfce7; color:#166534; }
        .status.yellow { background:#fef9c3; color:#854d0e; }
        /* Color aplicado a tarjetas completas por reglas */
        .card.red { background:#fee2e2; border-color:#fecaca; }
        .card.green { background:#dcfce7; border-color:#bbf7d0; }
        .card.yellow { background:#fef9c3; border-color:#fde68a; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px; border-bottom:1px solid #e5e7eb; text-align:left; }
        .muted { color:#6b7280; }
    </style>
    @yield('head')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <h1>Trabajo IoT</h1>
        <nav class="nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('templates.index') }}" class="{{ request()->is('templates*') ? 'active' : '' }}">Plantillas</a>
            <a href="{{ route('devices.index') }}" class="{{ request()->is('devices*') ? 'active' : '' }}">Dispositivos</a>
            <a href="{{ route('explorer') }}" class="{{ request()->routeIs('explorer') ? 'active' : '' }}">Explorador de Datos</a>
            <a href="{{ route('dashboards.index') }}" class="{{ request()->is('dashboards*') ? 'active' : '' }}">Paneles</a>
        </nav>
        <div class="muted" style="margin-top:18px; font-size:12px;">Sidebar</div>
    </aside>
    <main class="content">
        @yield('content')
    </main>
</div>
</body>
</html>