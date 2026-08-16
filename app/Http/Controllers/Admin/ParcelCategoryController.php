<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParcelCategory;
use Validator;

class ParcelCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $categories = ParcelCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('key', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parcel_category.view', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin.parcel_category.add');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_categories,key',
                'title' => 'required|max:120',
                'base_fare' => 'required|numeric|min:0',
                'multiplier' => 'required|numeric|min:0.1',
            ];

            $attributes = [
                'key' => 'Category Key',
                'title' => 'Category Title',
                'base_fare' => 'Base Fare',
                'multiplier' => 'Multiplier',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $category = new ParcelCategory;
            $category->key = strtolower($request->key);
            $category->title = $request->title;
            $category->description = $request->description;
            $category->base_fare = $request->base_fare;
            $category->multiplier = $request->multiplier;
            $category->sort_order = $request->sort_order ?? 0;
            $category->is_active = $request->is_active ? true : false;

            if ($request->hasFile('icon_path')) {
                $image = $request->file('icon_path');
                $extension = $image->getClientOriginalExtension();
                $file_name = 'parcel_cat_' . time() . '.' . $extension;
                $target_dir = public_path('images/parcel_icons');
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $image->move($target_dir, $file_name);
                $category->icon_path = 'images/parcel_icons/' . $file_name;
            }

            $category->save();
            flashMessage('success', 'Parcel Category Added Successfully');
        }

        return redirect('admin/parcel_categories');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['parcelCategory'] = ParcelCategory::find($request->id);
            if ($data['parcelCategory']) {
                return view('admin.parcel_category.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_categories,key,' . $request->id,
                'title' => 'required|max:120',
                'base_fare' => 'required|numeric|min:0',
                'multiplier' => 'required|numeric|min:0.1',
            ];

            $attributes = [
                'key' => 'Category Key',
                'title' => 'Category Title',
                'base_fare' => 'Base Fare',
                'multiplier' => 'Multiplier',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $category = ParcelCategory::find($request->id);
            $category->key = strtolower($request->key);
            $category->title = $request->title;
            $category->description = $request->description;
            $category->base_fare = $request->base_fare;
            $category->multiplier = $request->multiplier;
            $category->sort_order = $request->sort_order ?? $category->sort_order;
            $category->is_active = $request->is_active ? true : false;

            if ($request->hasFile('icon_path')) {
                $image = $request->file('icon_path');
                $extension = $image->getClientOriginalExtension();
                $file_name = 'parcel_cat_' . time() . '.' . $extension;
                $target_dir = public_path('images/parcel_icons');
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $image->move($target_dir, $file_name);
                $category->icon_path = 'images/parcel_icons/' . $file_name;
            }

            $category->save();
            flashMessage('success', 'Parcel Category Updated Successfully');
        }

        return redirect('admin/parcel_categories');
    }

    public function delete(Request $request)
    {
        $category = ParcelCategory::find($request->id);
        if ($category) {
            $category->delete();
            flashMessage('success', 'Parcel Category Deleted Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_categories');
    }

    public function toggle_status(Request $request)
    {
        $category = ParcelCategory::find($request->id);
        if ($category) {
            $category->is_active = !$category->is_active;
            $category->save();
            flashMessage('success', 'Status Updated Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_categories');
    }
}
