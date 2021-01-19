<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use Stu\Exception\AccessViolation;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        /** @var ShipBuildplanInterface $plan */
        $plan = $this->shipBuildplanRepository->find((int) request::getIntFatal('planid'));
        if ($plan === null || $plan->getUserId() !== $userId || $plan->isDeleteable() === false) {
            $game->addInformation(_('Der Bauplan konnte nicht gelöscht werden'));

            return;
        }
        $this->buildplanModuleRepository->truncateByBuildplan($plan->getId());
        $this->shipBuildplanRepository->delete($plan);

        $game->addInformation(_('Der Bauplan wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
