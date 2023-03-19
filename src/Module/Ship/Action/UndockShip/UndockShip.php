<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UndockShip;

use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UndockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private CancelRepairInterface $cancelRepair;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        CancelRepairInterface $cancelRepair,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->cancelRepair = $cancelRepair;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if ($ship->isFleetLeader()) {
            $msg = [];
            $msg[] = _("Flottenbefehl ausgeführt: Abdocken von ") . $ship->getDockedTo()->getName();
            ;
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                $wrapper = $this->shipWrapperFactory->wrapShip($fleetShip);
                $ship = $wrapper->get();

                if (!$ship->getDockedTo()) {
                    continue;
                }
                if (!$ship->hasEnoughCrew()) {
                    $msg[] = sprintf(
                        _('%s: Nicht genügend Crew vorhanden'),
                        $ship->getName()
                    );
                    continue;
                }

                $epsSystem = $wrapper->getEpsSystemData();
                if ($epsSystem->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                    continue;
                }
                if ($this->cancelRepair->cancelRepair($ship)) {
                    $msg[] = $ship->getName() . _(": Die Reparatur wurde abgebrochen");
                    continue;
                }
                $ship->setDockedTo(null);
                $epsSystem->setEps($epsSystem->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK)->update();

                $this->shipRepository->save($ship);
            }
            $game->addInformationMerge($msg);
            return;
        }
        if (!$ship->getDockedTo()) {
            return;
        }
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(_('Nicht genügend Crew vorhanden'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem->getEps() == 0) {
            $game->addInformation('Zum Abdocken wird 1 Energie benötigt');
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        $epsSystem->setEps($epsSystem->getEps() - 1)->update();
        $ship->setDockedTo(null);

        $this->shipRepository->save($ship);

        $game->addInformation('Abdockvorgang abgeschlossen');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
