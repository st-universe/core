<?php

namespace Stu\Module\Tick\Pirate;

use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;

class PirateTick implements PirateTickInterface
{
    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private PirateCreationInterface $pirateCreation,
        private PirateReactionInterface $pirateReaction,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function work(): void
    {
        $this->logger->log('PIRATE TICK:');

        // create new pirates (max 5 fleets)
        $pirateFleets = $this->pirateCreation->createPirateFleetsIfNeeded();

        // process pirate fleets
        foreach ($pirateFleets as $fleet) {
            $behaviourType = $this->getRandomBehaviourType();

            if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
                continue;
            }

            $this->logger->log(sprintf('pirateFleetId %d does %s', $fleet->getId(), $behaviourType->name));

            $pirateFleetBattleParty = $this->battlePartyFactory->createPirateFleetBattleParty($fleet);

            $this->behaviours[$behaviourType->value]->action(
                $pirateFleetBattleParty,
                $this->pirateReaction,
                new PirateReactionMetadata(),
                null
            );

            $this->reloadMinimalEps->reload($pirateFleetBattleParty);
        }
    }

    private function getRandomBehaviourType(): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomKeyOfProbabilities(PirateBehaviourEnum::getBehaviourProbabilities());

        return PirateBehaviourEnum::from($value);
    }
}
