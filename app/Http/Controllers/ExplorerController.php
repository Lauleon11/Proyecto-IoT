<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Device;
use Illuminate\Http\Request;

class ExplorerController extends Controller
{
    public function index(Request $request)
    {
        $templates = Template::with('telemetries')->orderBy('name')->get();
        $devices = Device::with('template')->orderBy('id', 'desc')->get();

        // Prefills
        $prefill = [
            'template_id' => $request->get('template_id'),
            'device_ids' => $request->get('device_ids', []),
            'telemetry_ids' => $request->get('telemetry_ids', []),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'interval' => $request->get('interval', '15m'),
        ];

        return view('explorer', compact('templates', 'devices', 'prefill'));
    }
}