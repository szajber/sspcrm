<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolGasDetectionCentral extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'gas_detection_central_id',
        'name',
        'location',
        'sort_order',
        'result',
        'notes',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function gasDetectionCentral()
    {
        return $this->belongsTo(GasDetectionCentral::class);
    }
}
