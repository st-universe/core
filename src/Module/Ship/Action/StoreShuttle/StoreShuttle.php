<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StoreShuttle;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class StoreShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STORE_SHUTTLE';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private EntityManagerInterface $entityManager;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ShipRemoverInterface $shipRemover;

    private InteractionCheckerInterface $interactionChecker;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        EntityManagerInterface $entityManager,
        TroopTransferUtilityInterface $troopTransferUtility,
        ShipRemoverInterface $shipRemover,
        InteractionCheckerInterface $interactionChecker,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->entityManager = $entityManager;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->shipRemover = $shipRemover;
        $this->interactionChecker = $interactionChecker;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $shuttleId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $shuttleId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $shuttleWrapper = $wrappers->getTarget();
        if ($shuttleWrapper === null) {
            return;
        }
        $shuttle = $shuttleWrapper->get();

        $commodity = $shuttle->getRump()->getCommodity();
        if ($commodity === null || !$commodity->isShuttle()) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($ship, $shuttle)) {
            return;
        }
        if ($shuttle->getUser() !== $ship->getUser()) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->getWarpState()) {
            $game->addInformation(_("Das Schiff hat den Warpantrieb aktiviert"));
            return;
        }

        if ($shuttle->getShieldState()) {
            $game->addInformation(_("Das Schiff hat die Schilde aktiviert"));
            return;
        }

        // check if ship got shuttle ramp
        if (!$ship->hasShuttleRamp()) {
            $game->addInformation(_("Das Schiff verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if shuttle ramp is healthy
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$ship->hasFreeShuttleSpace($this->loggerUtil)) {
            $game->addInformation(_("Die Shuttle-Rampe ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($ship) < $shuttle->getCrewCount()) {
            $game->addInformation(_('Nicht genügend Crew-Quartiere frei'));
            return;
        }

        // send shuttle to target storage
        $this->shipStorageManager->upperStorage(
            $ship,
            $commodity,
            1
        );

        // land shuttle and transfer crew
        $this->storeShuttle($ship, $shuttle);

        $game->addInformation("Shuttle erfolgreich eingesammelt");
    }

    private function storeShuttle(ShipInterface $ship, ShipInterface $shuttle): void
    {
        foreach ($shuttle->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $ship);
        }
        $this->entityManager->flush();

        $this->shipRemover->remove($shuttle);

        $this->shipLoader->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
