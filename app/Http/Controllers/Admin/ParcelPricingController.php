<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParcelFare;
use App\Models\ParcelCategory;
use App\Models\CarType;
use App\Models\Location;
use Validator;

class ParcelPricingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $fares = ParcelFare::query()
            ->with(['parcelCategory', 'carType', 'location'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->whereHas('parcelCategory', fn ($category) => $category->where('title', 'like', '%'.$search.'%'))
                        ->orWhereHas('carType', fn ($carType) => $carType->where('car_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('location', fn ($location) => $location->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parcel_pricing.view', [
            'fares' => $fares,
            'search' => $search,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['categories'] = ParcelCategory::where('is_active', true)->orderBy('sort_order')->get();
            $data['car_types'] = CarType::where('status', 'Active')->get();
            $data['locations'] = Location::where('status', 'Active')->get();
            return view('admin.parcel_pricing.add', $data);
        }

        if ($request->submit) {
            $rules = [
                'parcel_category_id' => 'required|exists:parcel_categories,id',
                'car_type_id' => 'required|exists:car_type,id',
                'base_fare' => 'required|numeric|min:0',
                'per_km_rate' => 'required|numeric|min:0',
                'min_fare' => 'required|numeric|min:0',
            ];

            $attributes = [
                'parcel_category_id' => 'Parcel Category',
                'car_type_id' => 'Vehicle Type',
                'base_fare' => 'Base Fare',
                'per_km_rate' => 'Per KM Rate',
                'min_fare' => 'Minimum Fare',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $existing = ParcelFare::where('parcel_category_id', $request->parcel_category_id)
                ->where('car_type_id', $request->car_type_id)
                ->where('location_id', $request->location_id ?: null)
                ->first();

            if ($existing) {
                flashMessage('danger', 'A fare for this category and vehicle combination already exists.');
                return back()->withInput();
            }

            $fare = new ParcelFare;
            $fare->parcel_category_id = $request->parcel_category_id;
            $fare->car_type_id = $request->car_type_id;
            $fare->location_id = $request->location_id ?: null;
            $fare->base_fare = $request->base_fare;
            $fare->per_km_rate = $request->per_km_rate;
            $fare->min_fare = $request->min_fare;
            $fare->is_active = $request->is_active ? true : false;
            $fare->save();

            flashMessage('success', 'Parcel Fare Added Successfully');
        }

        return redirect('admin/parcel_pricing');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['result'] = ParcelFare::find($request->id);
            if ($data['result']) {
                $data['categories'] = ParcelCategory::where('is_active', true)->orderBy('sort_order')->get();
                $data['car_types'] = CarType::where('status', 'Active')->get();
                $data['locations'] = Location::where('status', 'Active')->get();
                return view('admin.parcel_pricing.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $rules = [
                'parcel_category_id' => 'required|exists:parcel_categories,id',
                'car_type_id' => 'required|exists:car_type,id',
                'base_fare' => 'required|numeric|min:0',
                'per_km_rate' => 'required|numeric|min:0',
                'min_fare' => 'required|numeric|min:0',
            ];

            $attributes = [
                'parcel_category_id' => 'Parcel Category',
                'car_type_id' => 'Vehicle Type',
                'base_fare' => 'Base Fare',
                'per_km_rate' => 'Per KM Rate',
                'min_fare' => 'Minimum Fare',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $fare = ParcelFare::find($request->id);
            $fare->parcel_category_id = $request->parcel_category_id;
            $fare->car_type_id = $request->car_type_id;
            $fare->location_id = $request->location_id ?: null;
            $fare->base_fare = $request->base_fare;
            $fare->per_km_rate = $request->per_km_rate;
            $fare->min_fare = $request->min_fare;
            $fare->is_active = $request->is_active ? true : false;
            $fare->save();

            flashMessage('success', 'Parcel Fare Updated Successfully');
        }

        return redirect('admin/parcel_pricing');
    }

    public function delete(Request $request)
    {
        $fare = ParcelFare::find($request->id);
        if ($fare) {
            $fare->delete();
            flashMessage('success', 'Parcel Fare Deleted Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_pricing');
    }

    public function bulk_update(Request $request)
    {
        if ($request->submit) {
            $fares = $request->fares;
            if (is_array($fares)) {
                foreach ($fares as $id => $data) {
                    $fare = ParcelFare::find($id);
                    if ($fare) {
                        $fare->base_fare = $data['base_fare'] ?? $fare->base_fare;
                        $fare->per_km_rate = $data['per_km_rate'] ?? $fare->per_km_rate;
                        $fare->min_fare = $data['min_fare'] ?? $fare->min_fare;
                        $fare->is_active = isset($data['is_active']) ? true : false;
                        $fare->save();
                    }
                }
            }
            flashMessage('success', 'Parcel Fares Updated Successfully');
        }

        return redirect('admin/parcel_pricing');
    }
}
