<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\StuTestCase;

class StuRandomTest extends StuTestCase
{
    private StuRandom $stuRandom;

    public function setUp(): void
    {
        $this->stuRandom = new StuRandom();
    }

    public function testRandom_withStandardNormalDistribution(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $value = $this->stuRandom->rand(1, 100, true);

            $this->assertTrue($value >= 1);
            $this->assertTrue($value <= 100);
        }
    }
}
