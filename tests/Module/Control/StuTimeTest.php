<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Override;
use Stu\StuTestCase;

class StuTimeTest extends StuTestCase
{
    private StuTime $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new StuTime();
    }

    public function testTransformToStuDate(): void
    {
        $this->assertEquals(
            '22.08.2394',
            $this->subject->transformToStuDate(1724320422)
        );
    }

    public function testTransformToStuDateTime(): void
    {
        $this->assertEquals(
            '22.08.2394 09:53',
            $this->subject->transformToStuDateTime(1724320422)
        );
    }
}
