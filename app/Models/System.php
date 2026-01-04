<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'has_periodic_review',
    ];

    protected $casts = [
        'has_periodic_review' => 'boolean',
    ];

    public function protocolTemplates()
    {
        return $this->hasMany(ProtocolTemplate::class);
    }
}
