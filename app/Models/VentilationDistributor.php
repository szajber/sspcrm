<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentilationDistributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'name',
        'location',
        'sort_order',
        'is_active',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }
}
