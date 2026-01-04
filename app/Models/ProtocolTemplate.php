<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_id',
        'name',
        'title',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
