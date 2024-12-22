<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StoreShuttle;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class StoreShuttle implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STORE_SHUTTLE';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private StorageManagerInterface $storageManager,
        private EntityManagerInterface $entityManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private InteractionCheckerInterface $interactionChecker,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

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

        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
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
        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
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
        $this->storageManager->upperStorage(
            $ship,
            $commodity,
            1
        );

        // land shuttle and transfer crew
        $this->storeShuttle($ship, $shuttle);

        $game->addInformation("Shuttle erfolgreich eingesammelt");
    }

    private function storeShuttle(ShipInterface $ship, SpacecraftInterface $shuttle): void
    {
        foreach ($shuttle->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $ship);
        }
        $this->entityManager->flush();

        $this->spacecraftRemover->remove($shuttle);

        $this->shipLoader->save($ship);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
