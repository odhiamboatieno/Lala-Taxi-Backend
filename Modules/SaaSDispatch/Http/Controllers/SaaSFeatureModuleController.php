<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\SaaSFeatureModule;
use Modules\SaaSDispatch\Services\SaaSModuleManager;

class SaaSFeatureModuleController extends Controller
{
    public function index(SaaSModuleManager $moduleManager)
    {
        return view('saasdispatch::feature_modules.index', [
            'modules' => $moduleManager->featureModules(),
        ]);
    }

    public function update(Request $request, SaaSFeatureModule $module): RedirectResponse
    {
        $module->update([
            'is_installed' => $request->boolean('is_installed'),
            'is_enabled' => $request->boolean('is_installed') && $request->boolean('is_enabled'),
            'status' => $request->boolean('is_installed') && $request->boolean('is_enabled') ? 'Active' : 'Inactive',
        ]);

        flashMessage('success', 'SaaS feature module updated successfully.');

        return redirect()->route('saasdispatch.modules.index');
    }
}
