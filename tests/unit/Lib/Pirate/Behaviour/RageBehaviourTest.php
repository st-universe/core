<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Behaviour;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class RageBehaviourTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private $shipRepository;
    /** @var MockInterface|InteractionCheckerInterface */
    private $interactionChecker;
    /** @var MockInterface|FightLibInterface */
    private $fightLib;
    /** @var MockInterface|PrestigeCalculationInterface */
    private $prestigeCalculation;
    /** @var MockInterface|PirateAttackInterface */
    private $pirateAttack;

    /** @var MockInterface|FleetWrapperInterface */
    private $fleetWrapper;

    /** @var MockInterface|FleetWrapperInterface */
    private $fleet;

    /** @var MockInterface|PirateReactionInterface */
    private $pirateReaction;

    private PirateBehaviourInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->interactionChecker = $this->mock(InteractionCheckerInterface::class);
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->prestigeCalculation = $this->mock(PrestigeCalculationInterface::class);
        $this->pirateAttack = $this->mock(PirateAttackInterface::class);

        $this->fleetWrapper = mock(FleetWrapperInterface::class);
        $this->fleet = mock(FleetInterface::class);
        $this->pirateReaction = mock(PirateReactionInterface::class);

        $this->fleetWrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->fleet);

        $this->subject = new RageBehaviour(
            $this->shipRepository,
            $this->interactionChecker,
            $this->fightLib,
            $this->prestigeCalculation,
            $this->pirateAttack,
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
            ->with($ship)
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

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(false);

        $this->subject->action($this->fleetWrapper, $this->pirateReaction, $reactionMetadata, null);
    }

    public function testActionExpectNoActionIfCantAttackTarget(): void
    {
        $reactionMetadata = $this->mock(PirateReactionMetadata::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);

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

        $target->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
            ->andReturn(true);

        $this->fleetWrapper->shouldReceive('getLeadWrapper')
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('get')
            ->once()
            ->andReturn($ship);

        $this->shipRepository->shouldReceive('getPirateTargets')
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);

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

        $target->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
            ->andReturn(true);
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
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);

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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $target->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
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
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);

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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $target->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
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
            ->with($ship)
            ->once()
            ->andReturn([$target]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);

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

        $target2 = $this->mock(ShipInterface::class);

        $target3_1 = $this->mock(ShipInterface::class);
        $target3_2 = $this->mock(ShipInterface::class);
        $targetFleet3 = $this->mock(FleetInterface::class);

        $target4 = $this->mock(ShipInterface::class);

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
            ->with($ship)
            ->once()
            ->andReturn([$target, $target2, $target3_1, $target4]);

        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target)
            ->once()
            ->andReturn(true);
        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target2)
            ->once()
            ->andReturn(true);
        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target3_1)
            ->once()
            ->andReturn(true);
        $this->interactionChecker->shouldReceive('checkPosition')
            ->with($ship, $target4)
            ->once()
            ->andReturn(true);

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

        $target->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
            ->andReturn(false);
        $target2->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
            ->andReturn(false);
        $target3_1->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
            ->andReturn(false);
        $target4->shouldReceive('getUser->isProtectedAgainstPirates')
            ->withNoArgs()
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
