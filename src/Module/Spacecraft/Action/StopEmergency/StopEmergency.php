<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StopEmergency;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

/**
 * Stops a ship's emergency call
 */
final class StopEmergency implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STOP_EMERGENCY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        private StopEmergencyRequestInterface $stopEmergencyRequest,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $this->stopEmergencyRequest->getShipId(),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        if (!$ship->getIsInEmergency()) {
            return;
        }

        $ship->setIsInEmergency(false);

        $emergency = $this->spacecraftEmergencyRepository->getByShipId($ship->getId());

        if ($emergency !== null) {
            $emergency->setDeleted($this->stuTime->time());
            $this->spacecraftEmergencyRepository->save($emergency);
        }


        $game->addInformation('Das Notrufsignal wurde beendet');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
