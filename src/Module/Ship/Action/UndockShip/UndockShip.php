<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UndockShip;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UndockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
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
            $msg = array();
            $msg[] = _("Flottenbefehl ausgeführt: Abdocken von ") . $ship->getDockedShip()->getName();;
            foreach ($ship->getFleet()->getShips() as $key => $ship) {
                if (!$ship->getDock()) {
                    continue;
                }
                if ($ship->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                    continue;
                }
                $ship->cancelRepair();
                $ship->setDock(0);
                $ship->setEps($ship->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK);

                $this->shipRepository->save($ship);
            }
            $game->addInformationMerge($msg);
            return;
        }
        if (!$ship->getDock()) {
            return;
        }
        if ($ship->getEps() == 0) {
            $game->addInformation('Zum Abdocken wird 1 Energie benötigt');
            return;
        }
        $ship->cancelRepair();
        $ship->setEps($ship->getEps() - 1);
        $ship->setDock(0);

        $this->shipRepository->save($ship);

        $game->addInformation('Abdockvorgang abgeschlossen');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
