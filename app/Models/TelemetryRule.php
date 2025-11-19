<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetryRule extends Model
{
    protected $fillable = ['telemetry_id', 'operator', 'threshold', 'color'];

    public function telemetry(): BelongsTo
    {
        return $this->belongsTo(Telemetry::class);
    }
}