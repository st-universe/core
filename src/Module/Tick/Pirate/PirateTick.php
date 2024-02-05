<?php

namespace Stu\Module\Tick\Pirate;

use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;

final class PirateTick implements PirateTickInterface
{
    private const BEHAVIOUR_PROBABILITIES = [
        PirateBehaviourEnum::DO_NOTHING->value => 30,
        PirateBehaviourEnum::FLY->value => 40,
        PirateBehaviourEnum::RUB_COLONY->value => 5,
        PirateBehaviourEnum::ATTACK_SHIP->value => 5,
        PirateBehaviourEnum::HIDE->value => 20
    ];

    private PirateCreationInterface $pirateCreation;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    /** @var array<int, PirateBehaviourInterface> */
    private array $behaviours;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        PirateCreationInterface $pirateCreation,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        array $behaviours
    ) {
        $this->pirateCreation = $pirateCreation;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->stuRandom = $stuRandom;
        $this->behaviours = $behaviours;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function work(): void
    {
        // create new pirates (max 5 fleets)
        $pirateFleets = $this->pirateCreation->createPirateFleetsIfNeeded();

        // process pirate fleets
        foreach ($pirateFleets as $fleet) {
            $behaviourType = $this->getRandomBehaviourType();

            if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
                continue;
            }

            $this->logger->log(sprintf('pirateFleetId %d does %s', $fleet->getId(), $behaviourType->getDescription()));

            $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

            $this->behaviours[$behaviourType->value]->action($fleetWrapper);

            $this->reloadMinimalEps($fleetWrapper);
        }
    }

    private function reloadMinimalEps(FleetWrapperInterface $fleetWrapper): void
    {
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $epsSystem = $wrapper->getEpsSystemData();

            if (
                $epsSystem !== null
                && $epsSystem->getEpsPercentage() < 20
            ) {
                $epsSystem->setEps((int)($epsSystem->getMaxEps() * 0.2))->update();
            }
        }
    }

    private function getRandomBehaviourType(): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomOfProbabilities(self::BEHAVIOUR_PROBABILITIES);

        return PirateBehaviourEnum::from($value);
    }
}
