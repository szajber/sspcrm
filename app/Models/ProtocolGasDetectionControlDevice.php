<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolGasDetectionControlDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'gas_detection_control_device_id',
        'type',
        'location',
        'sort_order',
        'result',
        'notes',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function gasDetectionControlDevice()
    {
        return $this->belongsTo(GasDetectionControlDevice::class);
    }
}
