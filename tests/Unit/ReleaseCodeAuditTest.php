<?php

namespace Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ReleaseCodeAuditTest extends TestCase
{
    private string $temporary;

    protected function setUp(): void
    {
        $this->temporary = sys_get_temp_dir().'/lala-code-audit-'.bin2hex(random_bytes(8));
        mkdir($this->temporary.'/source', 0700, true);
        mkdir($this->temporary.'/target/app', 0700, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->temporary);
    }

    private function audit(string $old, string $new): Process
    {
        file_put_contents($this->temporary.'/target/.src-files', $old);
        file_put_contents($this->temporary.'/source/.src-files', $new);
        $process = new Process([PHP_BINARY, dirname(__DIR__, 2).'/docker/audit-code.php', $this->temporary.'/source', $this->temporary.'/target']);
        $process->run();
        return $process;
    }

    public function test_unchanged_manifest_passes(): void
    {
        $this->assertSame(0, $this->audit("app/kept.php\n", "app/kept.php\n")->getExitCode());
    }

    public function test_obsolete_code_blocks_sync_without_deleting_it(): void
    {
        file_put_contents($this->temporary.'/target/app/obsolete.php', 'old code');
        $process = $this->audit("app/obsolete.php\n", "app/new.php\n");
        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('app/obsolete.php', $process->getErrorOutput());
        $this->assertSame('old code', file_get_contents($this->temporary.'/target/app/obsolete.php'));
    }

    public function test_uploads_and_traversal_are_never_treated_as_disposable_code(): void
    {
        foreach (["../outside.php\n", "public/images/customer.png\n", "bootstrap/cache/config.php\n"] as $old) {
            $process = $this->audit($old, "app/new.php\n");
            $this->assertSame(1, $process->getExitCode());
            $this->assertStringContainsString('Unsafe prior manifest', $process->getErrorOutput());
        }
    }
}
