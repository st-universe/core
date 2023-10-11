<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LandShuttle;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class LandShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LAND_SHUTTLE';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private EntityManagerInterface $entityManager;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ShipRemoverInterface $shipRemover;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        EntityManagerInterface $entityManager,
        TroopTransferUtilityInterface $troopTransferUtility,
        ShipRemoverInterface $shipRemover,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->entityManager = $entityManager;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->shipRemover = $shipRemover;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shuttleId = request::indInt('shuttle');
        $targetId = request::getIntFatal('id');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shuttleId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $shuttle = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$this->interactionChecker->checkPosition($shuttle, $target)) {
            return;
        }
        if ($target->getUser() !== $shuttle->getUser()) {
            return;
        }

        if (!$shuttle->hasEnoughCrew($game)) {
            return;
        }

        $commodity = $shuttle->getRump()->getCommodity();
        if ($commodity === null || !$commodity->isShuttle()) {
            return;
        }

        if ($target->getWarpState()) {
            $game->addInformation(_("Das Zielschiff hat den Warpantrieb aktiviert"));
            return;
        }

        if ($target->getShieldState()) {
            $game->addInformation(_("Das Zielschiff hat die Schilde aktiviert"));
            return;
        }

        // check if target got shuttle ramp
        if (!$target->hasShuttleRamp()) {
            $game->addInformation(_("Das Zielschiff verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if target shuttle ramp is healthy
        if (!$target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe vom Zielschiff ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$target->hasFreeShuttleSpace(null)) {
            $game->addInformation(_("Die Shuttle-Rampe des Zielschiffs ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($target) < $shuttle->getCrewCount()) {
            $game->addInformation(_('Das Zielschiff verfügt nicht über genügend Crew-Quartiere'));
            return;
        }

        // send shuttle to target storage
        $this->shipStorageManager->upperStorage(
            $target,
            $commodity,
            1
        );

        // land shuttle and transfer crew
        $this->landShuttle($shuttle, $target);

        $game->addInformation("Shuttle erfolgreich gelandet");
    }

    private function landShuttle(ShipInterface $shuttle, ShipInterface $target): void
    {
        foreach ($shuttle->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $target);
        }
        $this->entityManager->flush();

        $this->shipRemover->remove($shuttle);

        $this->shipLoader->save($target);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
