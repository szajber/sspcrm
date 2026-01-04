<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolFireGateDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'fire_gate_device_id',
        'type',
        'system_number',
        'location',
        'gate_type',
        'fire_resistance_class',
        'manufacturer',
        'model',
        'check_detectors',
        'check_buttons',
        'check_test_button',
        'check_signalers',
        'check_holding_mechanism',
        'check_drive',
        'check_counterweight',
        'check_magnetic_clutch',
        'battery_date',
        'result',
        'notes',
    ];

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }
}
