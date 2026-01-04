<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolEmergencyLightingDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'emergency_lighting_device_id',
        'type',
        'location',
        'check_startup_time',
        'check_duration',
        'result',
        'notes',
    ];

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }
}
