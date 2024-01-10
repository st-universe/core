<?php

declare(strict_types=1);

namespace Stu\Config;

use Stu\StuTestCase;

/**
 * @runTestsInSeparateProcesses Avoid global settings to cause trouble within other tests
 */
class InitTest extends StuTestCase
{
    public function testInitCallsGivenCallable(): void
    {
        error_reporting(0);

        $output = 'some-output';

        static::expectOutputString($output);

        $app = function () use ($output): void {
            echo $output;
        };

        Init::run($app, false);
    }
}
