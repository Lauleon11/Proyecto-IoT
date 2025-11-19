<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DeviceController;

Route::get('/', fn() => view('home'))->name('home');

// Plantillas
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
Route::get('/templates/{template}/edit', [TemplateController::class, 'edit'])->name('templates.edit');
Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

// Dispositivos
Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle'])->name('devices.toggle');
Route::get('/devices/{device}/edit', [DeviceController::class, 'edit'])->name('devices.edit');
Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

// Explorador y Paneles
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\PanelController;
Route::get('/explorer', [ExplorerController::class, 'index'])->name('explorer');

// Paneles
Route::get('/dashboards', [PanelController::class, 'index'])->name('dashboards.index');
Route::get('/dashboards/create', [PanelController::class, 'create'])->name('dashboards.create');
Route::post('/dashboards', [PanelController::class, 'store'])->name('dashboards.store');
Route::get('/dashboards/{panel}', [PanelController::class, 'show'])->name('dashboards.show');
Route::get('/dashboards/{panel}/edit', [PanelController::class, 'edit'])->name('dashboards.edit');
Route::put('/dashboards/{panel}', [PanelController::class, 'update'])->name('dashboards.update');
Route::get('/dashboards/{panel}/data', [PanelController::class, 'data'])->name('dashboards.data');
