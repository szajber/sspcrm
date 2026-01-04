<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FireGateDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type', // gate, central
        'system_number',
        'location',
        'gate_type', // gravitational, electric
        'fire_resistance_class',
        'manufacturer',
        'model',
        'sort_order',
    ];

    public function clientObject(): BelongsTo
    {
        return $this->belongsTo(ClientObject::class);
    }
}
