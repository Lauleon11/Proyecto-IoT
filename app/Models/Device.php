<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $fillable = ['name', 'template_id', 'device_type', 'is_on'];

    protected static function booted(): void
    {
        static::creating(function (Device $device) {
            if (empty($device->device_id)) {
                $device->device_id = (string) Str::uuid();
            }
            if (empty($device->scope_id)) {
                $device->scope_id = 'scope-' . Str::random(8);
            }
            if (empty($device->key)) {
                $device->key = Str::random(32);
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}