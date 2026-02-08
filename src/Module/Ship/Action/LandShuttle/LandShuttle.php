<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LandShuttle;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Trait\SpacecraftShuttleSpaceTrait;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

final class LandShuttle implements ActionControllerInterface
{
    use SpacecraftShuttleSpaceTrait;

    public const string ACTION_IDENTIFIER = 'B_LAND_SHUTTLE';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private StorageManagerInterface $storageManager,
        private EntityManagerInterface $entityManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shuttleId = request::getIntFatal('shuttle');
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
        if ($target->getUser()->getId() !== $shuttle->getUser()->getId()) {
            return;
        }

        if (!$shuttle->hasEnoughCrew($game)) {
            return;
        }

        $commodity = $shuttle->getRump()->getCommodity();
        if ($commodity === null || !$commodity->isShuttle()) {
            return;
        }

        if ($target->isWarped()) {
            $game->getInfo()->addInformation(_("Das Ziel befindet sich im Warp"));
            return;
        }

        if ($target->isShielded()) {
            $game->getInfo()->addInformation(_("Das Ziel hat die Schilde aktiviert"));
            return;
        }

        // check if target got shuttle ramp
        if (!$target->hasShuttleRamp()) {
            $game->getInfo()->addInformation(_("Das Ziel verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if target shuttle ramp is healthy
        if (!$target->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
            $game->getInfo()->addInformation(_("Die Shuttle-Rampe des Zieles ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$this->hasFreeShuttleSpace($target)) {
            $game->getInfo()->addInformation(_("Die Shuttle-Rampe des Zieles ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($target) < $shuttle->getCrewCount()) {
            $game->getInfo()->addInformation(_('Das Ziel verfügt nicht über genügend Crew-Quartiere'));
            return;
        }

        // send shuttle to target storage
        $this->storageManager->upperStorage(
            $target,
            $commodity,
            1
        );

        // land shuttle and transfer crew
        $this->landShuttle($shuttle, $target);

        $game->getInfo()->addInformation("Shuttle erfolgreich gelandet");
    }

    private function landShuttle(Ship $shuttle, Spacecraft $target): void
    {
        foreach ($shuttle->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $target);
        }
        $this->entityManager->flush();

        $this->spacecraftRemover->remove($shuttle);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
