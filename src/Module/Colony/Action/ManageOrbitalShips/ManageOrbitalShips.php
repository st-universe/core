<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShips;

use Exception;
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

        $ships = request::postArray('ships');
        if (count($ships) == 0) {
            $game->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }
        $msg = array();
        $batt = request::postArrayFatal('batt');
        $man = request::postArray('man');
        $unman = request::postArray('unman');
        $reactor = request::postArray('reactor');
        $torp = request::postArray('torp');
        $torp_type = request::postArray('torp_type');
        $storage = $colony->getStorage();

        $sectorString = $colony->getSX() . '|' . $colony->getSY();
        if ($colony->isInSystem()) {
            $sectorString .= ' (' . $colony->getSystem()->getName() . '-System)';
        }

        foreach ($ships as $ship) {
            $shipobj = $this->shipRepository->find((int) $ship);
            if ($shipobj === null) {
                continue;
            }
            if ($shipobj->getCloakState()) {
                continue;
            }
            if (!$this->positionChecker->checkColonyPosition($colony, $shipobj)) {
                continue;
            }
            if ($shipobj->getIsDestroyed()) {
                continue;
            }
            if ($colony->getEps() > 0 && $shipobj->getEBatt() < $shipobj->getMaxEbatt() && array_key_exists(
                $ship,
                $batt
            )) {
                if ($batt[$ship] == 'm') {
                    $load = $shipobj->getMaxEbatt() - $shipobj->getEBatt();
                } else {
                    $load = (int) $batt[$ship];
                    if ($shipobj->getEBatt() + $load > $shipobj->getMaxEBatt()) {
                        $load = $shipobj->getMaxEBatt() - $shipobj->getEBatt();
                    }
                }
                if ($load > $colony->getEps()) {
                    $load = $colony->getEps();
                }
                if ($load > 0) {
                    $shipobj->setEBatt($shipobj->getEBatt() + $load);
                    $colony->lowerEps($load);
                    $msg[] = sprintf(
                        _('%s: Batterie um %d Einheiten aufgeladen'),
                        $shipobj->getName(),
                        $load
                    );
                    if ($shipobj->getUser() !== $user) {

                        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $shipobj->getId());

                        $this->privateMessageSender->send(
                            $userId,
                            (int) $shipobj->getUser()->getId(),
                            sprintf(
                                _('Die Kolonie %s lädt in Sektor %s die Batterie der %s um %s Einheiten'),
                                $colony->getName(),
                                $sectorString,
                                $shipobj->getName(),
                                $load
                            ),
                            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                            $href
                        );
                    }
                }
            }
            if (
                isset($man[$shipobj->getId()])
                && $shipobj->getCrewCount() == 0
                && $shipobj->getBuildplan()->getCrew() > 0
                && $shipobj->getUser()->getId() == $userId
            ) {
                if ($shipobj->getBuildplan()->getCrew() > $colony->getCrewAssignmentAmount()) {
                    $msg[] = sprintf(
                        _('%s: Nicht genügend Crew auf der Kolonie vorhanden (%d benötigt)'),
                        $shipobj->getName(),
                        $shipobj->getBuildplan()->getCrew()
                    );
                } else {
                    $this->crewCreator->createShipCrew($shipobj, $colony);
                    $msg[] = sprintf(
                        _('%s: Die Crew wurde hochgebeamt'),
                        $shipobj->getName()
                    );

                    if ($shipobj->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
                        $this->shipSystemManager->activate($shipobj, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
                    }
                }
            }
            if (
                isset($unman[$shipobj->getId()]) && $shipobj->getUser()->getId() == $userId && $shipobj->getCrewCount() > 0
            ) {
                //assign to colony
                foreach ($shipobj->getCrewlist() as $crewAssignment) {
                    $crewAssignment->setColony($colony);
                    $crewAssignment->setShip(null);
                    $crewAssignment->setSlot(null);
                    $this->shipCrewRepository->save($crewAssignment);
                }
                $shipobj->getCrewlist()->clear();
                $msg[] = sprintf(
                    _('%s: Die Crew wurde runtergebeamt'),
                    $shipobj->getName()
                );

                foreach ($shipobj->getDockedShips() as $dockedShip) {
                    $dockedShip->setDockedTo(null);
                    $this->shipRepository->save($dockedShip);
                }
                $shipobj->getDockedShips()->clear();

                $this->shipSystemManager->deactivateAll($shipobj);

                $shipobj->setAlertStateGreen();
            }
            if (isset($reactor[$shipobj->getId()]) && $reactor[$shipobj->getId()] > 0) {
                $hasWarpcore = $shipobj->hasWarpcore();
                $hasFusionReactor = $shipobj->hasFusionReactor();

                if (!$hasWarpcore && !$hasFusionReactor) {
                    throw new Exception();
                }

                if ($this->reactorUtil->storageContainsNeededCommodities($storage, $hasWarpcore)) {
                    $load = $reactor[$shipobj->getId()] == 'm' ? PHP_INT_MAX : (int)$reactor[$shipobj->getId()];
                    $loadMessage = $this->reactorUtil->loadReactor($shipobj, $load, $colony, null, $hasWarpcore);

                    if ($loadMessage !== null) {
                        $msg[] = $loadMessage;
                    }
                } else {
                    $msg[] = sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des %s benötigt:'),
                        $shipobj->getName(),
                        $hasWarpcore ? 'Warpkerns' : 'Fusionsreaktors'
                    );
                    $costs = $hasWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
                    foreach ($costs as $commodityId => $loadCost) {
                        $msg[] = sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId));
                    }
                }
            }
            if (isset($torp[$shipobj->getId()]) && $shipobj->getMaxTorpedos() > 0) {
                if ($torp[$shipobj->getId()] == 'm') {
                    $count = $shipobj->getMaxTorpedos();
                } else {
                    $count = (int) $torp[$shipobj->getId()];
                }
                try {
                    if ($count < 0) {
                        throw new Exception();
                    }
                    if ($count == $shipobj->getTorpedoCount()) {
                        throw new Exception();
                    }
                    if ($shipobj->getUser() !== $user && $count <= $shipobj->getTorpedoCount()) {
                        throw new Exception();
                    }

                    if ($shipobj->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
                        $possibleTorpedoTypes = $this->torpedoTypeRepository->getAll();
                    } else {
                        $possibleTorpedoTypes = $this->torpedoTypeRepository->getByLevel((int) $shipobj->getRump()->getTorpedoLevel());
                    }

                    if (
                        $shipobj->getTorpedoCount() == 0 && (!isset($torp_type[$shipobj->getId()]) ||
                            !array_key_exists($torp_type[$shipobj->getId()], $possibleTorpedoTypes))
                    ) {
                        throw new Exception();
                    }
                    if ($count > $shipobj->getMaxTorpedos()) {
                        $count = $shipobj->getMaxTorpedos();
                    }
                    if ($shipobj->getTorpedoCount() > 0) {
                        $torp_obj = $possibleTorpedoTypes[$shipobj->getTorpedo()->getId()];
                        $load = $count - $shipobj->getTorpedoCount();
                        if ($load > 0) {
                            if ($torp_obj === null) {
                                $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
                                $this->loggerUtil->log(sprintf('shipId: %d', $shipobj->getId()));
                                $possTorpTypeIds = [];
                                foreach ($possibleTorpedoTypes as $torpType) {
                                    $possTorpTypeIds[] = $torpType->getId();
                                }
                                $this->loggerUtil->log(sprintf('possibleTorpedoTypes: %s', implode(',', $possTorpTypeIds)));
                                $this->loggerUtil->log(sprintf('shipTorpedoCount: %d', $shipobj->getTorpedoCount()));
                                $this->loggerUtil->log(sprintf('shipTorpedoId: %d', $shipobj->getTorpedo()->getId()));
                                $this->loggerUtil->log(sprintf('load: %d', $load));
                            }
                            if (!$storage->containsKey($torp_obj->getCommodityId())) {
                                $msg[] = sprintf(
                                    _('%s: Es sind keine Torpedos des Typs %s auf der Kolonie vorhanden'),
                                    $shipobj->getName(),
                                    $torp_obj->getName()
                                );
                                throw new Exception();
                            }
                            if ($load > $storage[$torp_obj->getCommodityId()]->getAmount()) {
                                $load = $storage[$torp_obj->getCommodityId()]->getAmount();
                            }
                        }
                        $this->shipTorpedoManager->changeTorpedo($shipobj, $load);
                        if ($load < 0) {
                            $this->colonyStorageManager->upperStorage($colony, $torp_obj->getCommodity(), abs($load));
                            $torpName = $torp_obj->getName();

                            $msg[] = sprintf(
                                _('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
                                $shipobj->getName(),
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
                                $shipobj->getName(),
                                $load,
                                $torp_obj->getName()
                            );
                        }
                    } else {
                        $type = (int) $torp_type[$shipobj->getId()];
                        $torp_obj = $this->torpedoTypeRepository->find($type);
                        if (!$storage->containsKey($torp_obj->getCommodityId())) {
                            throw new Exception();
                        }
                        if ($count > $storage[$torp_obj->getCommodityId()]->getAmount()) {
                            $count = $storage[$torp_obj->getCommodityId()]->getAmount();
                        }
                        if ($count > $shipobj->getMaxTorpedos()) {
                            $count = $shipobj->getMaxTorpedos();
                        }
                        $this->shipTorpedoManager->changeTorpedo($shipobj, $count, $torp_obj);

                        $this->colonyStorageManager->lowerStorage(
                            $colony,
                            $this->commodityRepository->find($torp_obj->getCommodityId()),
                            $count
                        );

                        $msg[] = sprintf(
                            _('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
                            $shipobj->getName(),
                            $count,
                            $torp_obj->getName()
                        );
                    }
                } catch (Exception $e) {
                    // nothing
                }
            }

            $this->shipRepository->save($shipobj);
        }
        $this->colonyRepository->save($colony);

        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
