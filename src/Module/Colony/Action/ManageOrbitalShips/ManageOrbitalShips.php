<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShips;

use Exception;
use request;
use Stu\Component\Ship\ShipAlertStateEnum;
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
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

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

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private PositionCheckerInterface $positionChecker;

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
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        PositionCheckerInterface $positionChecker,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->crewCreator = $crewCreator;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->colonyRepository = $colonyRepository;
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->positionChecker = $positionChecker;
        $this->loggerUtil = $loggerUtil;
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
        $wk = request::postArray('wk');
        $torp = request::postArray('torp');
        $torp_type = request::postArray('torp_type');
        $storage = $colony->getStorage();

        $sectorString = $colony->getSX() . '|' . $colony->getSY();
        if ($colony->isInSystem()) {
            $sectorString .= ' (' . $colony->getSystem()->getName() . '-System)';
        }

        foreach ($ships as $ship) {
            $shipobj = $this->shipLoader->find((int) $ship);
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
                            (int) $shipobj->getUserId(),
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
                                $this->colonyStorageManager->lowerStorage(
                                    $colony,
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
                                        _('Die Kolonie %s hat in Sektor %s den Warpkern der %s um %d Einheiten aufgeladen'),
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
                            $this->colonyStorageManager->upperStorage($colony, $shipobj->getTorpedo()->getCommodity(), abs($load));
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
                            $this->colonyStorageManager->lowerStorage(
                                $colony,
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

                        $this->colonyStorageManager->lowerStorage(
                            $colony,
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
        $this->colonyRepository->save($colony);

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
