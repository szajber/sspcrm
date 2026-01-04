<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireDamperType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function fireDampers()
    {
        return $this->hasMany(FireDamper::class, 'type_id');
    }
}
