<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolFireDamper extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'fire_damper_id',
        'type_name',
        'location',
        'manufacturer',
        'check_optical',
        'check_drive',
        'check_mechanical',
        'check_alarm',
        'result',
        'notes',
    ];

    protected $casts = [
        'check_optical' => 'boolean',
        'check_drive' => 'boolean',
        'check_mechanical' => 'boolean',
        'check_alarm' => 'boolean',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function fireDamper()
    {
        return $this->belongsTo(FireDamper::class);
    }
}
