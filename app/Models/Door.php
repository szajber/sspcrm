<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Door extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'resistance_class_id',
        'custom_resistance_class',
        'location',
        'sort_order',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }

    public function resistanceClass()
    {
        return $this->belongsTo(DoorResistanceClass::class, 'resistance_class_id');
    }

    // Pomocniczy akcesor do pobierania nazwy klasy (ze słownika lub custom)
    public function getResistanceClassNameAttribute()
    {
        return $this->resistanceClass ? $this->resistanceClass->name : $this->custom_resistance_class;
    }
}
