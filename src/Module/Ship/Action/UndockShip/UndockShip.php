<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UndockShip;

use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UndockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private CancelRepairInterface $cancelRepair;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        CancelRepairInterface $cancelRepair
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->cancelRepair = $cancelRepair;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if ($ship->isFleetLeader()) {
            $msg = [];
            $msg[] = _("Flottenbefehl ausgeführt: Abdocken von ") . $ship->getDockedTo()->getName();;
            foreach ($ship->getFleet()->getShips() as $ship) {
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
                if ($ship->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                    continue;
                }
                if ($this->cancelRepair->cancelRepair($ship)) {
                    $msg[] = $ship->getName() . _(": Die Reparatur wurde abgebrochen");
                    continue;
                }
                $ship->setDockedTo(null);
                $ship->setEps($ship->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK);

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
        if ($ship->getEps() == 0) {
            $game->addInformation('Zum Abdocken wird 1 Energie benötigt');
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        $ship->setEps($ship->getEps() - 1);
        $ship->setDockedTo(null);

        $this->shipRepository->save($ship);

        $game->addInformation('Abdockvorgang abgeschlossen');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
