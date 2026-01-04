<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireExtinguisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type_id',
        'custom_type',
        'location',
        'next_service_year',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'next_service_year' => 'integer',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }

    public function type()
    {
        return $this->belongsTo(FireExtinguisherType::class, 'type_id');
    }

    public function getTypeNameAttribute()
    {
        return $this->type ? $this->type->name : $this->custom_type;
    }
}
