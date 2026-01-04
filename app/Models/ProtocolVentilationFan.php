<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolVentilationFan extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'ventilation_fan_id',
        'name',
        'location',
        'check_alarm_level_2',
        'check_technical_condition',
        'check_cables_condition',
        'current_1',
        'current_2',
        'result',
        'notes',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function ventilationFan()
    {
        return $this->belongsTo(VentilationFan::class);
    }
}
