<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;
use Stu\StuTestCase;

class StatusBarFactoryTest extends StuTestCase
{
    private StatusBarFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new StatusBarFactory();
    }

    public function testCreateStatusBarReturnsInstance(): void
    {
        static::assertInstanceOf(
            StatusBar::class,
            $this->subject->createStatusBar()
        );
    }
}
