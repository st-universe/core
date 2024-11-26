<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\StuTestCase;

class CleanTextUtilsTest extends StuTestCase
{
    public function testClearUnicodeRemoveUnicode(): void
    {
        $result = CleanTextUtils::clearUnicode('abc &#12345; &100000011; &; && ;;;def &12');

        $this->assertEquals('abc   &; && ;;;def ', $result);
    }
}
