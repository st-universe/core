<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StoreShuttle;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftInterface;

final class StoreShuttle implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STORE_SHUTTLE';

    private LoggerUtilInterface $loggerUtil;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private StorageManagerInterface $storageManager,
        private EntityManagerInterface $entityManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
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

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $shuttleId
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();

        $shuttleWrapper = $wrappers->getTarget();
        if ($shuttleWrapper === null) {
            return;
        }
        $shuttle = $shuttleWrapper->get();

        $commodity = $shuttle->getRump()->getCommodity();
        if ($commodity === null || !$commodity->isShuttle()) {
            return;
        }

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($spacecraft)
            ->setTarget($shuttle)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_SAME_USER,
                InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB
            ])
            ->check($game)) {
            return;
        }

        // check if ship got shuttle ramp
        if (!$spacecraft->hasShuttleRamp()) {
            $game->addInformation(_("Das Schiff verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if shuttle ramp is healthy
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$spacecraft->hasFreeShuttleSpace($this->loggerUtil)) {
            $game->addInformation(_("Die Shuttle-Rampe ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($spacecraft) < $shuttle->getCrewCount()) {
            $game->addInformation(_('Nicht genügend Crew-Quartiere frei'));
            return;
        }

        // send shuttle to target storage
        $this->storageManager->upperStorage(
            $spacecraft,
            $commodity,
            1
        );

        // land shuttle and transfer crew
        $this->storeShuttle($spacecraft, $shuttle);

        $game->addInformation("Shuttle erfolgreich eingesammelt");
    }

    private function storeShuttle(SpacecraftInterface $spacecraft, SpacecraftInterface $shuttle): void
    {
        foreach ($shuttle->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $spacecraft);
        }
        $this->entityManager->flush();

        $this->spacecraftRemover->remove($shuttle);

        $this->spacecraftLoader->save($spacecraft);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
