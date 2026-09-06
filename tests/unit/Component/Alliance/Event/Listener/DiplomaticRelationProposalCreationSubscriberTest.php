<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event\Listener;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\StuTestCase;

class DiplomaticRelationProposalCreationSubscriberTest extends StuTestCase
{
    private MockInterface&RelationRepositoryInterface $allianceRelationRepository;

    private MockInterface&RelationPermissionRepositoryInterface $relationPermissionRepository;

    private MockInterface&AllianceActionManagerInterface $allianceActionManager;

    private DiplomaticRelationProposalCreationSubscriber $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(RelationRepositoryInterface::class);
        $this->relationPermissionRepository = $this->mock(RelationPermissionRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);

        $this->subject = new DiplomaticRelationProposalCreationSubscriber(
            $this->allianceRelationRepository,
            $this->relationPermissionRepository,
            $this->allianceActionManager
        );
    }

    public function testOnWarDeclarationHandlesEvent(): void
    {
        $event = $this->mock(WarDeclaredEvent::class);
        $alliance = $this->mock(Alliance::class);
        $counterpart = $this->mock(Alliance::class);
        $relation = $this->mock(Relation::class);

        $allianceName = 'some-name';
        $counterpartId = 666;

        $event->shouldReceive('getAlliance')->withNoArgs()->once()->andReturn($alliance);
        $event->shouldReceive('getCounterpart')->withNoArgs()->once()->andReturn($counterpart);

        $this->allianceRelationRepository
            ->shouldReceive('truncateByAlliances')
            ->with($alliance, $counterpart)
            ->once();
        $this->allianceRelationRepository
            ->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($relation);
        $this->allianceRelationRepository->shouldReceive('save')->with($relation)->once();

        $counterpart->shouldReceive('getId')->withNoArgs()->once()->andReturn($counterpartId);

        $alliance->shouldReceive('getName')->withNoArgs()->once()->andReturn($allianceName);

        $this->allianceActionManager
            ->shouldReceive('sendMessage')
            ->with(
                $counterpartId,
                sprintf('Die Allianz %s hat Deiner Allianz den Krieg erklärt', $allianceName)
            )
            ->once();

        $relation->shouldReceive('setAlliance')->with($alliance)->once()->andReturnSelf();
        $relation->shouldReceive('setOpponent')->with($counterpart)->once()->andReturnSelf();
        $relation->shouldReceive('setType')->with(AllianceRelationTypeEnum::WAR)->once()->andReturnSelf();
        $this->relationPermissionRepository
            ->shouldReceive('replaceForRelation')
            ->with($relation, 0, AllianceRelationTypeEnum::WAR)
            ->once();
        $relation->shouldReceive('setDate')->with(Mockery::type('int'))->once()->andReturnSelf();

        $this->subject->onWarDeclaration($event);
    }

    public function testOnRelationProposalHandlesEvent(): void
    {
        $event = $this->mock(DiplomaticRelationProposedEvent::class);
        $alliance = $this->mock(Alliance::class);
        $counterpart = $this->mock(Alliance::class);
        $relation = $this->mock(Relation::class);

        $allianceName = 'some-name';
        $counterpartId = 666;
        $relationType = AllianceRelationTypeEnum::ALLIED;
        $permissions =
            RelationPermissionEnum::FRIENDLY->value | RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS->value;

        $event->shouldReceive('getAlliance')->withNoArgs()->once()->andReturn($alliance);
        $event->shouldReceive('getCounterpart')->withNoArgs()->once()->andReturn($counterpart);
        $event->shouldReceive('getRelationType')->withNoArgs()->once()->andReturn($relationType);
        $event->shouldReceive('getPermissions')->withNoArgs()->once()->andReturn($permissions);

        $counterpart->shouldReceive('getId')->withNoArgs()->once()->andReturn($counterpartId);

        $alliance->shouldReceive('getName')->withNoArgs()->once()->andReturn($allianceName);

        $this->allianceActionManager
            ->shouldReceive('sendMessage')
            ->with(
                $counterpartId,
                sprintf(
                    'Die Allianz %s hat Deiner Allianz ein Abkommen angeboten',
                    $allianceName
                )
            )
            ->once();

        $this->allianceRelationRepository
            ->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($relation);
        $this->allianceRelationRepository->shouldReceive('save')->with($relation)->once();

        $relation->shouldReceive('setAlliance')->with($alliance)->once()->andReturnSelf();
        $relation->shouldReceive('setOpponent')->with($counterpart)->once()->andReturnSelf();
        $relation->shouldReceive('setType')->with($relationType)->once()->andReturnSelf();
        $this->relationPermissionRepository
            ->shouldReceive('replaceForRelation')
            ->with($relation, $permissions, $relationType)
            ->once();
        $relation->shouldReceive('setDate')->with(0)->once()->andReturnSelf();

        $this->subject->onRelationProposal($event);
    }
}
