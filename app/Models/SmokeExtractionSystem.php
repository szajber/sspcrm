<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmokeExtractionSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'central_type_id',
        'custom_central_type',
        'location',
        'detectors_count',
        'buttons_count',
        'vent_buttons_count',
        'air_inlet_count',
        'smoke_exhaust_count',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'buttons_count' => 'integer',
        'vent_buttons_count' => 'integer',
        'air_inlet_count' => 'integer',
        'smoke_exhaust_count' => 'integer',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }

    public function centralType()
    {
        return $this->belongsTo(SmokeExtractionCentralType::class, 'central_type_id');
    }

    // Pomocniczy akcesor do pobierania nazwy typu centrali (ze słownika lub custom)
    public function getCentralTypeNameAttribute()
    {
        return $this->centralType ? $this->centralType->name : $this->custom_central_type;
    }
}
