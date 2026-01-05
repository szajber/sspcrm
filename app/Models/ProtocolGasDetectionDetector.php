<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolGasDetectionDetector extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'gas_detection_detector_id',
        'name',
        'location',
        'sort_order',
        'result',
        'next_calibration_date',
        'notes',
    ];

    protected $casts = [
        'next_calibration_date' => 'date',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function gasDetectionDetector()
    {
        return $this->belongsTo(GasDetectionDetector::class);
    }
}
