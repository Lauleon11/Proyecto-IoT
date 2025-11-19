<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Telemetry extends Model
{
    protected $fillable = [
        'template_id', 'name', 'data_type', 'decimals', 'min', 'max',
        'unit', 'description', 'false_label', 'true_label',
        'show_last_value', 'show_line_chart',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TelemetryRule::class);
    }
}