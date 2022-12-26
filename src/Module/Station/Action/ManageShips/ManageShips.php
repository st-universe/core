<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShips;

use Exception;
use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipTorpedoManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ManageShips implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_STATION_SHIPS';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private CrewCreatorInterface $crewCreator;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipStorageManagerInterface $shipStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private InteractionCheckerInterface $interactionChecker;

    private ReactorUtilInterface $reactorUtil;

    private StationUtilityInterface $stationUtility;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        CrewCreatorInterface $crewCreator,
        ShipCrewRepositoryInterface $shipCrewRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipStorageManagerInterface $shipStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        ShipSystemManagerInterface $shipSystemManager,
        InteractionCheckerInterface $interactionChecker,
        ReactorUtilInterface $reactorUtil,
        StationUtilityInterface $stationUtility,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->crewCreator = $crewCreator;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipStorageManager = $shipStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->interactionChecker = $interactionChecker;
        $this->reactorUtil = $reactorUtil;
        $this->stationUtility = $stationUtility;
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$this->stationUtility->canManageShips($station)) {
            return;
        }

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
        $storage = $station->getStorage();

        $sectorString = $station->getSectorString();

        $stationEps = $this->shipWrapperFactory->wrapShip($station)->getEpsSystemData();

        foreach ($ships as $ship) {
            $shipobj = $this->shipRepository->find((int) $ship);
            if ($shipobj === null) {
                continue;
            }
            if ($shipobj->getCloakState()) {
                continue;
            }
            if (!$this->interactionChecker->checkPosition($station, $shipobj)) {
                continue;
            }
            if ($shipobj->getIsDestroyed()) {
                continue;
            }


            $wrapper = $this->shipWrapperFactory->wrapShip($shipobj);
            $shipEps = $wrapper->getEpsSystemData();

            if ($stationEps->getEps() > 0 && $shipEps->getBattery() < $shipEps->getMaxBattery() && array_key_exists(
                $ship,
                $batt
            )) {
                if ($batt[$ship] == 'm') {
                    $load = $shipEps->getMaxBattery() - $shipEps->getBattery();
                } else {
                    $load = (int) $batt[$ship];
                    if ($shipEps->getBattery() + $load > $shipEps->getMaxBattery()) {
                        $load = $shipEps->getMaxBattery() - $shipEps->getBattery();
                    }
                }
                if ($load > $stationEps->getEps()) {
                    $load = $stationEps->getEps();
                }
                if ($load > 0) {
                    $shipEps->setBattery($shipEps->getBattery() + $load)->update();
                    $stationEps->setEps($stationEps->getEps() - $load)->update();
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
                                _('Die %s %s lädt in Sektor %s die Batterie der %s um %s Einheiten'),
                                $station->getRump()->getName(),
                                $station->getName(),
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
                if ($shipobj->getBuildplan()->getCrew() > $station->getExcessCrewCount()) {
                    $msg[] = sprintf(
                        _('%s: Nicht genügend überschüssige Crew auf Station vorhanden (%d benötigt)'),
                        $shipobj->getName(),
                        $shipobj->getBuildplan()->getCrew()
                    );
                } else {
                    $this->crewCreator->createShipCrew($shipobj, null, $station);
                    $msg[] = sprintf(
                        _('%s: Die Crew wurde hochgebeamt'),
                        $shipobj->getName()
                    );

                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
                }
            }
            if (
                isset($unman[$shipobj->getId()]) && $shipobj->getUser()->getId() == $userId && $shipobj->getCrewCount() > 0
            ) {
                if ($shipobj->getCrewCount() > $station->getMaxCrewCount() - $station->getCrewCount()) {
                    $msg[] = sprintf(
                        _('%s: Nicht genügend freie Quartiere auf Station vorhanden (%d benötigt)'),
                        $shipobj->getName(),
                        $shipobj->getCrewCount()
                    );
                } else {
                    //assign to station
                    foreach ($shipobj->getCrewlist() as $shipCrew) {
                        $shipCrew->setShip($station);
                        $shipCrew->setSlot(null);
                        $station->getCrewlist()->add($shipCrew);
                        $this->shipCrewRepository->save($shipCrew);
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

                    $this->shipSystemManager->deactivateAll($wrapper);
                    $shipobj->setAlertStateGreen();
                }
            }
            if (isset($reactor[$shipobj->getId()]) && $reactor[$shipobj->getId()] > 0) {
                $hasWarpcore = $shipobj->hasWarpcore();
                $hasFusionReactor = $shipobj->hasFusionReactor();

                if (!$hasWarpcore && !$hasFusionReactor) {
                    throw new Exception();
                }

                if ($this->reactorUtil->storageContainsNeededCommodities($storage, $hasWarpcore)) {
                    $load = $reactor[$shipobj->getId()] == 'm' ? PHP_INT_MAX : (int)$reactor[$shipobj->getId()];
                    $loadMessage = $this->reactorUtil->loadReactor($shipobj, $load, null, $station, $hasWarpcore);

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
                        throw new Exception;
                    }
                    if ($count == $shipobj->getTorpedoCount()) {
                        throw new Exception;
                    }
                    if ($shipobj->getUser() !== $user && $count <= $shipobj->getTorpedoCount()) {
                        throw new Exception;
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
                        throw new Exception;
                    }
                    if ($count > $shipobj->getMaxTorpedos()) {
                        $count = $shipobj->getMaxTorpedos();
                    }
                    if ($shipobj->getTorpedoCount() > 0) {
                        $torp_obj = $possibleTorpedoTypes[$shipobj->getTorpedo()->getId()];
                        $load = $count - $shipobj->getTorpedoCount();
                        if ($load > 0) {
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
                        $this->shipTorpedoManager->changeTorpedo($wrapper, $load);
                        if ($load < 0) {
                            $this->shipStorageManager->upperStorage($station, $torp_obj->getCommodity(), abs($load));
                            $torpName = $torp_obj->getName();

                            $msg[] = sprintf(
                                _('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
                                $shipobj->getName(),
                                abs($load),
                                $torpName
                            );
                        } elseif ($load > 0) {
                            $this->shipStorageManager->lowerStorage(
                                $station,
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
                            throw new Exception;
                        }
                        if ($count > $storage[$torp_obj->getCommodityId()]->getAmount()) {
                            $count = $storage[$torp_obj->getCommodityId()]->getAmount();
                        }
                        if ($count > $shipobj->getMaxTorpedos()) {
                            $count = $shipobj->getMaxTorpedos();
                        }
                        $this->shipTorpedoManager->changeTorpedo($wrapper, $count, $torp_obj);

                        $this->shipStorageManager->lowerStorage(
                            $station,
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
        $this->shipRepository->save($station);

        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
