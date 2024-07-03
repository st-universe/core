<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event\Listener;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\StuTestCase;

class DiplomaticRelationProposalCreationSubscriberTest extends StuTestCase
{
    /** @var MockInterface&AllianceRelationRepositoryInterface */
    private MockInterface $allianceRelationRepository;

    /** @var MockInterface&AllianceActionManagerInterface */
    private MockInterface $allianceActionManager;

    private DiplomaticRelationProposalCreationSubscriber $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(AllianceRelationRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);

        $this->subject = new DiplomaticRelationProposalCreationSubscriber(
            $this->allianceRelationRepository,
            $this->allianceActionManager
        );
    }

    public function testOnWarDeclarationHandlesEvent(): void
    {
        $event = $this->mock(WarDeclaredEvent::class);
        $alliance = $this->mock(AllianceInterface::class);
        $counterpart = $this->mock(AllianceInterface::class);
        $relation = $this->mock(AllianceRelationInterface::class);

        $allianceName = 'some-name';
        $counterpartId = 666;

        $event->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $event->shouldReceive('getCounterpart')
            ->withNoArgs()
            ->once()
            ->andReturn($counterpart);

        $this->allianceRelationRepository->shouldReceive('truncateByAlliances')
            ->with($alliance, $counterpart)
            ->once();
        $this->allianceRelationRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($relation);
        $this->allianceRelationRepository->shouldReceive('save')
            ->with($relation)
            ->once();

        $counterpart->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($counterpartId);

        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceName);

        $this->allianceActionManager->shouldReceive('sendMessage')
            ->with(
                $counterpartId,
                sprintf('Die Allianz %s hat Deiner Allianz den Krieg erklärt', $allianceName)
            )
            ->once();

        $relation->shouldReceive('setAlliance')
            ->with($alliance)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setOpponent')
            ->with($counterpart)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setType')
            ->with(AllianceEnum::ALLIANCE_RELATION_WAR)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setDate')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();

        $this->subject->onWarDeclaration($event);
    }

    public function testOnRelationProposalHandlesEvent(): void
    {
        $event = $this->mock(DiplomaticRelationProposedEvent::class);
        $alliance = $this->mock(AllianceInterface::class);
        $counterpart = $this->mock(AllianceInterface::class);
        $relation = $this->mock(AllianceRelationInterface::class);

        $allianceName = 'some-name';
        $counterpartId = 666;
        $relationType = 42;

        $event->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $event->shouldReceive('getCounterpart')
            ->withNoArgs()
            ->once()
            ->andReturn($counterpart);
        $event->shouldReceive('getRelationTypeId')
            ->withNoArgs()
            ->once()
            ->andReturn($relationType);

        $counterpart->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($counterpartId);

        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceName);

        $this->allianceActionManager->shouldReceive('sendMessage')
            ->with(
                $counterpartId,
                sprintf(
                    'Die Allianz %s hat Deiner Allianz ein Abkommen angeboten',
                    $allianceName
                )
            )
            ->once();

        $this->allianceRelationRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($relation);
        $this->allianceRelationRepository->shouldReceive('save')
            ->with($relation)
            ->once();

        $relation->shouldReceive('setAlliance')
            ->with($alliance)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setOpponent')
            ->with($counterpart)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setType')
            ->with($relationType)
            ->once()
            ->andReturnSelf();
        $relation->shouldReceive('setDate')
            ->with(0)
            ->once()
            ->andReturnSelf();

        $this->subject->onRelationProposal($event);
    }
}
