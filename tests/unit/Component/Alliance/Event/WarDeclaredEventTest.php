<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class WarDeclaredEventTest extends StuTestCase
{
    private MockInterface&AllianceInterface $alliance;

    private MockInterface&AllianceInterface $counterpart;

    private MockInterface&UserInterface $responsibleUser;

    private WarDeclaredEvent $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->alliance = $this->mock(AllianceInterface::class);
        $this->counterpart = $this->mock(AllianceInterface::class);
        $this->responsibleUser = $this->mock(UserInterface::class);

        $this->subject = new WarDeclaredEvent(
            $this->alliance,
            $this->counterpart,
            $this->responsibleUser
        );
    }

    public function testGetAllianceReturnsValue(): void
    {
        static::assertSame(
            $this->alliance,
            $this->subject->getAlliance()
        );
    }

    public function testGetCounterpartReturnsValue(): void
    {
        static::assertSame(
            $this->counterpart,
            $this->subject->getCounterpart()
        );
    }

    public function testGetResponsibleUserReturnsValue(): void
    {
        static::assertSame(
            $this->responsibleUser,
            $this->subject->getResponsibleUser()
        );
    }
}
