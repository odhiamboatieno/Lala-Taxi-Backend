<?php

namespace App\Services\Modules;

use App\Models\Module;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class ModuleManager
{
    /**
     * Absolute filesystem path of the modules directory.
     */
    protected string $modulesPath;

    public function __construct(protected ?Application $app = null)
    {
        $this->app = $app ?? app();
        $this->modulesPath = base_path('Modules');
    }

    public function modulesPath(): string
    {
        return $this->modulesPath;
    }

    /**
     * Scan the Modules directory and return a structured list of
     * all module folders on disk (whether or not they are installed).
     */
    public function scanDisk(): array
    {
        if (! File::isDirectory($this->modulesPath)) {
            return [];
        }

        $installed = $this->allFromRegistry()->keyBy('name');

        $modules = [];
        foreach (File::directories($this->modulesPath) as $dir) {
            $name = basename($dir);
            $manifestPath = $dir.DIRECTORY_SEPARATOR.'module.json';
            if (! File::exists($manifestPath)) {
                continue;
            }

            $manifest = $this->readManifest($manifestPath);
            if (! $manifest) {
                continue;
            }

            $manifest['name'] = $manifest['name'] ?? $name;
            $manifest['_path'] = $dir;
            $manifest['_installed'] = $installed->has($manifest['name']);
            $manifest['_enabled'] = (bool) ($installed[$manifest['name']]->enabled ?? false);
            $manifest['_registered'] = $installed->has($manifest['name']);
            $manifest['_record'] = $installed[$manifest['name']] ?? null;
            $modules[] = $manifest;
        }

        usort($modules, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $modules;
    }

    /**
     * Read all registered modules from the database (with cache).
     */
    public function allFromRegistry()
    {
        return Cache::remember('modules.registry', 60, function () {
            try {
                return Module::orderBy('name')->get();
            } catch (Throwable $e) {
                return collect();
            }
        });
    }

    public function enabledModules()
    {
        return $this->allFromRegistry()->where('enabled', true);
    }

    /**
     * Register all enabled modules' service providers with Laravel.
     * Called from StartServiceProvider::boot().
     */
    public function registerEnabledProviders(): void
    {
        foreach ($this->enabledModules() as $module) {
            foreach ($module->getProviderClasses() as $providerClass) {
                if (! class_exists($providerClass)) {
                    continue;
                }
                try {
                    $this->app->register($providerClass, force: false);
                } catch (Throwable $e) {
                    Log::warning("ModuleManager: failed to register provider {$providerClass}: ".$e->getMessage());
                }
            }
        }
    }

    /**
     * Load module helpers (require_once) for every enabled module.
     * These mirror the typical module/Helpers/helpers.php file convention.
     */
    public function loadEnabledHelpers(): void
    {
        foreach ($this->enabledModules() as $module) {
            $helpersPath = $module->path.DIRECTORY_SEPARATOR.'Helpers'.DIRECTORY_SEPARATOR.'helpers.php';
            if (File::exists($helpersPath)) {
                require_once $helpersPath;
            }
        }
    }

    public function findOnDisk(string $name): ?string
    {
        $candidate = $this->modulesPath.DIRECTORY_SEPARATOR.$name;
        if (File::isDirectory($candidate) && File::exists($candidate.DIRECTORY_SEPARATOR.'module.json')) {
            return $candidate;
        }
        return null;
    }

    public function readManifest(string $path): ?array
    {
        try {
            $data = json_decode(File::get($path), true, 32, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : null;
        } catch (Throwable $e) {
            Log::warning("ModuleManager: invalid manifest at {$path}: ".$e->getMessage());
            return null;
        }
    }

    public function flushCache(): void
    {
        Cache::forget('modules.registry');
    }
}
