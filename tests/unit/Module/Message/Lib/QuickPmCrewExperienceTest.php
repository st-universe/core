<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Mockery\MockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Module\Message\View\ShowWriteQuickPm\ShowWriteQuickPm;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\StuTestCase;

final class QuickPmCrewExperienceTest extends StuTestCase
{
    private MockInterface&ShipRepositoryInterface $shipRepository;
    private MockInterface&StationRepositoryInterface $stationRepository;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&PlayerRelationDeterminatorInterface $playerRelationDeterminator;
    private MockInterface&EventDispatcherInterface $eventDispatcher;

    private QuickPmCrewExperience $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->playerRelationDeterminator = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->eventDispatcher = $this->mock(EventDispatcherInterface::class);

        $this->subject = new QuickPmCrewExperience(
            $this->shipRepository,
            $this->stationRepository,
            $this->colonyRepository,
            $this->playerRelationDeterminator,
            $this->eventDispatcher
        );
    }

    public function testAwardsExperienceForForeignSpacecraftMessage(): void
    {
        $sender = $this->mock(User::class);
        $sourceUser = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $source = $this->mock(Ship::class);
        $target = $this->mock(Station::class);
        $location = $this->mock(Map::class);

        $sender->shouldReceive('getId')->once()->andReturn(101);
        $sourceUser->shouldReceive('getId')->once()->andReturn(101);
        $targetUser->shouldReceive('getId')->once()->andReturn(102);
        $targetUser->shouldReceive('isNpc')->once()->andReturn(false);
        $source->shouldReceive('getUser')->once()->andReturn($sourceUser);
        $target->shouldReceive('getUser')->times(3)->andReturn($targetUser);
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $location->shouldReceive('getId')->twice()->andReturn(42);

        $this->shipRepository->shouldReceive('find')->with(10)->once()->andReturn($source);
        $this->stationRepository->shouldReceive('find')->with(20)->once()->andReturn($target);
        $this->playerRelationDeterminator->shouldReceive('isFriend')->with($sender, $targetUser)->once()->andReturn(false);
        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(static function (CrewExperienceEvent $event) use ($source): bool {
                return $event->getSpacecraft() === $source
                    && $event->getTrigger() === SkillEnhancementEnum::FOREIGN_CONTACT_MESSAGE;
            })
            ->once()
            ->andReturnUsing(static fn (object $event): object => $event);

        $this->subject->awardExperience(
            $sender,
            102,
            10,
            ShowWriteQuickPm::TYPE_SHIP,
            20,
            ShowWriteQuickPm::TYPE_STATION
        );
    }

    public function testAwardsExperienceForForeignColonyMessage(): void
    {
        $sender = $this->mock(User::class);
        $sourceUser = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $source = $this->mock(Station::class);
        $target = $this->mock(Colony::class);
        $location = $this->mock(Map::class);

        $sender->shouldReceive('getId')->once()->andReturn(101);
        $sourceUser->shouldReceive('getId')->once()->andReturn(101);
        $targetUser->shouldReceive('getId')->once()->andReturn(102);
        $targetUser->shouldReceive('isNpc')->once()->andReturn(false);
        $source->shouldReceive('getUser')->once()->andReturn($sourceUser);
        $target->shouldReceive('getUser')->times(3)->andReturn($targetUser);
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $location->shouldReceive('getId')->twice()->andReturn(42);

        $this->stationRepository->shouldReceive('find')->with(10)->once()->andReturn($source);
        $this->colonyRepository->shouldReceive('find')->with(20)->once()->andReturn($target);
        $this->playerRelationDeterminator->shouldReceive('isFriend')->with($sender, $targetUser)->once()->andReturn(false);
        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(static function (CrewExperienceEvent $event) use ($source): bool {
                return $event->getSpacecraft() === $source
                    && $event->getTrigger() === SkillEnhancementEnum::FOREIGN_CONTACT_MESSAGE;
            })
            ->once()
            ->andReturnUsing(static fn (object $event): object => $event);

        $this->subject->awardExperience(
            $sender,
            102,
            10,
            ShowWriteQuickPm::TYPE_STATION,
            20,
            ShowWriteQuickPm::TYPE_COLONY
        );
    }

    public function testDoesNotAwardExperienceForNpcTargets(): void
    {
        $sender = $this->mock(User::class);
        $sourceUser = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $source = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $sender->shouldReceive('getId')->once()->andReturn(101);
        $sourceUser->shouldReceive('getId')->once()->andReturn(101);
        $targetUser->shouldReceive('getId')->once()->andReturn(99);
        $targetUser->shouldReceive('isNpc')->once()->andReturn(true);
        $source->shouldReceive('getUser')->once()->andReturn($sourceUser);
        $target->shouldReceive('getUser')->twice()->andReturn($targetUser);

        $this->shipRepository->shouldReceive('find')->with(10)->once()->andReturn($source);
        $this->shipRepository->shouldReceive('find')->with(20)->once()->andReturn($target);
        $this->playerRelationDeterminator->shouldReceive('isFriend')->never();
        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->subject->awardExperience(
            $sender,
            99,
            10,
            ShowWriteQuickPm::TYPE_SHIP,
            20,
            ShowWriteQuickPm::TYPE_SHIP
        );
    }

    public function testDoesNotAwardExperienceForFriendlyTargets(): void
    {
        $sender = $this->mock(User::class);
        $sourceUser = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $source = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $location = $this->mock(Map::class);

        $sender->shouldReceive('getId')->once()->andReturn(101);
        $sourceUser->shouldReceive('getId')->once()->andReturn(101);
        $targetUser->shouldReceive('getId')->once()->andReturn(102);
        $targetUser->shouldReceive('isNpc')->once()->andReturn(false);
        $source->shouldReceive('getUser')->once()->andReturn($sourceUser);
        $target->shouldReceive('getUser')->times(3)->andReturn($targetUser);
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $location->shouldReceive('getId')->twice()->andReturn(42);

        $this->shipRepository->shouldReceive('find')->with(10)->once()->andReturn($source);
        $this->shipRepository->shouldReceive('find')->with(20)->once()->andReturn($target);
        $this->playerRelationDeterminator->shouldReceive('isFriend')->with($sender, $targetUser)->once()->andReturn(true);
        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->subject->awardExperience(
            $sender,
            102,
            10,
            ShowWriteQuickPm::TYPE_SHIP,
            20,
            ShowWriteQuickPm::TYPE_SHIP
        );
    }

    public function testDoesNotAwardExperienceWhenQuickPmParametersDoNotMatchTheLocation(): void
    {
        $sender = $this->mock(User::class);
        $sourceUser = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $source = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $sourceLocation = $this->mock(Map::class);
        $targetLocation = $this->mock(Map::class);

        $sender->shouldReceive('getId')->once()->andReturn(101);
        $sourceUser->shouldReceive('getId')->once()->andReturn(101);
        $targetUser->shouldReceive('getId')->once()->andReturn(102);
        $targetUser->shouldReceive('isNpc')->once()->andReturn(false);
        $source->shouldReceive('getUser')->once()->andReturn($sourceUser);
        $target->shouldReceive('getUser')->times(2)->andReturn($targetUser);
        $source->shouldReceive('getLocation')->once()->andReturn($sourceLocation);
        $target->shouldReceive('getLocation')->once()->andReturn($targetLocation);
        $sourceLocation->shouldReceive('getId')->once()->andReturn(42);
        $targetLocation->shouldReceive('getId')->once()->andReturn(43);

        $this->shipRepository->shouldReceive('find')->with(10)->once()->andReturn($source);
        $this->shipRepository->shouldReceive('find')->with(20)->once()->andReturn($target);
        $this->playerRelationDeterminator->shouldReceive('isFriend')->never();
        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->subject->awardExperience(
            $sender,
            102,
            10,
            ShowWriteQuickPm::TYPE_SHIP,
            20,
            ShowWriteQuickPm::TYPE_SHIP
        );
    }
}
