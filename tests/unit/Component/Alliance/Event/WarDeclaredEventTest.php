<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class WarDeclaredEventTest extends StuTestCase
{
    private MockInterface&Alliance $alliance;

    private MockInterface&Alliance $counterpart;

    private MockInterface&User $responsibleUser;

    private WarDeclaredEvent $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->alliance = $this->mock(Alliance::class);
        $this->counterpart = $this->mock(Alliance::class);
        $this->responsibleUser = $this->mock(User::class);

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
