<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UndockShip;

use Override;
use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UndockShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNDOCK';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private CancelRepairInterface $cancelRepair)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $dockedTo = $ship->getDockedTo();
        if ($dockedTo === null) {
            return;
        }

        if ($ship->isFleetLeader() && $ship->getFleet() !== null) {
            $msg = [_("Flottenbefehl ausgeführt: Abdocken von ") . $dockedTo->getName()];

            $fleetWrapper = $wrapper->getFleetWrapper();
            if ($fleetWrapper === null) {
                return;
            }

            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $ship = $wrapper->get();

                if ($ship->getDockedTo() === null) {
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
                if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                    continue;
                }
                if ($this->cancelRepair->cancelRepair($ship)) {
                    $msg[] = $ship->getName() . _(": Die Reparatur wurde abgebrochen");
                    continue;
                }
                $ship->setDockedTo(null);
                $epsSystem->lowerEps(ShipEnum::SYSTEM_ECOST_DOCK)->update();

                $this->shipRepository->save($ship);
            }
            $game->addInformationMerge($msg);
            return;
        }
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(_('Nicht genügend Crew vorhanden'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation('Zum Abdocken wird 1 Energie benötigt');
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        $epsSystem->lowerEps(1)->update();
        $ship->setDockedTo(null);

        $this->shipRepository->save($ship);

        $game->addInformation('Abdockvorgang abgeschlossen');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
