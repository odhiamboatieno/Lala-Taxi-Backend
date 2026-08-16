<?php

/**
 * Vehicle Type Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Vehicle Type
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CarType;
use App\Models\DriverLocation;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Schema;
use Validator;

class VehicleTypeController extends Controller
{
    /**
     * Load Datatable for vehicle Type
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search')),
            'status_scope' => trim((string) $request->get('status_scope', 'all')),
        ];

        $query = CarType::query()
            ->select('car_type.*')
            ->selectSub(function ($subQuery) {
                $subQuery->from('vehicle')
                    ->selectRaw('COUNT(*)')
                    ->whereRaw('FIND_IN_SET(car_type.id, vehicle.vehicle_id)');
            }, 'vehicles_count');

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('car_name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($filters['status_scope'] === 'active') {
            $query->where('status', 'Active');
        }
        elseif ($filters['status_scope'] === 'inactive') {
            $query->where('status', 'Inactive');
        }

        $vehicleTypes = $query->orderBy('id')->paginate(12)->appends($request->query());

        $summary = [
            'total_types' => (int) CarType::query()->count(),
            'active_types' => (int) CarType::query()->where('status', 'Active')->count(),
            'inactive_types' => (int) CarType::query()->where('status', 'Inactive')->count(),
            'total_vehicles' => (int) Vehicle::query()->count(),
        ];

        $typeCards = CarType::query()
            ->select('id', 'car_name', 'vehicle_image', 'status')
            ->selectSub(function ($subQuery) {
                $subQuery->from('vehicle')
                    ->selectRaw('COUNT(*)')
                    ->whereRaw('FIND_IN_SET(car_type.id, vehicle.vehicle_id)');
            }, 'vehicles_count')
            ->orderByDesc('vehicles_count')
            ->limit(6)
            ->get();

        return view('admin.vehicle_type.view', compact('vehicleTypes', 'filters', 'summary', 'typeCards'));
    }

    /**
     * Add a New vehicle Type
     *
     * @param array $request  Input values
     * @return redirect     to vehicle Type view
     */
    public function add(Request $request)
    {
        if($request->isMethod('GET')) {
           
            return view('admin.vehicle_type.add');
        }
        if($request->submit) {
            $hasAppImageColumn = Schema::hasColumn('car_type', 'app_image');
            $rules = [
                'vehicle_name'  => 'required|unique:car_type,car_name,'.$request->id,
                'vehicle_image' => 'required|mimes:jpg,jpeg,png,gif',                
                'active_image'  => 'required|mimes:jpg,jpeg,png,gif',
                'is_pool'       => 'required',
                'status'        => 'required',
            ];
            if ($hasAppImageColumn) {
                $rules['app_image'] = 'required|mimes:jpg,jpeg,png,gif';
            }

            $attributes = [
                'vehicle_name'  => 'Name',                      
                'active_image'  => 'Active image',
                'vehicle_image' => 'Vehicle image',
                'status'        => 'Status',
                'is_pool'       => 'is For Pool',
            ];
            if ($hasAppImageColumn) {
                $attributes['app_image'] = 'App icon';
            }

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->after(function ($validator) use($request) {
                $active_car = CarType::where('status','active')->count();
                if($active_car<=0 && $request->status=='Inactive') {
                   $validator->errors()->add('status',"Atleast one vehicle type should be in active status");
                }
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $vehicle_type = new  CarType;
            $vehicle_type->car_name     = $request->vehicle_name;
            $vehicle_type->description  = $request->description;
            $vehicle_type->is_pool       = $request->is_pool;
            $vehicle_type->status       = $request->status;

            $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/car_image';

            if ($request->hasFile('active_image')) {
                $image = $request->file('active_image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "active_image_".time().".".$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
                $vehicle_type->active_image = $upload_result['file_name'];
            }

            if ($request->hasFile('vehicle_image')) {
                $image = $request->file('vehicle_image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "vehicle_image_".time().".".$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
                $vehicle_type->vehicle_image = $upload_result['file_name'];
            }

            if ($hasAppImageColumn && $request->hasFile('app_image')) {
                $image = $request->file('app_image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "app_image_".time().".".$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
                $vehicle_type->app_image = $upload_result['file_name'];
            }

            $vehicle_type->save();
            flashMessage('success', 'Added Successfully');
        }

        return redirect('admin/vehicle_type');
    }

    /**
     * Update vehicle Type Details
     *
     * @param array $request    Input values
     * @return redirect     to vehicle Type View
     */
    public function update(Request $request)
    {
        if($request->isMethod('GET')) {
            $data['result'] = CarType::find($request->id);
            if($data['result']) {
                return view('admin.vehicle_type.edit', $data);  
            }
            flashMessage('danger', 'Invalid ID');
        }
        if($request->submit) {
            $hasAppImageColumn = Schema::hasColumn('car_type', 'app_image');
            $rules = [
                'vehicle_name'  => 'required|unique:car_type,car_name,'.$request->id,
                'is_pool'       => 'required',
                'status'        => 'required',
                'active_image'  => 'mimes:jpg,jpeg,png,gif',
                'vehicle_image' => 'mimes:jpg,jpeg,png,gif',
            ];
            if ($hasAppImageColumn) {
                $rules['app_image'] = 'mimes:jpg,jpeg,png,gif';
            }

            // add vehicle Type Validation Custom Fields Name
            $attributes = [
                'vehicle_name'  => 'Name',                      
                'active_image'  => 'Active image',
                'vehicle_image' => 'Vehicle image',
                'status'        => 'Status',
                'is_pool'       => 'is For Pool',
            ];
            if ($hasAppImageColumn) {
                $attributes['app_image'] = 'App icon';
            }

            $validator = Validator::make($request->all(), $rules,[], $attributes);
            $validator->after(function ($validator) use($request) {
                $active_car = CarType::where('status','active')->where('id','!=',$request->id)->count();
                if($active_car<=0 && $request->status=='Inactive') {
                   $validator->errors()->add('status',"Atleast one vehicle type should be in active status");
                }
            });
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $vehicle_type = CarType::find($request->id);              
            $vehicle_type->car_name     = $request->vehicle_name;
            $vehicle_type->description  = $request->description;                
            $vehicle_type->status       = $request->status; 
            $vehicle_type->is_pool       = $request->is_pool; 

            $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/car_image';

            if ($request->hasFile('active_image')) {
                $image = $request->file('active_image');

                $extension = $image->getClientOriginalExtension();
                $file_name = "active_image_".time().".".$extension;
                $options = compact('target_dir','file_name');

                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }

                $vehicle_type->active_image = $upload_result['file_name'];
            }

            if ($request->hasFile('vehicle_image')) {
                $image = $request->file('vehicle_image');

                $extension = $image->getClientOriginalExtension();
                $file_name = "vehicle_image_".time().".".$extension;
                $options = compact('target_dir','file_name');

                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }

                $vehicle_type->vehicle_image = $upload_result['file_name'];
            }

            if ($hasAppImageColumn && $request->hasFile('app_image')) {
                $image = $request->file('app_image');

                $extension = $image->getClientOriginalExtension();
                $file_name = "app_image_".time().".".$extension;
                $options = compact('target_dir','file_name');

                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }

                $vehicle_type->app_image = $upload_result['file_name'];
            }

            $vehicle_type->save(); 

            flashMessage('success', 'Updated Successfully');
        }

        return redirect('admin/vehicle_type');
    }

    /**
     * Delete vehicle Type
     *
     * @param array $request    Input values
     * @return redirect     to vehicle Type View
     */
    public function delete(Request $request)
    {
        $driver_location_id = DriverLocation::where('car_id',$request->id)->count();
        $find_vehicle_id = Vehicle::where('vehicle_id',$request->id)->count();
        $active_car = CarType::where('status','active')->where('id','!=',$request->id)->count();
        if($driver_location_id) {
            flashMessage('danger', "Driver using this Vehicle  type, So can't delete this");
        }
        else if($find_vehicle_id) {
            flashMessage('danger', "vehicle using this Vehicle type, So can't delete this");
        }
        else if($active_car<=0) {
            flashMessage('danger', "Atleast one vehicle type should be in active status, So can't delete this");
        }
        else { 
            CarType::where('id',$request->id)->delete();
            flashMessage('success', 'Deleted Successfully');
        }
        return redirect('admin/vehicle_type');
    }
}
