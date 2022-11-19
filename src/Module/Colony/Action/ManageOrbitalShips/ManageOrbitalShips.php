<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShips;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

final class ManageOrbitalShips implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_ORBITAL_SHIPS';

    private ColonyLoaderInterface $colonyLoader;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private CrewCreatorInterface $crewCreator;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private PositionCheckerInterface $positionChecker;

    private ReactorUtilInterface $reactorUtil;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        CrewCreatorInterface $crewCreator,
        ShipCrewRepositoryInterface $shipCrewRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyStorageManagerInterface $colonyStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        PositionCheckerInterface $positionChecker,
        ReactorUtilInterface $reactorUtil,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->crewCreator = $crewCreator;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->positionChecker = $positionChecker;
        $this->reactorUtil = $reactorUtil;
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrbitManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipIds = request::postArray('ships');
        if (count($shipIds) == 0) {
            $game->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }
        $msg = [];

        foreach ($shipIds as $shipId) {
            $this->handleShip($game, $shipId, $colony, $msg);
        }
        $this->colonyRepository->save($colony);

        $game->addInformationMerge($msg);
    }

    private function handleShip(
        GameControllerInterface $game,
        int $shipId,
        ColonyInterface $colony,
        &$msg
    ): void {

        $user = $game->getUser();

        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            return;
        }
        if ($ship->getCloakState()) {
            return;
        }
        if (!$this->positionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }
        if ($ship->getIsDestroyed()) {
            return;
        }

        $this->batteryShip($ship, $user, $colony, $msg);
        $this->manShip($ship, $user, $colony, $msg);
        $this->unmanShip($ship, $user, $colony, $msg);
        $this->reactorShip($ship, $colony, $msg);
        $this->torpedoShip($ship, $user, $colony, $msg);

        $this->shipRepository->save($ship);
    }


    private function batteryShip(ShipInterface $ship, UserInterface $user, ColonyInterface $colony, &$msg): void
    {
        $batt = request::postArrayFatal('batt');
        $shipId = $ship->getId();
        $userId = $user->getId();

        $sectorString = $colony->getSX() . '|' . $colony->getSY();
        if ($colony->isInSystem()) {
            $sectorString .= ' (' . $colony->getSystem()->getName() . '-System)';
        }

        if ($colony->getEps() > 0 && $ship->getEBatt() < $ship->getMaxEbatt() && array_key_exists(
            $shipId,
            $batt
        )) {
            if ($batt[$shipId] == 'm') {
                $load = $ship->getMaxEbatt() - $ship->getEBatt();
            } else {
                $load = (int) $batt[$shipId];
                if ($ship->getEBatt() + $load > $ship->getMaxEBatt()) {
                    $load = $ship->getMaxEBatt() - $ship->getEBatt();
                }
            }
            if ($load > $colony->getEps()) {
                $load = $colony->getEps();
            }
            if ($load > 0) {
                $ship->setEBatt($ship->getEBatt() + $load);
                $colony->lowerEps($load);
                $msg[] = sprintf(
                    _('%s: Batterie um %d Einheiten aufgeladen'),
                    $ship->getName(),
                    $load
                );
                if ($ship->getUser() !== $user) {

                    $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());

                    $this->privateMessageSender->send(
                        $userId,
                        $ship->getUser()->getId(),
                        sprintf(
                            _('Die Kolonie %s lädt in Sektor %s die Batterie der %s um %s Einheiten'),
                            $colony->getName(),
                            $sectorString,
                            $ship->getName(),
                            $load
                        ),
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                        $href
                    );
                }
            }
        }
    }

    private function manShip(ShipInterface $ship, UserInterface $user, ColonyInterface $colony, &$msg): void
    {
        $man = request::postArray('man');

        if (
            isset($man[$ship->getId()])
            && $ship->getCrewCount() == 0
            && $ship->getBuildplan()->getCrew() > 0
            && $ship->getUser() === $user
        ) {
            if ($ship->getBuildplan()->getCrew() > $colony->getCrewAssignmentAmount()) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Crew auf der Kolonie vorhanden (%d benötigt)'),
                    $ship->getName(),
                    $ship->getBuildplan()->getCrew()
                );
            } else {
                $this->crewCreator->createShipCrew($ship, $colony);
                $msg[] = sprintf(
                    _('%s: Die Crew wurde hochgebeamt'),
                    $ship->getName()
                );

                if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
                    $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
                }
            }
        }
    }

    private function unmanShip(ShipInterface $ship, UserInterface $user, ColonyInterface $colony, &$msg): void
    {
        $unman = request::postArray('unman');

        if (
            isset($unman[$ship->getId()]) && $ship->getUser() === $user && $ship->getCrewCount() > 0
        ) {
            //check if there is enough space for crew on colony
            if ($ship->getCrewCount() > $colony->getFreeAssignmentCount()) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Platz für die Crew auf der Kolonie'),
                    $ship->getName()
                );
                return;
            }

            //assign to colony
            foreach ($ship->getCrewlist() as $crewAssignment) {
                $crewAssignment->setColony($colony);
                $crewAssignment->setShip(null);
                $crewAssignment->setSlot(null);
                $this->shipCrewRepository->save($crewAssignment);
            }
            $ship->getCrewlist()->clear();
            $msg[] = sprintf(
                _('%s: Die Crew wurde runtergebeamt'),
                $ship->getName()
            );

            foreach ($ship->getDockedShips() as $dockedShip) {
                $dockedShip->setDockedTo(null);
                $this->shipRepository->save($dockedShip);
            }
            $ship->getDockedShips()->clear();

            $this->shipSystemManager->deactivateAll($ship);

            $ship->setAlertStateGreen();
        }
    }

    private function reactorShip(ShipInterface $ship, ColonyInterface $colony, &$msg): void
    {
        $reactor = request::postArray('reactor');
        $storage = $colony->getStorage();

        if (isset($reactor[$ship->getId()]) && $reactor[$ship->getId()] > 0) {
            $hasWarpcore = $ship->hasWarpcore();
            $hasFusionReactor = $ship->hasFusionReactor();

            if (!$hasWarpcore && !$hasFusionReactor) {
                return;
            }

            if ($this->reactorUtil->storageContainsNeededCommodities($storage, $hasWarpcore)) {
                $load = $reactor[$ship->getId()] == 'm' ? PHP_INT_MAX : (int)$reactor[$ship->getId()];
                $loadMessage = $this->reactorUtil->loadReactor($ship, $load, $colony, null, $hasWarpcore);

                if ($loadMessage !== null) {
                    $msg[] = $loadMessage;
                }
            } else {
                $msg[] = sprintf(
                    _('%s: Es werden mindestens folgende Waren zum Aufladen des %s benötigt:'),
                    $ship->getName(),
                    $hasWarpcore ? 'Warpkerns' : 'Fusionsreaktors'
                );
                $costs = $hasWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
                foreach ($costs as $commodityId => $loadCost) {
                    $msg[] = sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId));
                }
            }
        }
    }

    private function torpedoShip(ShipInterface $ship, UserInterface $user, ColonyInterface $colony, &$msg): void
    {
        $torp = request::postArray('torp');
        $torp_type = request::postArray('torp_type');
        $storage = $colony->getStorage();

        if (isset($torp[$ship->getId()]) && $ship->getMaxTorpedos() > 0) {
            if ($torp[$ship->getId()] == 'm') {
                $count = $ship->getMaxTorpedos();
            } else {
                $count = (int) $torp[$ship->getId()];
            }
            if ($count < 0) {
                return;
            }
            if ($count == $ship->getTorpedoCount()) {
                return;
            }
            if ($ship->getUser() !== $user && $count <= $ship->getTorpedoCount()) {
                return;
            }

            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
                $possibleTorpedoTypes = $this->torpedoTypeRepository->getAll();
            } else {
                $possibleTorpedoTypes = $this->torpedoTypeRepository->getByLevel((int) $ship->getRump()->getTorpedoLevel());
            }

            if (
                $ship->getTorpedoCount() == 0 && (!isset($torp_type[$ship->getId()]) ||
                    !array_key_exists($torp_type[$ship->getId()], $possibleTorpedoTypes))
            ) {
                return;
            }
            if ($count > $ship->getMaxTorpedos()) {
                $count = $ship->getMaxTorpedos();
            }
            if ($ship->getTorpedoCount() > 0) {
                $torp_obj = $possibleTorpedoTypes[$ship->getTorpedo()->getId()];
                $load = $count - $ship->getTorpedoCount();
                if ($load > 0) {
                    if ($torp_obj === null) {
                        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
                        $this->loggerUtil->log(sprintf('shipId: %d', $ship->getId()));
                        $possTorpTypeIds = [];
                        foreach ($possibleTorpedoTypes as $torpType) {
                            $possTorpTypeIds[] = $torpType->getId();
                        }
                        $this->loggerUtil->log(sprintf('possibleTorpedoTypes: %s', implode(',', $possTorpTypeIds)));
                        $this->loggerUtil->log(sprintf('shipTorpedoCount: %d', $ship->getTorpedoCount()));
                        $this->loggerUtil->log(sprintf('shipTorpedoId: %d', $ship->getTorpedo()->getId()));
                        $this->loggerUtil->log(sprintf('load: %d', $load));
                    }
                    if (!$storage->containsKey($torp_obj->getCommodityId())) {
                        $msg[] = sprintf(
                            _('%s: Es sind keine Torpedos des Typs %s auf der Kolonie vorhanden'),
                            $ship->getName(),
                            $torp_obj->getName()
                        );
                        return;
                    }
                    if ($load > $storage[$torp_obj->getCommodityId()]->getAmount()) {
                        $load = $storage[$torp_obj->getCommodityId()]->getAmount();
                    }
                }
                $this->shipTorpedoManager->changeTorpedo($ship, $load);
                if ($load < 0) {
                    $this->colonyStorageManager->upperStorage($colony, $torp_obj->getCommodity(), abs($load));
                    $torpName = $torp_obj->getName();

                    $msg[] = sprintf(
                        _('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
                        $ship->getName(),
                        abs($load),
                        $torpName
                    );
                } elseif ($load > 0) {
                    $this->colonyStorageManager->lowerStorage(
                        $colony,
                        $this->commodityRepository->find($torp_obj->getCommodityId()),
                        $load
                    );

                    $msg[] = sprintf(
                        _('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
                        $ship->getName(),
                        $load,
                        $torp_obj->getName()
                    );
                }
            } else {
                $type = (int) $torp_type[$ship->getId()];
                $torp_obj = $this->torpedoTypeRepository->find($type);
                if (!$storage->containsKey($torp_obj->getCommodityId())) {
                    return;
                }
                if ($count > $storage[$torp_obj->getCommodityId()]->getAmount()) {
                    $count = $storage[$torp_obj->getCommodityId()]->getAmount();
                }
                if ($count > $ship->getMaxTorpedos()) {
                    $count = $ship->getMaxTorpedos();
                }
                $this->shipTorpedoManager->changeTorpedo($ship, $count, $torp_obj);

                $this->colonyStorageManager->lowerStorage(
                    $colony,
                    $this->commodityRepository->find($torp_obj->getCommodityId()),
                    $count
                );

                $msg[] = sprintf(
                    _('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
                    $ship->getName(),
                    $count,
                    $torp_obj->getName()
                );
            }
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
