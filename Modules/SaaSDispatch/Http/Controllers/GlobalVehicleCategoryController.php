<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\SaaSDispatch\Entities\GlobalVehicleCategory;
use Modules\SaaSDispatch\Http\Requests\GlobalVehicleCategoryRequest;

class GlobalVehicleCategoryController extends Controller
{
    public function index()
    {
        return view('saasdispatch::global_vehicle_categories.index', [
            'categories' => GlobalVehicleCategory::query()->latest('sort_order')->latest('id')->get(),
        ]);
    }

    public function store(GlobalVehicleCategoryRequest $request): RedirectResponse
    {
        GlobalVehicleCategory::query()->create($this->payload($request));
        flashMessage('success', 'Global vehicle category created successfully.');

        return redirect()->route('saasdispatch.global-vehicle-categories.index');
    }

    public function update(GlobalVehicleCategoryRequest $request, GlobalVehicleCategory $globalVehicleCategory): RedirectResponse
    {
        $globalVehicleCategory->update($this->payload($request));
        flashMessage('success', 'Global vehicle category updated successfully.');

        return redirect()->route('saasdispatch.global-vehicle-categories.index');
    }

    public function destroy(GlobalVehicleCategory $globalVehicleCategory): RedirectResponse
    {
        $globalVehicleCategory->delete();
        flashMessage('success', 'Global vehicle category deleted successfully.');

        return redirect()->route('saasdispatch.global-vehicle-categories.index');
    }

    protected function payload(GlobalVehicleCategoryRequest $request): array
    {
        return [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'icon' => $request->input('icon'),
            'is_visible_to_rider' => $request->boolean('is_visible_to_rider'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status'),
        ];
    }
}
