<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolDoor extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'door_id',
        'resistance_class',
        'location',
        'status',
        'notes',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function door()
    {
        return $this->belongsTo(Door::class);
    }
}
