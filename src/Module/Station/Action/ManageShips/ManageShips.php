<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShips;

use Exception;
use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
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
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
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

    private PositionCheckerInterface $positionChecker;

    private StationUtilityInterface $stationUtility;

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
        PositionCheckerInterface $positionChecker,
        StationUtilityInterface $stationUtility
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
        $this->positionChecker = $positionChecker;
        $this->stationUtility = $stationUtility;
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
        $wk = request::postArray('wk');
        $torp = request::postArray('torp');
        $torp_type = request::postArray('torp_type');
        $storage = $station->getStorage();

        $sectorString = $station->getSectorString();

        foreach ($ships as $ship) {
            $shipobj = $this->shipRepository->find((int) $ship);
            if ($shipobj === null) {
                continue;
            }
            if ($shipobj->getCloakState()) {
                continue;
            }
            if (!$this->positionChecker->checkPosition($station, $shipobj)) {
                continue;
            }
            if ($shipobj->getIsDestroyed()) {
                continue;
            }
            if ($station->getEps() > 0 && $shipobj->getEBatt() < $shipobj->getMaxEbatt() && array_key_exists(
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
                if ($load > $station->getEps()) {
                    $load = $station->getEps();
                }
                if ($load > 0) {
                    $shipobj->setEBatt($shipobj->getEBatt() + $load);
                    $station->setEps($station->getEps() - $load);
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
                if ($shipobj->getBuildplan()->getCrew() > $shipobj->getUser()->getFreeCrewCount()) {
                    $msg[] = sprintf(
                        _('%s: Nicht genügend Crew vorhanden (%d benötigt)'),
                        $shipobj->getName(),
                        $shipobj->getBuildplan()->getCrew()
                    );
                } else {
                    $this->crewCreator->createShipCrew($shipobj);
                    $shipobj->getUser()->lowerFreeCrewCount($shipobj->getBuildplan()->getCrew());
                    $msg[] = sprintf(
                        _('%s: Die Crew wurde hochgebeamt'),
                        $shipobj->getName()
                    );

                    $this->shipSystemManager->activate($shipobj, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
                }
            }
            if (
                isset($unman[$shipobj->getId()]) && $shipobj->getUser()->getId() == $userId && $shipobj->getCrewCount() > 0
            ) {
                $this->shipCrewRepository->truncateByShip((int) $shipobj->getId());
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
            if (isset($wk[$shipobj->getId()]) && $wk[$shipobj->getId()] > 0) {
                if ($this->storageContainsNeededCommodities($storage)) {
                    if ($shipobj->getWarpcoreLoad() < $shipobj->getWarpcoreCapacity()) {
                        if ($wk[$shipobj->getId()] == 'm') {
                            $load = ceil(($shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) / ShipEnum::WARPCORE_LOAD);
                        } else {
                            $load = ceil(((int) $wk[$shipobj->getId()]) / ShipEnum::WARPCORE_LOAD);
                            if ($load * ShipEnum::WARPCORE_LOAD > $shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) {
                                $load = ceil(($shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) / ShipEnum::WARPCORE_LOAD);
                            }
                        }
                        $load = (int) $load;
                        if ($load >= 1) {
                            foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                                if ($storage[$commodityId]->getAmount() < ($load * $loadCost)) {
                                    $load = (int) ($storage[$commodityId]->getAmount() / $loadCost);
                                }
                            }
                            foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                                $this->shipStorageManager->lowerStorage(
                                    $station,
                                    $this->commodityRepository->find($commodityId),
                                    $loadCost * $load
                                );
                            }
                            if ($shipobj->getWarpcoreLoad() + $load * ShipEnum::WARPCORE_LOAD > $shipobj->getWarpcoreCapacity()) {
                                $load = $shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad();
                            } else {
                                $load = $load * ShipEnum::WARPCORE_LOAD;
                            }
                            $shipobj->setWarpcoreLoad($shipobj->getWarpcoreLoad() + $load);
                            $msg[] = sprintf(
                                _('Der Warpkern der %s wurde um %d Einheiten aufgeladen'),
                                $shipobj->getName(),
                                $load
                            );
                            if ($shipobj->getUser() !== $user) {

                                $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $shipobj->getId());

                                $this->privateMessageSender->send(
                                    $userId,
                                    (int) $shipobj->getUser()->getId(),
                                    sprintf(
                                        _('Die %s %s hat in Sektor %s den Warpkern der %s um %d Einheiten aufgeladen'),
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
                } else {
                    $msg[] = sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:'),
                        $shipobj->getName()
                    );
                    foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
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
                            if (!$storage->containsKey($torp_obj->getGoodId())) {
                                $msg[] = sprintf(
                                    _('%s: Es sind keine Torpedos des Typs %s auf der Kolonie vorhanden'),
                                    $shipobj->getName(),
                                    $torp_obj->getName()
                                );
                                throw new Exception();
                            }
                            if ($load > $storage[$torp_obj->getGoodId()]->getAmount()) {
                                $load = $storage[$torp_obj->getGoodId()]->getAmount();
                            }
                        }
                        $shipobj->setTorpedoCount($shipobj->getTorpedoCount() + $load);
                        if ($load < 0) {
                            $this->shipStorageManager->upperStorage($station, $shipobj->getTorpedo()->getCommodity(), abs($load));
                            $torpName = $shipobj->getTorpedo()->getName();

                            if ($shipobj->getTorpedoCount() == 0) {
                                $shipobj->setTorpedo(null);

                                try {
                                    $this->shipSystemManager->deactivate($shipobj, ShipSystemTypeEnum::SYSTEM_TORPEDO);
                                } catch (AlreadyOffException $e) {
                                } catch (SystemNotDeactivableException $e) {
                                } catch (SystemNotFoundException $e) {
                                }
                            }
                            $msg[] = sprintf(
                                _('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
                                $shipobj->getName(),
                                abs($load),
                                $torpName
                            );
                        } elseif ($load > 0) {
                            $this->shipStorageManager->lowerStorage(
                                $station,
                                $this->commodityRepository->find($torp_obj->getGoodId()),
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
                        if (!$storage->containsKey($torp_obj->getGoodId())) {
                            throw new Exception;
                        }
                        if ($count > $storage[$torp_obj->getGoodId()]->getAmount()) {
                            $count = $storage[$torp_obj->getGoodId()]->getAmount();
                        }
                        if ($count > $shipobj->getMaxTorpedos()) {
                            $count = $shipobj->getMaxTorpedos();
                        }
                        $shipobj->setTorpedo($torp_obj);
                        $shipobj->setTorpedoCount($count);

                        $this->shipStorageManager->lowerStorage(
                            $station,
                            $this->commodityRepository->find($torp_obj->getGoodId()),
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

    private function storageContainsNeededCommodities($storage): bool
    {
        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
            if (!$storage->containsKey($commodityId)) {
                return false;
            }
        }

        return true;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
