<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\StuTestCase;

class CleanTextUtilsTest extends StuTestCase
{
    public function testClearUnicodeRemoveUnicode(): void
    {
        $result = CleanTextUtils::clearUnicode('abc &#12345; &100000011; &; && ;;;def');

        $this->assertEquals('abc   &; && ;;;def', $result);
    }

    public function testClearUnicodeNoUnicodeFound_SemicolonMissing(): void
    {
        $result = CleanTextUtils::clearUnicode('abc &#12345 def');

        $this->assertEquals('abc &#12345 def', $result);
    }
}
