<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

class RubColonyBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ColonyRepositoryInterface $colonyRepository,
        private DistanceCalculationInterface $distanceCalculation,
        private PirateNavigationInterface $pirateNavigation,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ShipSystemManagerInterface $shipSystemManager,
        private BeamUtilInterface $beamUtil,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->distanceCalculation = $distanceCalculation;
        $this->pirateNavigation = $pirateNavigation;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipSystemManager = $shipSystemManager;
        $this->beamUtil = $beamUtil;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->colonyRepository->getPirateTargets($leadShip);
        if (empty($targets)) {
            $this->logger->log('    no colony targets in reach');
            return PirateBehaviourEnum::FLY;
        }

        usort($targets, fn (ColonyInterface $a, ColonyInterface $b) =>
        $this->distanceCalculation->shipToColonyDistance($leadShip, $a) - $this->distanceCalculation->shipToColonyDistance($leadShip, $b));

        $closestColony = current($targets);

        if ($this->pirateNavigation->navigateToTarget($fleet, $closestColony->getStarsystemMap())) {
            $this->rubColony($fleet, $closestColony);
        }

        return null;
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

        $allInformations = new InformationWrapper();

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

            if (empty($filteredColonyStorage)) {
                $this->logger->log('    no beamable storage on colony');
                return;
            }

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS, true);

            $ship = $wrapper->get();
            $randomCommodityId = array_rand($filteredColonyStorage);

            $informations = new InformationWrapper();

            $this->beamUtil->transferCommodity(
                $randomCommodityId,
                $this->stuRandom->rand(1, $wrapper->get()->getMaxStorage()),
                $wrapper,
                $colony,
                $wrapper->get(),
                $informations
            );

            if (!$informations->isEmpty()) {
                $informations->addInformationArray([sprintf(
                    _('Die %s hat folgende Waren von der Kolonie %s gestohlen'),
                    $ship->getName(),
                    $colony->getName()
                )], true);
            }

            $allInformations->addInformationWrapper($informations);
        }

        $this->privateMessageSender->send(
            $pirateUser->getId(),
            $colony->getUser()->getId(),
            $allInformations,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(
                'colony.php?%s=1&id=%d',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            )
        );
    }
}
