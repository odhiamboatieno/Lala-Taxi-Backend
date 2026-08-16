<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteDriver extends Model
{
    protected $fillable = [
        'rider_id',
        'driver_id',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
