<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolPwpDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'pwp_device_id',
        'type',
        'location',
        'system_number',
        'check_access',
        'check_signage',
        'check_condition',
        'check_activation',
        'result',
        'notes',
    ];

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }
}
