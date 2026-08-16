<?php

namespace App\Services\Modules;

use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ModuleInstaller
{
    public function __construct(protected ModuleManager $manager) {}

    /**
     * Install a module from a zip upload. Returns the created Module record.
     */
    public function installFromUpload(UploadedFile $file, bool $autoEnable = true, bool $runMigrations = true): Module
    {
        $tempPath = $this->storeTemp($file);

        try {
            $extractedRoot = $this->extract($tempPath);
            $modulePath = $this->resolveModuleRoot($extractedRoot);

            return $this->installFromPath($modulePath, $autoEnable, $runMigrations);
        } finally {
            @File::delete($tempPath);
        }
    }

    /**
     * Register an already-extracted module on disk into the registry.
     * Used both for fresh installs and for re-discovering existing folders.
     */
    public function installFromPath(string $modulePath, bool $autoEnable = true, bool $runMigrations = true): Module
    {
        $manifestPath = $modulePath.DIRECTORY_SEPARATOR.'module.json';
        if (! File::exists($manifestPath)) {
            throw new RuntimeException("Manifest not found at {$manifestPath}");
        }

        $manifest = $this->manager->readManifest($manifestPath);
        if (! $manifest) {
            throw new RuntimeException("Invalid module.json manifest.");
        }

        $name = $manifest['name'] ?? basename($modulePath);
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name)) {
            throw new RuntimeException("Invalid module name: {$name}");
        }

        $targetPath = $this->manager->modulesPath().DIRECTORY_SEPARATOR.$name;

        // If extracted into a different folder, move it.
        if (realpath($modulePath) !== realpath($targetPath)) {
            if (File::exists($targetPath)) {
                // Already there → update, don't blow away.
            } else {
                File::moveDirectory($modulePath, $targetPath);
                $modulePath = $targetPath;
            }
        }

        $manifest = $this->manager->readManifest($modulePath.DIRECTORY_SEPARATOR.'module.json');

        $providers = $manifest['providers'] ?? [];
        $requires = $manifest['requires'] ?? [];
        $hasMigrations = File::isDirectory($modulePath.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations');

        $record = Module::updateOrCreate(
            ['name' => $name],
            [
                'alias' => $manifest['alias'] ?? Str::lower($name),
                'version' => $manifest['version'] ?? '1.0.0',
                'description' => $manifest['description'] ?? null,
                'author' => $manifest['author'] ?? null,
                'path' => $modulePath,
                'providers' => $providers,
                'requires' => $requires,
                'enabled' => $autoEnable,
                'autoload' => true,
                'has_migrations' => $hasMigrations,
                'migrated' => false,
                'checksum' => sha1_file($modulePath.DIRECTORY_SEPARATOR.'module.json') ?: null,
                'installed_at' => now(),
                'enabled_at' => $autoEnable ? now() : null,
            ]
        );

        $this->manager->flushCache();

        if ($autoEnable && $hasMigrations && $runMigrations) {
            $this->runMigrationsFor($record);
        }

        $this->refreshAutoloader();

        return $record;
    }

    public function enable(Module $module): Module
    {
        $module->enabled = true;
        $module->enabled_at = now();
        $module->save();
        $this->manager->flushCache();
        $this->refreshAutoloader();
        return $module;
    }

    public function disable(Module $module): Module
    {
        $module->enabled = false;
        $module->enabled_at = null;
        $module->save();
        $this->manager->flushCache();
        return $module;
    }

    public function uninstall(Module $module, bool $removeFiles = true): void
    {
        $path = $module->path;
        $module->delete();
        $this->manager->flushCache();

        if ($removeFiles && $path && File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * Run any pending migrations shipped with this module.
     * Mirrors how each module's ServiceProvider would load them
     * (we call artisan migrate --path=<module>/Database/Migrations).
     */
    public function runMigrationsFor(Module $module): void
    {
        if (! $module->has_migrations) {
            return;
        }

        $migrationsPath = $module->path.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations';
        if (! File::isDirectory($migrationsPath)) {
            return;
        }

        try {
            \Artisan::call('migrate', [
                '--path' => $this->relativeFromBase($migrationsPath),
                '--force' => true,
            ]);
            $module->migrated = true;
            $module->save();
        } catch (\Throwable $e) {
            Log::warning("ModuleInstaller: migrations failed for {$module->name}: ".$e->getMessage());
        }
    }

    protected function storeTemp(UploadedFile $file): string
    {
        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'module-uploads');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }
        $target = $dir.DIRECTORY_SEPARATOR.Str::uuid()->toString().'.zip';
        $file->move($dir, basename($target));
        return $target;
    }

    protected function extract(string $zipPath): string
    {
        $extractRoot = storage_path('app'.DIRECTORY_SEPARATOR.'module-extracts'.DIRECTORY_SEPARATOR.Str::uuid()->toString());
        File::makeDirectory($extractRoot, 0775, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Cannot open zip: {$zipPath}");
        }
        $zip->extractTo($extractRoot);
        $zip->close();

        return $extractRoot;
    }

    /**
     * The zip may be either:
     *   ModuleName/
     *     module.json
     *     ...
     * or have module.json at the root. Normalize both cases.
     */
    protected function resolveModuleRoot(string $extractRoot): string
    {
        if (File::exists($extractRoot.DIRECTORY_SEPARATOR.'module.json')) {
            return $extractRoot;
        }

        foreach (File::directories($extractRoot) as $sub) {
            if (File::exists($sub.DIRECTORY_SEPARATOR.'module.json')) {
                return $sub;
            }
        }

        throw new RuntimeException("module.json not found in uploaded zip.");
    }

    protected function relativeFromBase(string $abs): string
    {
        $base = base_path().DIRECTORY_SEPARATOR;
        return Str::after($abs, $base);
    }

    /**
     * Best-effort refresh of the composer autoloader so newly added
     * module classes are resolvable. Silently ignored if composer is
     * not available (e.g. on shared hosting).
     */
    public function refreshAutoloader(): void
    {
        $base = base_path();
        if (! File::exists($base.DIRECTORY_SEPARATOR.'composer.json')) {
            return;
        }

        try {
            @shell_exec('cd '.escapeshellarg($base).' && composer dump-autoload -o 2>&1');
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
