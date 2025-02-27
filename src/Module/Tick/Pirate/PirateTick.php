<?php

namespace Stu\Module\Tick\Pirate;

use Override;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;

final class PirateTick implements PirateTickInterface
{
    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private PirateCreationInterface $pirateCreation,
        private PirateReactionInterface $pirateReaction,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
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

            $fleetWrapper = $this->spacecraftWrapperFactory->wrapFleet($fleet);

            $this->behaviours[$behaviourType->value]->action(
                $fleetWrapper,
                $this->pirateReaction,
                new PirateReactionMetadata(),
                null
            );

            $this->reloadMinimalEps->reload($fleetWrapper);
        }
    }

    private function getRandomBehaviourType(): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomKeyOfProbabilities(PirateBehaviourEnum::getBehaviourProbabilities());

        return PirateBehaviourEnum::from($value);
    }
}
