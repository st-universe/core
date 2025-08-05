<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use Override;
use request;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    public function __construct(private BuildPlanDeleterInterface $buildPlanDeleter, private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $spacecraftBuildplan = $this->spacecraftBuildplanRepository->find(request::getIntFatal('planid'));
        if (
            $spacecraftBuildplan === null
            || $spacecraftBuildplan->getUserId() !== $userId
            || $this->buildPlanDeleter->isDeletable($spacecraftBuildplan) === false
        ) {
            $game->getInfo()->addInformation('Der Bauplan konnte nicht gelöscht werden');

            return;
        }

        $this->buildPlanDeleter->delete($spacecraftBuildplan);

        $game->getInfo()->addInformation('Der Bauplan wurde gelöscht');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
