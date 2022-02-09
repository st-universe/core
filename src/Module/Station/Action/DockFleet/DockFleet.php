<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DockFleet;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class DockFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DOCK_FLEET';

    private ShipLoaderInterface $shipLoader;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private PositionCheckerInterface $positionChecker;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        PositionCheckerInterface $positionChecker,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->positionChecker = $positionChecker;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $this->loggerUtil->log('A');
        $targetFleet = $this->fleetRepository->find(request::getIntFatal('fid'));
        if ($targetFleet === null) {
            $this->loggerUtil->log('B');
            return;
        }
        if (!$this->positionChecker->checkPosition($targetFleet->getLeadShip(), $station)) {
            $this->loggerUtil->log('C');
            return;
        }
        if ($targetFleet->getUser() !== $game->getUser()) {
            $this->loggerUtil->log('D');
            return;
        }
        if (!$station->isBase()) {
            $this->loggerUtil->log('E');
            return;
        }

        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        if ($station->getShieldState()) {
            $game->addInformation(_("Aktion nicht möglich. Die Station hat die Schilde aktiviert"));
            return;
        }

        $this->fleetDock($station, $targetFleet, $game);

        //$game->addInformation('Andockvorgang abgeschlossen');
    }

    private function fleetDock(ShipInterface $station, FleetInterface $targetFleet, GameControllerInterface $game): void
    {
        $this->loggerUtil->log('F');
        $msg = [];
        $msg[] = _("Station aktiviert Andockleitsystem zur Flotte: ") . $targetFleet->getName();;
        $freeSlots = $station->getFreeDockingSlotCount();
        foreach ($targetFleet->getShips() as $ship) {
            $this->loggerUtil->log('G');
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->getDockedTo()) {
                continue;
            }
            if ($station->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                $msg[] = $station->getName() . _(": Nicht genügend Energie vorhanden");
                break;
            }
            if ($ship->getCloakState()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            if ($ship->cancelRepair()) {
                $msg[] = $ship->getName() . _(': Die Reparatur wurde abgebrochen');
            }

            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);
            } catch (ShipSystemException $e) {
            }

            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (ShipSystemException $e) {
            }

            $ship->setDockedTo($station);

            $station->setEps($station->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK);

            $this->shipRepository->save($ship);

            $freeSlots--;
        }

        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
