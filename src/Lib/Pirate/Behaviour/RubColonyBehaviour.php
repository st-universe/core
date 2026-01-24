<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\ColonyRepositoryInterface;

class RubColonyBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ColonyRepositoryInterface $colonyRepository,
        private DistanceCalculationInterface $distanceCalculation,
        private PirateNavigationInterface $pirateNavigation,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private CommodityTransferInterface $commodityTransfer,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuRandom $stuRandom,
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
        $leadShip = $leadWrapper->get();

        $targets = $this->colonyRepository->getPirateTargets($leadWrapper);
        if ($targets === []) {
            $this->logger->log('    no colony targets in reach');
            return PirateBehaviourEnum::FLY;
        }

        usort($targets, fn(Colony $a, Colony $b): int =>
        $this->distanceCalculation->spacecraftToColonyDistance($leadShip, $a) - $this->distanceCalculation->spacecraftToColonyDistance($leadShip, $b));

        $closestColony = current($targets);

        if ($this->pirateNavigation->navigateToTarget($pirateFleetBattleParty, $closestColony->getStarsystemMap())) {
            $this->logger->logf(
                '    reached colonyId %d at %s',
                $closestColony->getId(),
                $closestColony->getSectorString()
            );
            $this->rubColony($pirateFleetBattleParty, $closestColony);
        }

        return null;
    }

    private function rubColony(PirateFleetBattleParty $pirateFleetBattleParty, Colony $colony): void
    {
        if ($this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled()) {
            $this->logger->log('    colony has shield on');
            return;
        }

        $pirateUser = $pirateFleetBattleParty->getUser();

        $filteredColonyStorage = array_filter(
            $colony->getStorage()->toArray(),
            fn(Storage $storage): bool => $storage->getCommodity()->isBeamable($colony->getUser(), $pirateUser)
        );

        $allInformations = new InformationWrapper();

        foreach ($pirateFleetBattleParty->getActiveMembers() as $wrapper) {

            if ($filteredColonyStorage === []) {
                $this->logger->log('    no beamable storage on colony');
                return;
            }

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, true);

            $ship = $wrapper->get();
            $randomCommodityId = (int)$this->stuRandom->array_rand($filteredColonyStorage);

            $informations = new InformationWrapper();

            $hasStolen = $this->commodityTransfer->transferCommodity(
                $randomCommodityId,
                $this->stuRandom->rand(1, $wrapper->get()->getMaxStorage()),
                $wrapper,
                $colony,
                $wrapper->get(),
                $informations
            );

            if ($hasStolen) {
                $informations->addInformationArray([sprintf(
                    _('Die %s hat folgende Waren von der Kolonie %s gestohlen'),
                    $ship->getName(),
                    $colony->getName()
                )], true);

                $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, true);
            }

            $allInformations->addInformationWrapper($informations);
        }

        $this->privateMessageSender->send(
            $pirateUser->getId(),
            $colony->getUser()->getId(),
            $allInformations,
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            sprintf(
                '%s?%s=1&id=%d',
                ModuleEnum::COLONY->getPhpPage(),
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            )
        );
    }
}
