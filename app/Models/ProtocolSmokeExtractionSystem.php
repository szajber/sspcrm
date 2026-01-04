<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolSmokeExtractionSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'smoke_extraction_system_id',
        'central_type_name',
        'location',
        'detectors_count',
        'buttons_count',
        'vent_buttons_count',
        'air_inlet_count',
        'smoke_exhaust_count',
        'battery_date',
        'result',
        'notes',
    ];

    protected $casts = [
        'buttons_count' => 'integer',
        'vent_buttons_count' => 'integer',
        'air_inlet_count' => 'integer',
        'smoke_exhaust_count' => 'integer',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function smokeExtractionSystem()
    {
        return $this->belongsTo(SmokeExtractionSystem::class);
    }
}
