<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'supports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'status',
        'image',
        'Owner', // Match database column name
        'cancelled_by',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the owner attribute (maps to Owner column).
     *
     * @return string
     */
    public function getOwnerAttribute()
    {
        return $this->attributes['Owner'];
    }

    /**
     * Set the owner attribute (maps to Owner column).
     *
     * @param string $value
     * @return void
     */
    public function setOwnerAttribute($value)
    {
        $this->attributes['Owner'] = $value;
    }

    /**
     * Get the image source URL.
     *
     * @return string
     */
    public function getImageSrcAttribute()
    {
        return url('images/support/' . $this->attributes['image']);
    }

    /**
     * Scope for active supports for riders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active')
                     ->where(function ($q) {
                         $q->where('Owner', 'Rider')->orWhere('Owner', 'All');
                     });
    }
}