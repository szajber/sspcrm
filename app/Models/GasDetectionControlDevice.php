<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasDetectionControlDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_object_id',
        'type',
        'location',
        'sort_order',
        'is_active',
    ];

    public function clientObject()
    {
        return $this->belongsTo(ClientObject::class);
    }
}
