<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwpDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type', // switch, trigger
        'location',
        'system_number',
        'sort_order',
    ];

    public function clientObject(): BelongsTo
    {
        return $this->belongsTo(ClientObject::class);
    }
}
