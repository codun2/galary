<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'make', 'model', 'year', 'mileage', 'fuel', 'transmission', 'color',
        'drivetrain', 'price', 'description', 'features', 'status', 'is_featured',
        'views_count',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function media()
    {
        return $this->hasMany(VehicleMedia::class)->orderBy('sort_order');
    }

    public function cover()
    {
        return $this->hasOne(VehicleMedia::class)->where('is_cover', true);
    }
}
