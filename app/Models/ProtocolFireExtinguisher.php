<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolFireExtinguisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'fire_extinguisher_id',
        'type_name',
        'location',
        'status',
        'next_service_year',
        'notes',
    ];

    protected $casts = [
        'next_service_year' => 'integer',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function fireExtinguisher()
    {
        return $this->belongsTo(FireExtinguisher::class);
    }
}
