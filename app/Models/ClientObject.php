<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientObject extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'address',
        'postal_code',
        'city',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'client_object_system');
    }
}
