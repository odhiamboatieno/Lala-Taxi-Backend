<?php

namespace Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CorporateReleaseScriptsTest extends TestCase
{
    private string $temporary;
    private const IMAGE = 'ghcr.io/example/lala-taxi-backend@sha256:'.'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    protected function setUp(): void
    {
        $this->temporary = sys_get_temp_dir().'/lala-release-scripts-'.bin2hex(random_bytes(8));
        mkdir($this->temporary.'/bin', 0700, true);
        foreach (['release.sh', 'preflight.sh'] as $script) {
            copy(dirname(__DIR__, 2).'/deploy/'.$script, $this->temporary.'/'.$script);
        }
        // A test-only Docker executable records the real shell scripts' calls.
        // No daemon, containers, credentials or production services are touched.
        file_put_contents($this->temporary.'/bin/docker', '#!'.PHP_BINARY."\n".<<<'PHP'
<?php
$arguments = array_slice($argv, 1);
$call = ['arguments' => $arguments];
if (array_slice($arguments, 0, 4) === ['compose', 'exec', '-T', 'app']) {
    $call['stdin_bytes'] = strlen(stream_get_contents(STDIN));
}
file_put_contents(getenv('LALA_RELEASE_TEST_LOG'), json_encode($call).PHP_EOL, FILE_APPEND);
$command = implode(' ', $arguments);
if ($command === getenv('LALA_RELEASE_TEST_FAIL_COMMAND')) exit(42);
if ($command === 'compose config --images') {
    foreach (json_decode(getenv('LALA_RELEASE_TEST_IMAGES'), true, 512, JSON_THROW_ON_ERROR) as $image) echo $image.PHP_EOL;
}
PHP);
        chmod($this->temporary.'/bin/docker', 0700);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->temporary);
    }

    private function release(array $overrides = [], bool $pipeScript = false): Process
    {
        $process = new Process($pipeScript ? ['sh'] : ['sh', './release.sh'], $this->temporary, array_replace([
            'PATH' => $this->temporary.'/bin:'.getenv('PATH'),
            'BACKUP_REFERENCE' => 'isolated-test-backup',
            'RESTORE_EVIDENCE' => 'isolated-test-restore',
            'LALA_RELEASE_TEST_LOG' => $this->temporary.'/calls.jsonl',
            'LALA_RELEASE_TEST_IMAGES' => json_encode(['mysql:8.4', 'redis:7.4-alpine', 'nginx:1.27-alpine', ...array_fill(0, 4, self::IMAGE)]),
            'LALA_RELEASE_TEST_FAIL_COMMAND' => '',
        ], $overrides));
        $process->setInput($pipeScript ? file_get_contents($this->temporary.'/release.sh') : 'operator-input-not-for-application-commands');
        $process->setTimeout(10);
        $process->run();
        return $process;
    }

    private function calls(): array
    {
        $path = $this->temporary.'/calls.jsonl';
        return is_file($path) ? array_map(fn ($line) => json_decode($line, true, 512, JSON_THROW_ON_ERROR), file($path, FILE_IGNORE_NEW_LINES)) : [];
    }

    private function commands(): array
    {
        return array_map(fn ($call) => implode(' ', $call['arguments']), $this->calls());
    }

    public function test_release_pulls_only_application_images_and_preserves_database_dependencies(): void
    {
        $process = $this->release();
        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertSame([
            'compose config --quiet',
            'compose config --images',
            'compose pull app queue reverb scheduler',
            'compose stop nginx scheduler queue reverb app',
            'compose up -d --no-deps --wait --wait-timeout 600 app',
            'compose exec -T app php artisan corporate:release-check --no-interaction',
            'compose up -d --no-deps --wait --wait-timeout 180 nginx queue reverb scheduler',
            'compose config --quiet',
            'compose config --images',
            'compose exec -T app php artisan corporate:release-check --no-interaction',
            'compose ps',
        ], $this->commands());
        foreach ($this->calls() as $call) {
            if (array_key_exists('stdin_bytes', $call)) $this->assertSame(0, $call['stdin_bytes']);
        }
    }

    public function test_piped_release_script_is_not_consumed_by_application_exec(): void
    {
        $process = $this->release(pipeScript: true);
        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertContains('compose up -d --no-deps --wait --wait-timeout 180 nginx queue reverb scheduler', $this->commands());
        $this->assertSame('compose ps', array_last($this->commands()));
        $this->assertStringContainsString('Release started.', $process->getOutput());
    }

    public function test_missing_backup_evidence_stops_before_any_docker_call(): void
    {
        foreach (['BACKUP_REFERENCE', 'RESTORE_EVIDENCE'] as $required) {
            $process = $this->release([$required => '']);
            $this->assertNotSame(0, $process->getExitCode());
            $this->assertSame([], $this->calls());
        }
    }

    public function test_image_pull_failure_does_not_stop_running_services(): void
    {
        $this->assertSame(42, $this->release(['LALA_RELEASE_TEST_FAIL_COMMAND' => 'compose pull app queue reverb scheduler'])->getExitCode());
        $this->assertCount(3, $this->calls());
        $this->assertFalse((bool) array_filter($this->commands(), fn ($command) => str_starts_with($command, 'compose stop ')));
    }

    public function test_failed_writer_start_does_not_start_readers_or_check_unready_application(): void
    {
        $command = 'compose up -d --no-deps --wait --wait-timeout 600 app';
        $this->assertSame(42, $this->release(['LALA_RELEASE_TEST_FAIL_COMMAND' => $command])->getExitCode());
        $this->assertSame($command, array_last($this->commands()));
    }

    public function test_failed_schema_runtime_gate_does_not_start_delivery_or_other_readers(): void
    {
        $command = 'compose exec -T app php artisan corporate:release-check --no-interaction';
        $this->assertSame(42, $this->release(['LALA_RELEASE_TEST_FAIL_COMMAND' => $command])->getExitCode());
        $this->assertSame($command, array_last($this->commands()));
        $this->assertCount(6, $this->calls());
    }

    public function test_different_immutable_application_images_fail_before_pull_or_downtime(): void
    {
        $process = $this->release(['LALA_RELEASE_TEST_IMAGES' => json_encode([self::IMAGE, str_replace('aaaa', 'bbbb', self::IMAGE)])]);
        $this->assertNotSame(0, $process->getExitCode());
        $this->assertStringContainsString('same immutable image', $process->getErrorOutput());
        $this->assertCount(2, $this->calls());
    }

    public function test_empty_or_mutable_application_images_fail_before_pull_or_downtime(): void
    {
        foreach ([[], ['ghcr.io/example/lala-taxi-backend:latest']] as $images) {
            $process = $this->release(['LALA_RELEASE_TEST_IMAGES' => json_encode($images)]);
            $this->assertNotSame(0, $process->getExitCode());
        }
        foreach ($this->commands() as $command) $this->assertStringStartsWith('compose config ', $command);
    }
}
