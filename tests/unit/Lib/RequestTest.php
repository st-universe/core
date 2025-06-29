<?php

declare(strict_types=1);

namespace Stu\Lib;

use request;
use Stu\StuTestCase;

class RequestTest extends StuTestCase
{
    public function testReturnIntExpectZeroWhenNullString(): void
    {
        $this->assertEquals(0, request::returnInt('null'));
    }
}
