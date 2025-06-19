<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Behaviour;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\Component\PirateProtectionInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class RageBehaviourTest extends StuTestCase
{
    private MockInterface&ShipRepositoryInterface $shipRepository;
    private MockInterface&FightLibInterface $fightLib;
    private MockInterface&PrestigeCalculationInterface $prestigeCalculation;
    private MockInterface&PirateAttackInterface $pirateAttack;
    private MockInterface&PirateProtectionInterface $pirateProtection;

    /** @var MockInterface&FleetWrapperInterface */
    private $fleetWrapper;

    /** @var MockInterface&FleetWrapperInterface */
    private $fleet;

    /** @var MockInterface&PirateReactionInterface */
    private $pirateReaction;

    private PirateBehaviourInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->prestigeCalculation = $this->mock(PrestigeCalculationInterface::class);
        $this->pirateAttack = $this->mock(PirateAttackInterface::class);
        $this->pirateProtection = $this->mock(PirateProtectionInterface::class);

        $this->fleetWrapper = mock(FleetWrapperInterface::class);
        $this->fleet = mock(FleetInterface::class);
        $this->pirateReaction = mock(PirateReactionInterface::class);

        $this->fleetWrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->fleet);

        $this->subject = new RageBehaviour(
            $this->shipRepository,
            $this->fightLib,
            $this->prestigeCalculation,
            $this->pirateAttack,
            $this->pirateProtection,
            $this->initLoggerUtil()
        );
    }

    public function testActionExpectNoActionIfNoTargets(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([]);

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectNoActionIfTargetNotOnPosition(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($this->mock(MapInterface::class));

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectNoActionIfCantAttackTarget(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(false);

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectNoActionIfProtectedAgainstPirates(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);

        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user)
            ->andReturn(true);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(true);

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectNoActionIfTargetDontHasPositivePrestige(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user)
            ->andReturn(true);

        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $target->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->andReturn(0);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(true);

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectAttackOfSingleTarget(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user)
            ->andReturn(false);
        $target->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->andReturn(1);

        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target)
            ->andReturn(75);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(true);

        $this->prestigeCalculation->shouldReceive('targetHasPositivePrestige')
            ->with($target)
            ->once()
            ->andReturn(true);

        $this->pirateAttack->shouldReceive('attackShip')
            ->with($this->fleetWrapper, $target)
            ->once();

        $this->pirateReaction->shouldReceive('react')
            ->with($this->fleet, PirateReactionTriggerEnum::ON_RAGE, $ship, $reactionMetadata)
            ->once();

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectAttackOfTriggerTarget(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $location = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user)
            ->andReturn(false);
        $target->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->andReturn(0);

        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target)
            ->andReturn(75);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target]);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(true);

        $this->pirateAttack->shouldReceive('attackShip')
            ->with($this->fleetWrapper, $target)
            ->once();

        $this->pirateReaction->shouldReceive('react')
            ->with($this->fleet, PirateReactionTriggerEnum::ON_RAGE, $ship, $reactionMetadata)
            ->once();

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, $target);
    }

    public function testActionExpectAttackOfWeakestTarget(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $target2 = $this->mock(ShipInterface::class);
        $user2 = $this->mock(UserInterface::class);

        $target3_1 = $this->mock(ShipInterface::class);
        $user3_1 = $this->mock(UserInterface::class);
        $target3_2 = $this->mock(ShipInterface::class);
        $targetFleet3 = $this->mock(FleetInterface::class);

        $target4 = $this->mock(ShipInterface::class);
        $user4 = $this->mock(UserInterface::class);

        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target)
            ->andReturn(75);
        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target2)
            ->andReturn(73);
        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target3_1)
            ->andReturn(73);
        $this->fightLib->shouldReceive('calculateHealthPercentage')
            ->with($target3_2)
            ->andReturn(75);

        $target->shouldReceive('getFleet')
            ->andReturn(null);
        $target2->shouldReceive('getFleet')
            ->andReturn(null);
        $target3_1->shouldReceive('getFleet')
            ->andReturn($targetFleet3);
        $targetFleet3->shouldReceive('getShips')
            ->andReturn(new ArrayCollection([$target3_1, $target3_2]));
        $target4->shouldReceive('getFleet')
            ->andReturn(null);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $target2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);
        $target3_1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(44);

        $this->prestigeCalculation->shouldReceive('targetHasPositivePrestige')
            ->with($target)
            ->andReturn(true);
        $this->prestigeCalculation->shouldReceive('targetHasPositivePrestige')
            ->with($target2)
            ->andReturn(true);
        $this->prestigeCalculation->shouldReceive('targetHasPositivePrestige')
            ->with($target3_1)
            ->andReturn(true);
        $this->prestigeCalculation->shouldReceive('targetHasPositivePrestige')
            ->with($target4)
            ->andReturn(false);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($wrapper)
            ->once()
            ->andReturn([$target, $target2, $target3_1, $target4]);

        $location = $this->mock(MapInterface::class);
        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target2->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target3_1->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);
        $target4->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target, true, false, false)
            ->once()
            ->andReturn(true);
        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target2, true, false, false)
            ->once()
            ->andReturn(true);
        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target3_1, true, false, false)
            ->once()
            ->andReturn(true);
        $this->fightLib->shouldReceive('canAttackTarget')
            ->with($ship, $target4, true, false, false)
            ->once()
            ->andReturn(true);

        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $target2->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);
        $target3_1->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user3_1);
        $target4->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user4);

        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user)
            ->andReturn(false);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user2)
            ->andReturn(false);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user3_1)
            ->andReturn(false);
        $this->pirateProtection->shouldReceive('isProtectedAgainstPirates')
            ->with($user4)
            ->andReturn(false);

        $this->pirateAttack->shouldReceive('attackShip')
            ->with($this->fleetWrapper, $target2)
            ->once();

        $this->pirateReaction->shouldReceive('react')
            ->with($this->fleet, PirateReactionTriggerEnum::ON_RAGE, $ship, $reactionMetadata)
            ->once();

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }
}
