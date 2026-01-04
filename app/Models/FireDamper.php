<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireDamper extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type_id',
        'custom_type',
        'location',
        'manufacturer',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }

    public function type()
    {
        return $this->belongsTo(FireDamperType::class, 'type_id');
    }

    // Pomocniczy akcesor do pobierania nazwy typu (ze słownika lub custom)
    public function getTypeNameAttribute()
    {
        return $this->type ? $this->type->name : $this->custom_type;
    }
}
