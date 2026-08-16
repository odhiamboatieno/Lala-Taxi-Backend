<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $frameworkDirectory = realpath(__DIR__.'/../storage/framework') ?: (__DIR__.'/../storage/framework');

        foreach ([
            $frameworkDirectory,
            $frameworkDirectory.DIRECTORY_SEPARATOR.'cache',
            $frameworkDirectory.DIRECTORY_SEPARATOR.'views',
            $frameworkDirectory.DIRECTORY_SEPARATOR.'sessions',
            $frameworkDirectory.DIRECTORY_SEPARATOR.'testing-tmp',
        ] as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }

        $tempDirectory = $frameworkDirectory.DIRECTORY_SEPARATOR.'testing-tmp';

        putenv('TMP='.$tempDirectory);
        putenv('TEMP='.$tempDirectory);
        putenv('TMPDIR='.$tempDirectory);
        ini_set('sys_temp_dir', $tempDirectory);
        error_reporting(error_reporting() & ~E_WARNING);

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
