<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorResistanceClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function doors()
    {
        return $this->hasMany(Door::class, 'resistance_class_id');
    }
}
