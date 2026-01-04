<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'system_id',
        'performer_id',
        'number',
        'number_index',
        'date',
        'next_date',
        'data',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'next_date' => 'date',
        'data' => 'array',
    ];

    public function clientObject(): BelongsTo
    {
        return $this->belongsTo(ClientObject::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public function fireExtinguishers()
    {
        return $this->hasMany(ProtocolFireExtinguisher::class);
    }

    public function doors()
    {
        return $this->hasMany(ProtocolDoor::class);
    }
}
