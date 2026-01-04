<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolVentilationDistributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'ventilation_distributor_id',
        'name',
        'location',
        'check_visual_status',
        'check_visual_notes',
        'check_cables_status',
        'check_cables_notes',
        'check_devices_status',
        'check_devices_notes',
        'check_internal_cables_status',
        'check_internal_cables_notes',
        'check_main_switch_status',
        'check_main_switch_notes',
        'check_documentation_status',
        'check_documentation_notes',
        'check_manual_controls_status',
        'check_manual_controls_notes',
        'check_optical_status',
        'check_optical_notes',
        'check_input_signals_status',
        'check_input_signals_notes',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function ventilationDistributor()
    {
        return $this->belongsTo(VentilationDistributor::class);
    }
}
