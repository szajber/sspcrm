<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyLightingDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type',
        'location',
        'sort_order',
    ];

    public function clientObject(): BelongsTo
    {
        return $this->belongsTo(ClientObject::class);
    }
}
