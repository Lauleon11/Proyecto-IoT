<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Template;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('template')->orderByDesc('id')->get();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        $templates = Template::orderBy('name')->get();
        return view('devices.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'template_id' => 'required|exists:templates,id',
            'device_type' => 'required|string|in:real,digital_twin,python',
        ]);

        $device = Device::create($data);
        return redirect()->route('devices.show', $device)->with('status', 'Dispositivo creado');
    }

    public function show(Device $device)
    {
        $device->load(['template.telemetries.rules']);
        return view('devices.show', compact('device'));
    }

    public function toggle(Device $device)
    {
        $device->is_on = !$device->is_on;
        $device->save();
        return redirect()->route('devices.show', $device);
    }

    public function edit(Device $device)
    {
        $templates = Template::orderBy('name')->get();
        return view('devices.edit', compact('device', 'templates'));
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'template_id' => 'required|exists:templates,id',
            'device_type' => 'required|string|in:real,digital_twin,python',
            'is_on' => 'nullable|boolean',
        ]);
        $device->update([
            'name' => $data['name'] ?? null,
            'template_id' => $data['template_id'],
            'device_type' => $data['device_type'],
            'is_on' => (bool)($data['is_on'] ?? $device->is_on),
        ]);
        return redirect()->route('devices.show', $device)->with('status', 'Dispositivo actualizado');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('status', 'Dispositivo eliminado');
    }
}