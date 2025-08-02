<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Stu\StuTestCase;

class LogTypeEnumTest extends StuTestCase
{
    public function testGetLogfilePath(): void
    {
        $result = LogTypeEnum::ANOMALY->getLogfilePath('path/to/logs');

        $this->assertEquals('path/to/logs/anomaly/anomaly.log', $result);
    }

    public function testIsRotating(): void
    {
        $result = LogTypeEnum::DEFAULT->isRotating();

        $this->assertFalse($result);
    }
}
