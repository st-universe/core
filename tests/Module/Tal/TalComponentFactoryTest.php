<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;
use Stu\StuTestCase;

class TalComponentFactoryTest extends StuTestCase
{
    private TalComponentFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new TalComponentFactory();
    }

    public function testCreateTalStatusBarReturnsInstance(): void
    {
        static::assertInstanceOf(
            TalStatusBar::class,
            $this->subject->createTalStatusBar()
        );
    }
}
