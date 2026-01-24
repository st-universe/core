<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

class HideBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private StarSystemRepositoryInterface $starSystemRepository,
        private PirateNavigationInterface $pirateNavigation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function action(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $leadWrapper = $pirateFleetBattleParty->getLeader();

        $hideSystems = $this->starSystemRepository->getPirateHides($leadWrapper);
        if ($hideSystems === []) {
            $this->logger->log('    no hide system in reach');
            return PirateBehaviourEnum::RAGE;
        }

        shuffle($hideSystems);
        $closestHideSystem = current($hideSystems);

        $this->pirateNavigation->navigateToTarget($pirateFleetBattleParty, $closestHideSystem);

        return null;
    }
}
