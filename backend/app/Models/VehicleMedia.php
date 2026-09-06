<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMedia extends Model
{
    protected $table = 'vehicle_media';

    protected $fillable = ['vehicle_id', 'file_path', 'is_cover', 'sort_order'];

    protected $casts = ['is_cover' => 'boolean'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
