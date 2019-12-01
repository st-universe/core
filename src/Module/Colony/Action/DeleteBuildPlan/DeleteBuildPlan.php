<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use AccessViolation;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    private ColonyLoaderInterface $colonyLoader;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        /** @var ShipBuildplanInterface $plan */
        $plan = $this->shipBuildplanRepository->find((int) request::getIntFatal('planid'));
        if ($plan === null || $plan->getUserId() !== $userId || $plan->isDeleteable() === false) {
            throw new AccessViolation();
        }
        $this->shipBuildplanRepository->delete($plan);

        //$this->getTemplate()->setVar('FUNC', $this->getSelectedBuildingFunction());
        $game->showMacro('html/colonymacros.xhtml/cm_buildplans');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
