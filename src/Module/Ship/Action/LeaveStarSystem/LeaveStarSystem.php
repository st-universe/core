<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ActivatorDeactivatorHelperInterface $helper;

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ActivatorDeactivatorHelperInterface $helper,
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->helper = $helper;
        $this->astroEntryLib = $astroEntryLib;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getSystem() === null) {
            return;
        }

        if ($ship->isFleetLeader() && $ship->getFleet()->getDefendedColony() !== null) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Verteidigung nicht möglich'));
            return;
        }

        if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
            return;
        }

        //reload ship because it got saved in helper class
        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $this->leaveStarSystem($ship);
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();

            /** @var ShipInterface[] $result */
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                if (!$this->helper->activate($fleetShip->getId(), ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: Warpantrieb kann nicht aktiviert werden";
                    $fleetShip->leaveFleet();
                    $this->shipRepository->save($fleetShip);
                } else {
                    //reload ship because it got saved in helper class
                    $reloadedShip = $this->shipLoader->getByIdAndUser(
                        $fleetShip->getId(),
                        $userId
                    );

                    $this->leaveStarSystem($reloadedShip);
                    if ($reloadedShip->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($reloadedShip, $game);
                    }
                    $this->shipRepository->save($reloadedShip);
                }
            }
            $game->addInformation("Die Flotte hat das Sternsystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternsystem wurde verlassen");
        }

        $this->shipRepository->save($ship);
    }

    private function leaveStarSystemTraktor(ShipInterface $ship, GameControllerInterface $game): void
    {
        if ($ship->getTraktorShip()->getFleetId()) {
            $name = $ship->getTraktorShip()->getName();
            $ship->deactivateTraktorBeam();

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Verlassen des Systems deaktiviert'),
                $name
            ));
            return;
        }
        if ($ship->getEps() < 1) {
            $name = $ship->getTraktorShip()->getName();
            $ship->deactivateTraktorBeam();
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->leaveStarSystem($ship->getTraktorShip());
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    private function leaveStarSystem(ShipInterface $ship): void
    {
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true);
        }
        $ship->setSystem(null);
        $ship->setSX(0);
        $ship->setSY(0);

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
