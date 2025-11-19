<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelItem extends Model
{
    use HasFactory;

    protected $fillable = ['panel_id', 'device_id', 'telemetry_id', 'viz_type', 'position'];

    public function panel()
    {
        return $this->belongsTo(Panel::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function telemetry()
    {
        return $this->belongsTo(Telemetry::class);
    }
}