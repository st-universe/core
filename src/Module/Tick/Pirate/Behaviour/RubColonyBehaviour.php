<?php

namespace Stu\Module\Tick\Pirate\Behaviour;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tick\Pirate\Component\PirateFlightInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

class RubColonyBehaviour implements PirateBehaviourInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private DistanceCalculationInterface $distanceCalculation;

    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private RandomSystemEntryInterface $randomSystemEntry;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipSystemManagerInterface $shipSystemManager;

    private BeamUtilInterface $beamUtil;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        DistanceCalculationInterface $distanceCalculation,
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        RandomSystemEntryInterface $randomSystemEntry,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipSystemManagerInterface $shipSystemManager,
        BeamUtilInterface $beamUtil,
        PrivateMessageSenderInterface $privateMessageSender,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->distanceCalculation = $distanceCalculation;
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->randomSystemEntry = $randomSystemEntry;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipSystemManager = $shipSystemManager;
        $this->beamUtil = $beamUtil;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function action(FleetWrapperInterface $fleet): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->colonyRepository->getPirateTargets($leadShip);
        if (empty($targets)) {
            $this->logger->log('    no colony targets in reach');
            return;
        }

        usort($targets, fn (ColonyInterface $a, ColonyInterface $b) =>
        $this->distanceCalculation->shipToColonyDistance($leadShip, $a) - $this->distanceCalculation->shipToColonyDistance($leadShip, $b));

        $closestColony = current($targets);

        $colonySystem = $closestColony->getSystem();

        // move to system
        if (
            $leadShip->getSystem() === null
            && $leadShip->isOverSystem() !== $colonySystem
        ) {
            $this->navigateToSystem($leadWrapper, $colonySystem);
        }

        // reached system?
        if (
            $leadShip->isOverSystem() !== $colonySystem
            && $leadShip->getSystem() !== $colonySystem
        ) {
            $this->logger->log('    did not reach system');
            return;
        }

        // enter system ?
        if ($leadShip->isOverSystem() === $colonySystem) {
            $this->logger->log('    try to enter system');
            $this->enterSystem($leadWrapper, $colonySystem);
        }

        // entered system ?
        $systemMap = $leadShip->getStarsystemMap();
        if ($systemMap === null || $systemMap->getSystem() !== $colonySystem) {
            $this->logger->log('    did not enter system');
            return;
        }

        // move to colony and rub
        if ($systemMap->getColony() !== $closestColony) {
            if (!$this->navigateToColony($leadWrapper, $closestColony)) {
                $this->logger->log('    did not reach colony');
                return;
            }
        }

        $this->rubColony($fleet, $closestColony);
    }

    private function navigateToSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): void
    {
        $this->navigateToTarget($wrapper, $system->getMapField());
    }

    private function navigateToTarget(ShipWrapperInterface $wrapper, MapInterface|StarSystemMapInterface|null $target): void
    {
        if ($target === null) {
            return;
        }

        $ship = $wrapper->get();

        $this->logger->log(sprintf('    navigateToTarget: %s', $target->getSectorString()));

        while ($ship->getCurrentMapField() !== $target) {

            $lastPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('    currentPosition: %s', $lastPosition->getSectorString()));

            $xDistance = $target->getX() - $lastPosition->getX();
            $yDistance = $target->getY() - $lastPosition->getY();

            $isInXDirection = $this->moveInXDirection($xDistance, $yDistance);

            $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
                $ship,
                $this->getTargetX($isInXDirection, $lastPosition->getX(), $xDistance),
                $this->getTargetY($isInXDirection, $lastPosition->getY(), $yDistance)
            );

            $this->pirateFlight->movePirate($wrapper, $flightRoute);

            $newPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('    newPosition: %s', $newPosition->getSectorString()));

            if ($newPosition === $lastPosition) {
                return;
            }
        }
    }

    private function getTargetX(bool $isInXDirection, int $currentX, int $xDistance): int
    {
        if (!$isInXDirection) {
            return $currentX;
        }

        $this->logger->log(sprintf('    getTargetX with isInXDirection: %b, currentX: %d, xDistance: %d', $isInXDirection, $currentX, $xDistance));

        return $currentX + $this->stuRandom->rand(
            $xDistance > 0 ? 1 : $xDistance,
            $xDistance > 0 ? $xDistance : -1
        );
    }

    private function getTargetY(bool $isInXDirection, int $currentY, int $yDistance): int
    {
        if ($isInXDirection) {
            return $currentY;
        }

        $this->logger->log(sprintf('    getTargetY with isInXDirection: %b, currentY: %d, yDistance: %d', $isInXDirection, $currentY, $yDistance));

        return $currentY + $this->stuRandom->rand(
            $yDistance > 0 ? 1 : $yDistance,
            $yDistance > 0 ? $yDistance : -1
        );
    }

    private function moveInXDirection(int $xDistance, int $yDistance): bool
    {
        if ($yDistance === 0) {
            return true;
        }

        if ($xDistance === 0) {
            return false;
        }

        $this->logger->log(sprintf('    moveInXDirection with xDistance: %d, yDistance: %d', $xDistance, $yDistance));

        return $this->stuRandom->rand(1, abs($xDistance) + abs($yDistance)) <= abs($xDistance);
    }

    private function enterSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): void
    {
        $destination = $this->randomSystemEntry->getRandomEntryPoint($wrapper->get(), $system);

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $destination
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);
    }

    private function navigateToColony(ShipWrapperInterface $wrapper, ColonyInterface $colony): bool
    {
        $this->navigateToTarget($wrapper, $colony->getStarsystemMap());

        return $wrapper->get()->getCurrentMapField() === $colony->getStarsystemMap();
    }

    private function rubColony(FleetWrapperInterface $fleetWrapper, ColonyInterface $colony): void
    {
        if ($this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled()) {
            $this->logger->log('    colony has shield on');
            return;
        }

        $pirateUser = $fleetWrapper->get()->getUser();

        $filteredColonyStorage = array_filter(
            $colony->getStorage()->toArray(),
            fn (StorageInterface $storage) => $storage->getCommodity()->isBeamable($colony->getUser(), $pirateUser)
        );

        $informations = new InformationWrapper();

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

            if (empty($filteredColonyStorage)) {
                $this->logger->log('    no beamable storage on colony');
                return;
            }

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS, true);

            $ship = $wrapper->get();
            $randomCommodityId = array_rand($filteredColonyStorage);

            $informations->addInformation(sprintf(
                _('Die %s hat folgende Waren von der Kolonie %s gestohlen'),
                $ship->getName(),
                $colony->getName()
            ));

            $this->beamUtil->transferCommodity(
                $randomCommodityId,
                $this->stuRandom->rand(1, $wrapper->get()->getMaxStorage()),
                $wrapper,
                $colony,
                $wrapper->get(),
                $informations
            );
        }

        $this->privateMessageSender->send(
            $pirateUser->getId(),
            $colony->getUser()->getId(),
            $informations->getInformationsAsString(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(
                'colony.php?%s=1&id=%d',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            )
        );
    }
}
