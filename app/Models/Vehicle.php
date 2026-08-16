<?php

/**
 * Driver Docuemnts Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Driver Docuemnts
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle';

    public $timestamps = false;

    protected $fillable = ['user_id','company_id','vehicle_id','vehicle_type','vehicle_name','vehicle_number'];

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
    
    /**
     * Join with car_type table
     *
     */    
    public function car_type()
    {
        return $this->belongsTo(\App\Models\CarType::class,'vehicle_id','id');
    }

    /**
     * Join with user table
     *
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id','id');
    }

    /**
     * Join with user table
     *
     */
    public function female_driver()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id','id')->whereUserType('Driver')->whereGender('2');
    }

    public function make()
    {
        return $this->belongsTo(\App\Models\MakeVehicle::class, 'vehicle_make_id', 'id');
    }

    public function makeWithSelected() {
        return $this->belongsTo(\App\Models\MakeVehicle::class, 'vehicle_make_id', 'id')->select('id','make_vehicle_name as name');
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\VehicleModel::class, 'vehicle_model_id', 'id');
    }

    public function modelWithSelected() {
        return $this->belongsTo(\App\Models\VehicleModel::class, 'vehicle_model_id', 'id')->select('id','model_name as name');
    }

    public static function findVehicleExist($id,$user_id) {
        return Vehicle::where('id',$id)
        ->where('user_id',$user_id)
        ->first();
    }

    public static function getPreDefaultVehicle($user_id, $excludeVehicleId = null) {
        $query = Vehicle::where('user_id',$user_id)
        ->where('default_type', '1');

        if($excludeVehicleId) {
            $query->where('id','!=',$excludeVehicleId);
        }

        return $query->first();
    }

    public static function ensureDefaultForUser(int $userId): void
    {
        $vehicles = Vehicle::where('user_id', $userId)->orderBy('id')->get();
        if ($vehicles->isEmpty()) {
            return;
        }

        $activeVehicles = $vehicles->where('is_active', 1);
        $existingDefault = $vehicles->firstWhere('default_type', '1');

        if ($activeVehicles->count() === 1) {
            $defaultVehicle = $activeVehicles->first();
            Vehicle::where('user_id', $userId)->update(['default_type' => '0']);
            Vehicle::where('id', $defaultVehicle->id)->update(['default_type' => '1']);
            return;
        }

        if ($vehicles->count() === 1) {
            Vehicle::where('id', $vehicles->first()->id)->update(['default_type' => '1']);
            return;
        }

        if (!$existingDefault && $activeVehicles->isNotEmpty()) {
            Vehicle::where('user_id', $userId)->update(['default_type' => '0']);
            Vehicle::where('id', $activeVehicles->first()->id)->update(['default_type' => '1']);
        }
    }

    // Get Translated Status Attribute
    public function getTransStatusAttribute()
    {
        return trans('messages.driver_dashboard.'.$this->attributes['status']);
    }

    /**
     * Join with filter objects table
     *
     */
    public function female() {
        return $this->hasMany(\App\Models\FilterObject::class,'object_id','id')->whereType('vehicle')->whereFilterId(1);
    }

    /**
     * Join with filter objects table
     *
     */
    public function handicap() {
        return $this->hasMany(\App\Models\FilterObject::class,'object_id','id')->whereType('vehicle')->whereFilterId(2);
    }

    /**
     * Join with filter objects table
     *
     */
    public function child_seat() {
        return $this->hasMany(\App\Models\FilterObject::class,'object_id','id')->whereType('vehicle')->whereFilterId(3);
    }
}
