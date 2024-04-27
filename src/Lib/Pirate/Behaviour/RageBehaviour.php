<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCoreInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class RageBehaviour implements PirateBehaviourInterface
{
    private ShipRepositoryInterface $shipRepository;

    private InteractionCheckerInterface $interactionChecker;

    private FightLibInterface $fightLib;

    private ShipAttackCoreInterface $shipAttackCore;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PirateLoggerInterface $logger;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        InteractionCheckerInterface $interactionChecker,
        FightLibInterface $fightLib,
        ShipAttackCoreInterface $shipAttackCore,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->interactionChecker = $interactionChecker;
        $this->fightLib = $fightLib;
        $this->shipAttackCore = $shipAttackCore;
        $this->shipWrapperFactory = $shipWrapperFactory;

        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->shipRepository->getPirateTargets($leadShip);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn (ShipInterface $target) =>
            $this->interactionChecker->checkPosition($leadShip, $target)
                && $this->fightLib->canAttackTarget($leadShip, $target, false)
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if (empty($filteredTargets)) {
            return PirateBehaviourEnum::SEARCH_FRIEND;
        }

        foreach ($filteredTargets as $ship) {
            $this->logger->log(sprintf('      shipId with %F health', $this->fightLib->calculateHealthPercentage($ship)));
        }

        usort(
            $filteredTargets,
            fn (ShipInterface $a, ShipInterface $b) =>
            $this->fightLib->calculateHealthPercentage($a) -  $this->fightLib->calculateHealthPercentage($b)
        );

        $weakestTarget = current($filteredTargets);

        $this->logger->logf('    attacking weakestTarget with shipId: %d', $weakestTarget->getId());

        $this->attackShip($fleet, $weakestTarget);

        $pirateReaction->react(
            $fleet->get(),
            PirateReactionTriggerEnum::ON_RAGE
        );

        return null;
    }

    private function attackShip(FleetWrapperInterface $fleetWrapper, ShipInterface $target): void
    {
        $leadWrapper = $fleetWrapper->getLeadWrapper();
        $isFleetFight = false;
        $informations = new InformationWrapper();

        $this->shipAttackCore->attack(
            $leadWrapper,
            $this->shipWrapperFactory->wrapShip($target),
            $isFleetFight,
            $informations
        );
    }
}
