<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmokeExtractionCentralType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function smokeExtractionSystems()
    {
        return $this->hasMany(SmokeExtractionSystem::class, 'central_type_id');
    }
}
