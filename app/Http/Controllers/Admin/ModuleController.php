<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\Modules\ModuleInstaller;
use App\Services\Modules\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleManager $manager,
        protected ModuleInstaller $installer,
    ) {}

    /**
     * List all modules found on disk + their install/enable status.
     */
    public function index()
    {
        $modules = $this->manager->scanDisk();

        return view('admin.modules.index', [
            'modules' => $modules,
            'pageTitle' => 'Modules',
            'pageSubtitle' => 'Install, enable and manage WiTaxi add-on modules.',
        ]);
    }

    /**
     * Show upload form / details.
     */
    public function create()
    {
        return view('admin.modules.upload', [
            'pageTitle' => 'Upload module',
            'pageSubtitle' => 'Upload a module zip and install it into WiTaxi.',
        ]);
    }

    /**
     * Receive a module zip, install + auto-enable.
     */
    public function store(Request $request)
    {
        $request->validate([
            'module' => ['required', 'file', 'mimetypes:application/zip,application/x-zip-compressed,application/octet-stream', 'max:51200'],
            'enable' => ['nullable', 'boolean'],
            'run_migrations' => ['nullable', 'boolean'],
        ]);

        try {
            $module = $this->installer->installFromUpload(
                $request->file('module'),
                autoEnable: (bool) $request->boolean('enable', true),
                runMigrations: (bool) $request->boolean('run_migrations', true),
            );

            Cache::forget('modules.registry');

            return redirect()
                ->route('admin.modules.index')
                ->with('success', "Module '{$module->name}' installed successfully.");
        } catch (\Throwable $e) {
            Log::error('Module upload failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()
                ->withInput()
                ->with('error', 'Upload failed: '.$e->getMessage());
        }
    }

    public function enable(Module $module)
    {
        $this->installer->enable($module);
        return back()->with('success', "Module '{$module->name}' enabled.");
    }

    public function disable(Module $module)
    {
        $this->installer->disable($module);
        return back()->with('success', "Module '{$module->name}' disabled.");
    }

    public function migrate(Module $module)
    {
        $this->installer->runMigrationsFor($module);
        return back()->with('success', "Migrations ran for '{$module->name}'.");
    }

    public function destroy(Request $request, Module $module)
    {
        $removeFiles = (bool) $request->boolean('remove_files', true);
        $name = $module->name;
        $this->installer->uninstall($module, $removeFiles);

        return redirect()
            ->route('admin.modules.index')
            ->with('success', "Module '{$name}' uninstalled.");
    }

    /**
     * Re-register a module that already exists on disk but is missing
     * from the database (e.g. after a server restore).
     */
    public function rediscover(Request $request)
    {
        $request->validate(['name' => ['required', 'string']]);
        $path = $this->manager->findOnDisk($request->input('name'));

        if (! $path) {
            return back()->with('error', "Module folder not found: {$request->input('name')}");
        }

        try {
            $module = $this->installer->installFromPath($path, true, false);
            return back()->with('success', "Module '{$module->name}' re-registered.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Re-register failed: '.$e->getMessage());
        }
    }
}
