<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['name', 'phone', 'vehicle_id', 'subject', 'message', 'status'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
