<?php

declare(strict_types=1);

namespace Stu\Config;

use ReflectionClass;
use Stu\StuTestCase;

class InitTest extends StuTestCase
{
    public function testInitCallsGivenCallable(): void
    {
        $output = 'some-output';
        $initContainer = $this->getStaticProperty(Init::class, 'CONTAINER');
        $configFiles = $this->getStaticProperty(ConfigFileSetup::class, 'configFiles');
        $errorReporting = error_reporting();
        $includePath = get_include_path();
        $timezone = date_default_timezone_get();

        try {
            $this->setStaticProperty(Init::class, 'CONTAINER', null);
            error_reporting(0);

            static::expectOutputString($output);

            Init::run(static function () use ($output): void {
                echo $output;
            }, false);
        } finally {
            $this->setStaticProperty(Init::class, 'CONTAINER', $initContainer);
            $this->setStaticProperty(ConfigFileSetup::class, 'configFiles', $configFiles);
            error_reporting($errorReporting);
            set_include_path($includePath);
            date_default_timezone_set($timezone);
        }
    }

    /** @param class-string $className */
    private function getStaticProperty(string $className, string $propertyName): mixed
    {
        return (new ReflectionClass($className))->getProperty($propertyName)->getValue();
    }

    /** @param class-string $className */
    private function setStaticProperty(string $className, string $propertyName, mixed $value): void
    {
        (new ReflectionClass($className))->getProperty($propertyName)->setValue(null, $value);
    }
}
