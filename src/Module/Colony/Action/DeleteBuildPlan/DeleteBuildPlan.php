<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use request;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private BuildPlanDeleterInterface $buildPlanDeleter;

    public function __construct(
        BuildPlanDeleterInterface $buildPlanDeleter,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->buildPlanDeleter = $buildPlanDeleter;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $shipBuildplan = $this->shipBuildplanRepository->find(request::getIntFatal('planid'));
        if (
            $shipBuildplan === null
            || $shipBuildplan->getUserId() !== $userId
            || $this->buildPlanDeleter->isDeletable($shipBuildplan) === false
        ) {
            $game->addInformation('Der Bauplan konnte nicht gelöscht werden');

            return;
        }

        $this->buildPlanDeleter->delete($shipBuildplan);

        $game->addInformation('Der Bauplan wurde gelöscht');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
