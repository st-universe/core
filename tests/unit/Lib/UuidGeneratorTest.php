<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\StuTestCase;

class UuidGeneratorTest extends StuTestCase
{
    private UuidGenerator $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->subject = new UuidGenerator();
    }

    public function testGenV4ReturnsUuid(): void
    {
        static::assertMatchesRegularExpression(
            '/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/',
            $this->subject->genV4()
        );
    }
}
