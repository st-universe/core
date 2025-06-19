<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\StuTestCase;

class DiplomaticRelationProposedEventTest extends StuTestCase
{
    private MockInterface&AllianceInterface $alliance;

    private MockInterface&AllianceInterface $counterpart;

    private int $relationTypeId = 666;

    private DiplomaticRelationProposedEvent $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->alliance = $this->mock(AllianceInterface::class);
        $this->counterpart = $this->mock(AllianceInterface::class);

        $this->subject = new DiplomaticRelationProposedEvent(
            $this->alliance,
            $this->counterpart,
            $this->relationTypeId
        );
    }

    public function testGetCounterpartReturnsValue(): void
    {
        static::assertSame(
            $this->counterpart,
            $this->subject->getCounterpart()
        );
    }

    public function testGetAllianceReturnsValue(): void
    {
        static::assertSame(
            $this->alliance,
            $this->subject->getAlliance()
        );
    }

    public function testGetRelationTypeIdReturnsValue(): void
    {
        static::assertSame(
            $this->relationTypeId,
            $this->subject->getRelationTypeId()
        );
    }
}
