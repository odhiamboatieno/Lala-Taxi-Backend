<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParcelPackageSize;
use Validator;

class ParcelPackageSizeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $sizes = ParcelPackageSize::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('key', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('subtitle', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parcel_package_size.view', [
            'sizes' => $sizes,
            'search' => $search,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin.parcel_package_size.add');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_package_sizes,key',
                'title' => 'required|max:120',
                'price_multiplier' => 'required|numeric|min:0.1',
            ];

            $attributes = [
                'key' => 'Size Key',
                'title' => 'Size Title',
                'price_multiplier' => 'Price Multiplier',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $size = new ParcelPackageSize;
            $size->key = strtolower($request->key);
            $size->title = $request->title;
            $size->subtitle = $request->subtitle;
            $size->description = $request->description;
            $size->price_multiplier = $request->price_multiplier;
            $size->sort_order = $request->sort_order ?? 0;
            $size->is_active = $request->is_active ? true : false;
            $size->save();

            flashMessage('success', 'Package Size Added Successfully');
        }

        return redirect('admin/parcel_package_sizes');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['packageSize'] = ParcelPackageSize::find($request->id);
            if ($data['packageSize']) {
                return view('admin.parcel_package_size.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_package_sizes,key,' . $request->id,
                'title' => 'required|max:120',
                'price_multiplier' => 'required|numeric|min:0.1',
            ];

            $attributes = [
                'key' => 'Size Key',
                'title' => 'Size Title',
                'price_multiplier' => 'Price Multiplier',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $size = ParcelPackageSize::find($request->id);
            $size->key = strtolower($request->key);
            $size->title = $request->title;
            $size->subtitle = $request->subtitle;
            $size->description = $request->description;
            $size->price_multiplier = $request->price_multiplier;
            $size->sort_order = $request->sort_order ?? $size->sort_order;
            $size->is_active = $request->is_active ? true : false;
            $size->save();

            flashMessage('success', 'Package Size Updated Successfully');
        }

        return redirect('admin/parcel_package_sizes');
    }

    public function delete(Request $request)
    {
        $size = ParcelPackageSize::find($request->id);
        if ($size) {
            $size->delete();
            flashMessage('success', 'Package Size Deleted Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_package_sizes');
    }

    public function toggle_status(Request $request)
    {
        $size = ParcelPackageSize::find($request->id);
        if ($size) {
            $size->is_active = !$size->is_active;
            $size->save();
            flashMessage('success', 'Status Updated Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_package_sizes');
    }
}
