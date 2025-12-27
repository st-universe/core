<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\StationRepair;

use request;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StationRepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STATION_REPAIR';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private RepairUtilInterface $repairUtil
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::postIntFatal('id'),
            $userId
        );

        $station = $wrapper->get();
        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        if (!$wrapper->isUnalerted()) {
            return;
        }

        if ($station->getCondition()->isUnderRepair()) {
            $game->getInfo()->addInformation(_('Die Station wird bereits repariert.'));
            return;
        }

        $station->getCondition()->setState(SpacecraftStateEnum::REPAIR_PASSIVE);

        $duration = $this->repairUtil->getRepairDuration($wrapper);

        $game->getInfo()->addInformationf(
            'Die Station wird repariert. Fertigstellung in %s Ticks.',
            $duration
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
